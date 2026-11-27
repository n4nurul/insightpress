<?php

namespace App\Http\Controllers\Admin;

use App\Core\Services\MediaService;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct(protected MediaService $media) {}

    public function index(Request $request)
    {
        $type = $request->get('type', 'news');
        $query = Post::with('category')->where('type', $type)->latest();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $posts = $query->paginate(15)->withQueryString();
        return view('admin.posts.index', compact('posts', 'type'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'news');
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.posts.form', compact('type', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePost($request);

        $slug = $request->filled('slug') ? bn_slug($request->slug) : bn_slug($data['title']);
        if (Post::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(100, 999);
        }

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->media->uploadImage($request->file('featured_image'), 'posts');
        }

        $data['slug'] = $slug;
        $data['user_id'] = Auth::id();
        $data['is_breaking'] = $request->boolean('is_breaking');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['published_at'] = $data['published_at'] ?? now();
        $data['meta_data'] = $request->input('meta_data', []);

        Post::create($data);

        return redirect()->route('admin.posts.index', ['type' => $data['type']])
            ->with('success', 'Published successfully.');
    }

    public function edit(Post $post)
    {
        $type = $post->type;
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.posts.form', compact('post', 'type', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validatePost($request);

        if ($request->filled('slug') && $request->slug !== $post->slug) {
            $slug = bn_slug($request->slug);
            if (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug .= '-' . rand(100, 999);
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('featured_image')) {
            $this->media->delete($post->featured_image);
            $data['featured_image'] = $this->media->uploadImage($request->file('featured_image'), 'posts');
        }

        $data['is_breaking'] = $request->boolean('is_breaking');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['meta_data'] = array_merge($post->meta_data ?? [], $request->input('meta_data', []));

        $post->update($data);

        return redirect()->route('admin.posts.index', ['type' => $post->type])
            ->with('success', 'Updated successfully.');
    }

    public function destroy(Post $post)
    {
        $type = $post->type;
        $this->media->delete($post->featured_image);
        $post->delete();

        return redirect()->route('admin.posts.index', ['type' => $type])
            ->with('success', 'Deleted successfully.');
    }

    protected function validatePost(Request $request): array
    {
        return $request->validate([
            'type' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'sub_title' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'body' => 'nullable|string',
            'featured_image' => 'nullable|image|max:5120',
            'video_url' => 'nullable|string|max:255',
            'focus_keyword' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'seo_score' => 'nullable|integer|min:0|max:100',
            'status' => 'required|in:published,draft',
            'published_at' => 'nullable|date',
        ]);
    }
}
