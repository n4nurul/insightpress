<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Post extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id', 'category_id', 'type', 'sub_title', 'title', 'slug',
        'excerpt', 'body', 'featured_image', 'video_url', 'is_breaking',
        'is_featured', 'views_count', 'meta_data', 'focus_keyword',
        'meta_title', 'meta_description', 'seo_score', 'status', 'published_at'
    ];

    protected $casts = [
        'is_breaking' => 'boolean',
        'is_featured' => 'boolean',
        'meta_data' => 'array',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (Post $post) {
            if ($post->category_id) {
                $post->categories()->syncWithoutDetaching([$post->category_id]);
            }
            Cache::flush();
        });
        static::deleted(fn () => Cache::flush());
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_post');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeInCategory($query, int $categoryId)
    {
        return $query->where(function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId)
              ->orWhereHas('categories', fn ($cq) => $cq->where('categories.id', $categoryId));
        });
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where(function ($q) {
                         $q->whereNull('published_at')->orWhere('published_at', '<=', now());
                     });
    }
}
