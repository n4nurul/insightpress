<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('cms_xml_sitemap', 3600, function () {
            $categories = Category::where('is_active', true)->get();
            $posts = Post::published()->whereIn('type', ['news', 'service', 'page'])->latest('updated_at')->take(1000)->get();

            $content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            $content .= '<url><loc>' . url('/') . '</loc><changefreq>hourly</changefreq><priority>1.0</priority></url>' . "\n";

            foreach ($categories as $cat) {
                $content .= '<url><loc>' . route('category.show', $cat->slug) . '</loc><changefreq>daily</changefreq><priority>0.8</priority></url>' . "\n";
            }

            foreach ($posts as $post) {
                $content .= '<url><loc>' . route('post.show', $post->slug) . '</loc><lastmod>' . $post->updated_at->toAtomString() . '</lastmod><changefreq>weekly</changefreq><priority>0.9</priority></url>' . "\n";
            }

            $content .= '</urlset>';
            return $content;
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
