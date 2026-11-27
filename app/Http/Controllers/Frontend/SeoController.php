<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $posts = Post::published()
            ->whereIn('type', ['news', 'service', 'page'])
            ->latest('updated_at')
            ->take(2000)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Homepage
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . e(url('/')) . "</loc>\n";
        $xml .= "    <changefreq>always</changefreq>\n";
        $xml .= "    <priority>1.0</priority>\n";
        $xml .= "  </url>\n";

        // Team Page
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . e(route('team.index')) . "</loc>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.6</priority>\n";
        $xml .= "  </url>\n";

        // Categories
        foreach ($categories as $cat) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . e(route('category.show', $cat->slug)) . "</loc>\n";
            $xml .= "    <lastmod>" . $cat->updated_at->toAtomString() . "</lastmod>\n";
            $xml .= "    <changefreq>hourly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        // Posts & Pages
        foreach ($posts as $post) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . e(route('post.show', $post->slug)) . "</loc>\n";
            $xml .= "    <lastmod>" . $post->updated_at->toAtomString() . "</lastmod>\n";
            $xml .= "    <changefreq>daily</changefreq>\n";
            $xml .= "    <priority>" . ($post->type === 'news' ? '0.9' : '0.7') . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function newsSitemap(): Response
    {
        abort_unless(cms_addon('google_news_sitemap', false), 404);
        $siteName = cms_setting('site_name', 'InsightPress News');
        // Google News Sitemap includes articles published in the last 48 hours
        $recentNews = Post::published()
            ->where('type', 'news')
            ->where('published_at', '>=', now()->subHours(48))
            ->latest('published_at')
            ->take(1000)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";

        foreach ($recentNews as $post) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . e(route('post.show', $post->slug)) . "</loc>\n";
            $xml .= "    <news:news>\n";
            $xml .= "      <news:publication>\n";
            $xml .= "        <news:name>" . e($siteName) . "</news:name>\n";
            $xml .= "        <news:language>bn</news:language>\n";
            $xml .= "      </news:publication>\n";
            $xml .= "      <news:publication_date>" . $post->published_at->toAtomString() . "</news:publication_date>\n";
            $xml .= "      <news:title>" . e($post->title) . "</news:title>\n";
            $xml .= "    </news:news>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function robots(): Response
    {
        $lines = [
            "User-agent: *",
            "Allow: /",
            "Disallow: /admin",
            "Disallow: /login",
            "",
            "Sitemap: " . url('/sitemap.xml'),
            cms_addon('google_news_sitemap', false) ? "Sitemap: " . url('/news-sitemap.xml') : "",
        ];

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
