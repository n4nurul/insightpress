<?php

namespace App\Http\Controllers\Admin;

use App\Core\Services\MediaService;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct(protected MediaService $media) {}

    public function index(Request $request)
    {
        $type = $request->get('type', 'news');
        $filter = $request->get('filter', 'active'); // active, archived, trash
        $user = Auth::user();

        if ($user->isReporter() && $type !== 'news') {
            abort(403, 'Reporters can only manage news posts.');
        }

        $baseQuery = Post::where('type', $type)
            ->when($user->isReporter(), fn($q) => $q->where('user_id', $user->id));

        $counts = [
            'active' => (clone $baseQuery)->where('status', '!=', 'archived')->count(),
            'archived' => (clone $baseQuery)->where('status', 'archived')->count(),
            'trash' => (clone $baseQuery)->onlyTrashed()->count(),
        ];

        $query = Post::with(['category', 'author'])->where('type', $type);

        if ($user->isReporter()) {
            $query->where('user_id', $user->id);
        }

        if ($filter === 'trash') {
            $query->onlyTrashed()->latest('deleted_at');
        } elseif ($filter === 'archived') {
            $query->where('status', 'archived')->latest('published_at');
        } else {
            $query->where('status', '!=', 'archived')->latest('published_at');
        }

        // Category Filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Month-Year Date Filter (YYYY-MM)
        if ($request->filled('month')) {
            $parts = explode('-', $request->month);
            if (count($parts) === 2) {
                $query->whereYear('published_at', $parts[0])
                      ->whereMonth('published_at', $parts[1]);
            }
        }

        // Search Filter
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $posts = $query->paginate(15)->withQueryString();

        // Filter Dropdown Data
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $months = Post::withTrashed()
            ->where('type', $type)
            ->whereNotNull('published_at')
            ->selectRaw("DATE_FORMAT(published_at, '%Y-%m') as ym")
            ->distinct()
            ->orderByDesc('ym')
            ->pluck('ym')
            ->map(fn($ym) => [
                'value' => $ym,
                'label' => Carbon::createFromFormat('Y-m', $ym)->format('F Y'),
            ]);

        return view('admin.posts.index', compact('posts', 'type', 'filter', 'counts', 'categories', 'months'));
    }

    public function bulkAction(Request $request)
    {
        if (!Auth::user()->isEditor()) {
            abort(403, 'Editor or Admin privileges required.');
        }

        $request->validate([
            'bulk_action' => 'required|in:publish,draft,archive,trash,restore,force_delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $ids = $request->ids;
        $action = $request->bulk_action;

        match ($action) {
            'publish' => Post::whereIn('id', $ids)->update(['status' => 'published', 'published_at' => now()]),
            'draft' => Post::whereIn('id', $ids)->update(['status' => 'draft']),
            'archive' => Post::whereIn('id', $ids)->update(['status' => 'archived', 'is_breaking' => false, 'is_featured' => false]),
            'trash' => Post::whereIn('id', $ids)->delete(),
            'restore' => Post::onlyTrashed()->whereIn('id', $ids)->restore(),
            'force_delete' => Post::onlyTrashed()->whereIn('id', $ids)->get()->each(function ($post) {
                $this->media->delete($post->featured_image);
                $post->forceDelete();
            }),
        };

        return back()->with('success', 'Bulk action applied to ' . count($ids) . ' item(s).');
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'news');
        if (Auth::user()->isReporter() && $type !== 'news') {
            abort(403);
        }
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.posts.form', compact('type', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePost($request);
        $user = Auth::user();

        $rawSlug = $request->filled('slug') ? $request->slug : $data['title'];
        $slug = bn_slug($rawSlug) ?: 'post-' . time();
        if (Post::withTrashed()->where('slug', $slug)->exists()) {
            $slug .= '-' . rand(100, 999);
        }

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->media->uploadImage($request->file('featured_image'), 'posts');
        }

        $data['slug'] = $slug;
        $data['user_id'] = $user->id;
        $data['is_breaking'] = $user->isEditor() ? $request->boolean('is_breaking') : false;
        $data['is_featured'] = $user->isEditor() ? $request->boolean('is_featured') : false;
        $data['status'] = $user->isEditor() ? $data['status'] : 'draft';
        $data['published_at'] = $this->resolvePublishedAt($request);
        $data['meta_data'] = $request->input('meta_data', []);

        unset($data['publish_date'], $data['publish_time']);
        Post::create($data);

        return redirect()->route('admin.posts.index', ['type' => $data['type']])
            ->with('success', $user->isEditor() ? 'Saved successfully.' : 'Submitted for editorial review (Draft).');
    }

    public function edit(Post $post)
    {
        $user = Auth::user();
        if ($user->isReporter() && $post->user_id !== $user->id) {
            abort(403, 'You can only edit your own posts.');
        }

        $type = $post->type;
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.posts.form', compact('post', 'type', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $user = Auth::user();
        if ($user->isReporter() && $post->user_id !== $user->id) {
            abort(403);
        }

        $data = $this->validatePost($request);

        if ($request->filled('slug')) {
            $slug = bn_slug($request->slug) ?: $post->slug;
            if ($slug !== $post->slug && Post::withTrashed()->where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug .= '-' . rand(100, 999);
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('featured_image')) {
            $this->media->delete($post->featured_image);
            $data['featured_image'] = $this->media->uploadImage($request->file('featured_image'), 'posts');
        }

        $data['is_breaking'] = $user->isEditor() ? $request->boolean('is_breaking') : $post->is_breaking;
        $data['is_featured'] = $user->isEditor() ? $request->boolean('is_featured') : $post->is_featured;
        $data['status'] = $user->isEditor() ? $data['status'] : 'draft';
        $data['published_at'] = $this->resolvePublishedAt($request, $post->published_at);
        $data['meta_data'] = array_merge($post->meta_data ?? [], $request->input('meta_data', []));

        unset($data['publish_date'], $data['publish_time']);
        $post->update($data);

        return redirect()->route('admin.posts.index', ['type' => $post->type])
            ->with('success', 'Updated successfully.');
    }

    public function archive(Post $post)
    {
        if (!Auth::user()->isEditor()) {
            abort(403);
        }

        $post->update([
            'status' => 'archived',
            'is_breaking' => false,
            'is_featured' => false,
        ]);

        return back()->with('success', 'Post moved to Archive.');
    }

    public function destroy(Post $post)
    {
        if (!Auth::user()->isEditor()) {
            abort(403, 'Only Editors or Admins can delete posts.');
        }

        $type = $post->type;
        $post->delete();

        return redirect()->route('admin.posts.index', ['type' => $type])
            ->with('success', 'Moved to Trash. You can recover it anytime from the Trash tab.');
    }

    public function restore($id)
    {
        if (!Auth::user()->isEditor()) {
            abort(403);
        }

        $post = Post::onlyTrashed()->findOrFail($id);
        $type = $post->type;
        $post->restore();

        return redirect()->route('admin.posts.index', ['type' => $type, 'filter' => 'trash'])
            ->with('success', 'Post recovered successfully!');
    }

    public function forceDelete($id)
    {
        if (!Auth::user()->isEditor()) {
            abort(403);
        }

        $post = Post::onlyTrashed()->findOrFail($id);
        $type = $post->type;
        $this->media->delete($post->featured_image);
        $post->forceDelete();

        return redirect()->route('admin.posts.index', ['type' => $type, 'filter' => 'trash'])
            ->with('success', 'Post permanently deleted.');
    }

    protected function resolvePublishedAt(Request $request, $fallback = null): Carbon
    {
        $date = $request->input('publish_date');
        $time = $request->input('publish_time');

        if ($date && $time) {
            return Carbon::parse("{$date} {$time}");
        }

        if ($date) {
            $baseTime = $fallback ? Carbon::parse($fallback)->format('H:i:s') : now()->format('H:i:s');
            return Carbon::parse("{$date} {$baseTime}");
        }

        return $fallback ? Carbon::parse($fallback) : now();
    }

    protected function validatePost(Request $request): array
    {
        return $request->validate([
            'type' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'sub_title' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'body' => 'nullable|string',
            'featured_image' => 'nullable|image|max:5120',
            'video_url' => 'nullable|string|max:255',
            'focus_keyword' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'seo_score' => 'nullable|integer|min:0|max:100',
            'status' => 'required|in:published,draft,archived',
            'publish_date' => 'nullable|date',
            'publish_time' => 'nullable|string',
        ]);
    }
}
