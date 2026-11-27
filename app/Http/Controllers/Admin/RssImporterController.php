<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RssImporterController extends Controller
{
    public function index()
    {
        abort_unless(cms_addon('rss_importer', true), 404);

        $categories = Category::where('type', 'news')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('admin.rss-importer.index', compact('categories'));
    }

    public function import(Request $request)
    {
        abort_unless(cms_addon('rss_importer', true), 404);

        $data = $request->validate([
            'feed_url' => ['required', 'url'],
            'category_id' => ['required', 'exists:categories,id'],
            'limit' => ['required', 'integer', 'min:1', 'max:20'],
            'status' => ['required', 'in:published,draft'],
            'mark_featured' => ['nullable', 'boolean'],
        ]);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/rss+xml, application/xml, text/xml, */*',
            ])->timeout(20)->get($data['feed_url']);

            if (!$response->successful()) {
                return back()->withInput()->with('error', 'ফিড লিংক থেকে ডেটা আনা যায়নি (HTTP ' . $response->status() . ')। নিচের Direct Article URL ইম্পোর্টার ব্যবহার করুন।');
            }

            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($xml === false) {
                return back()->withInput()->with('error', 'সঠিক RSS/XML ফরম্যাট পাওয়া যায়নি। নিচের Direct Article URL ইম্পোর্টার ব্যবহার করুন।');
            }

            $items = [];
            if (isset($xml->channel->item)) {
                foreach ($xml->channel->item as $item) {
                    $items[] = $item;
                }
            } elseif (isset($xml->entry)) {
                foreach ($xml->entry as $entry) {
                    $items[] = $entry;
                }
            }

            if (empty($items)) {
                return back()->withInput()->with('error', 'এই ফিডে কোনো নিউজ আইটেম পাওয়া যায়নি।');
            }

            $imported = 0;
            $skipped = 0;

            foreach (array_slice($items, 0, (int) $data['limit']) as $item) {
                $title = trim(html_entity_decode(strip_tags((string) ($item->title ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ($title === '') {
                    continue;
                }

                if (Post::where('title', $title)->exists()) {
                    $skipped++;
                    continue;
                }

                $namespaces = $item->getNamespaces(true);
                $rawHtml = '';

                if (isset($namespaces['content'])) {
                    $contentNs = $item->children($namespaces['content']);
                    $rawHtml = (string) ($contentNs->encoded ?? '');
                }

                if (trim($rawHtml) === '') {
                    $rawHtml = (string) ($item->description ?? $item->summary ?? $item->content ?? '');
                }

                $cleanBody = strip_tags($rawHtml, '<p><br><h2><h3><h4><ul><ol><li><strong><b><em><blockquote>');
                $cleanBody = preg_replace('/<a\b[^>]*>(.*?)<\/a>/is', '$1', $cleanBody);
                if (trim(strip_tags($cleanBody)) === '') {
                    $cleanBody = '<p>' . e($title) . '</p>';
                } elseif (!str_contains($cleanBody, '<p>')) {
                    $cleanBody = '<p>' . nl2br(e(trim(strip_tags($cleanBody)))) . '</p>';
                }

                $imageUrl = $this->extractImageUrl($item, $namespaces, $rawHtml);
                $this->storePost($title, $cleanBody, $imageUrl, $data['category_id'], $data['status'], (bool) ($data['mark_featured'] ?? false), $imported * 5);

                $imported++;
            }

            return back()->with('success', "সফলভাবে {$imported}টি নিউজ ইম্পোর্ট হয়েছে!" . ($skipped > 0 ? " (আগে থাকায় {$skipped}টি স্কিপ করা হয়েছে)" : ''));
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'ইম্পোর্ট ত্রুটি: ' . $e->getMessage());
        }
    }

    public function importSingleUrl(Request $request)
    {
        abort_unless(cms_addon('rss_importer', true), 404);

        $data = $request->validate([
            'article_url' => ['required', 'url'],
            'category_id' => ['required', 'exists:categories,id'],
            'status' => ['required', 'in:published,draft'],
            'mark_featured' => ['nullable', 'boolean'],
        ]);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->timeout(20)->get($data['article_url']);

            if (!$response->successful()) {
                return back()->withInput()->with('error', 'পেজটি লোড করা যায়নি (HTTP ' . $response->status() . ')।');
            }

            $html = $response->body();
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
            $xpath = new \DOMXPath($dom);

            // 1. Extract Title (og:title -> h1 -> title)
            $title = '';
            $ogTitle = $xpath->query('//meta[@property="og:title"]/@content');
            if ($ogTitle->length > 0) {
                $title = trim($ogTitle->item(0)->nodeValue);
            }
            if ($title === '') {
                $h1 = $xpath->query('//h1');
                if ($h1->length > 0) {
                    $title = trim($h1->item(0)->textContent);
                }
            }
            if ($title === '') {
                return back()->withInput()->with('error', 'এই পেজ থেকে নিউজের শিরোনাম পাওয়া যায়নি।');
            }

            // Clean trailing site name if separated by | or -
            $title = preg_replace('/\s*[\|\-–]\s*(সিলেটভিউ২৪|SylhetView24|প্রথম আলো|Prothom Alo|জাগো নিউজ|JagoNews24).*$/iu', '', $title);

            if (Post::where('title', $title)->exists()) {
                return back()->withInput()->with('error', 'এই শিরোনামের নিউজটি ইতিমধ্যে ইম্পোর্ট করা আছে।');
            }

            // 2. Extract Image (og:image)
            $imageUrl = null;
            $ogImage = $xpath->query('//meta[@property="og:image"]/@content');
            if ($ogImage->length > 0) {
                $candidate = trim($ogImage->item(0)->nodeValue);
                if (filter_var($candidate, FILTER_VALIDATE_URL)) {
                    $imageUrl = $candidate;
                }
            }

            // 3. Extract Paragraphs
            $paragraphs = [];
            $pNodes = $xpath->query('//article//p | //main//p | //p');
            foreach ($pNodes as $p) {
                $text = trim(preg_replace('/\s+/', ' ', $p->textContent));
                if (mb_strlen($text) >= 45 && !str_contains($text, '©') && !str_contains($text, 'সর্বস্বত্ব সংরক্ষিত')) {
                    $paragraphs[] = '<p>' . e($text) . '</p>';
                }
                if (count($paragraphs) >= 15) {
                    break;
                }
            }

            if (empty($paragraphs)) {
                $ogDesc = $xpath->query('//meta[@property="og:description"]/@content | //meta[@name="description"]/@content');
                if ($ogDesc->length > 0 && trim($ogDesc->item(0)->nodeValue) !== '') {
                    $paragraphs[] = '<p>' . e(trim($ogDesc->item(0)->nodeValue)) . '</p>';
                } else {
                    $paragraphs[] = '<p>' . e($title) . '</p>';
                }
            }

            $cleanBody = implode("\n", array_unique($paragraphs));

            $this->storePost($title, $cleanBody, $imageUrl, $data['category_id'], $data['status'], (bool) ($data['mark_featured'] ?? false), 0);

            return back()->with('success', 'সফলভাবে নিউজটি ইম্পোর্ট হয়েছে: ' . $title);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'URL ইম্পোর্ট ত্রুটি: ' . $e->getMessage());
        }
    }

    private function storePost(string $title, string $cleanBody, ?string $imageUrl, int $categoryId, string $status, bool $isFeatured, int $minusMinutes): void
    {
        $excerpt = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($cleanBody))), 190);
        $savedImagePath = $imageUrl ? $this->downloadImage($imageUrl) : null;

        $baseSlug = preg_replace('/[^\p{L}\p{N}]+/u', '-', mb_strtolower($title));
        $baseSlug = trim($baseSlug, '-') ?: 'news-' . time();
        $slug = $baseSlug;
        $counter = 1;
        while (Post::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        Post::create([
            'user_id' => auth()->id() ?? 1,
            'category_id' => $categoryId,
            'type' => 'news',
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'body' => $cleanBody,
            'featured_image' => $savedImagePath,
            'is_featured' => $isFeatured,
            'is_breaking' => false,
            'status' => $status,
            'published_at' => now()->subMinutes($minusMinutes),
            'meta_title' => $title,
            'meta_description' => $excerpt,
            'seo_score' => 78,
        ]);
    }

    private function extractImageUrl($item, array $namespaces, string $rawHtml): ?string
    {
        if (isset($item->enclosure['url'])) {
            $url = (string) $item->enclosure['url'];
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }
        }

        if (isset($namespaces['media'])) {
            $media = $item->children($namespaces['media']);
            if (isset($media->content)) {
                $attrs = $media->content->attributes();
                if (!empty($attrs['url'])) {
                    return (string) $attrs['url'];
                }
            }
            if (isset($media->thumbnail)) {
                $attrs = $media->thumbnail->attributes();
                if (!empty($attrs['url'])) {
                    return (string) $attrs['url'];
                }
            }
        }

        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $rawHtml, $matches)) {
            $src = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (filter_var($src, FILTER_VALIDATE_URL)) {
                return $src;
            }
        }

        return null;
    }

    private function downloadImage(string $url): ?string
    {
        try {
            $imgResponse = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0',
            ])->timeout(10)->get($url);

            if (!$imgResponse->successful()) {
                return null;
            }

            $contentType = $imgResponse->header('Content-Type', '');
            $ext = match (true) {
                str_contains($contentType, 'png') => 'png',
                str_contains($contentType, 'webp') => 'webp',
                str_contains($contentType, 'gif') => 'gif',
                default => 'jpg',
            };

            $filename = 'news/' . date('Y/m') . '/' . Str::random(24) . '.' . $ext;
            Storage::disk('public')->put($filename, $imgResponse->body());

            return $filename;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
