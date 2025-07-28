<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $table = 'blog_posts';
    
    protected $fillable = [
        'title',
        'slug',
        'content',
        'featured_image',
        'excerpt',
        'is_published',
        'published_at'
    ];
    
    protected $dates = [
        'published_at',
        'created_at',
        'updated_at'
    ];
    
    protected $casts = [
        'is_published' => 'boolean'
    ];
    
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->slug = $model->generateSlug($model->title);
        });
        
        static::updating(function ($model) {
            if ($model->isDirty('title')) {
                $model->slug = $model->generateSlug($model->title);
            }
        });
    }
    
    protected function generateSlug($title)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $count = BlogPost::where('slug', 'LIKE', "{$slug}%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }
}