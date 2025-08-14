<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';
        protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'image_url',
        'github_url',
        'live_url',
        'is_featured',
        'sort_order',
        'technologies',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'technologies' => 'array',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}