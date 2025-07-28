<?php

namespace App\Repositories;

use App\Models\BlogPost;
use Carbon\Carbon;

class BlogPostRepository
{
    public function all()
    {
        return BlogPost::latest()->get();
    }
    
    public function find($id)
    {
        return BlogPost::findOrFail($id);
    }
    
    public function findBySlug($slug)
    {
        return BlogPost::where('slug', $slug)->firstOrFail();
    }
    
    public function create(array $data)
    {
        $post = new BlogPost($data);
        
        if ($data['is_published'] ?? false) {
            $post->published_at = Carbon::now();
        }
        
        $post->save();
        
        return $post;
    }
    
    public function update($id, array $data)
    {
        $post = $this->find($id);
        
        $post->fill($data);
        
        if (($data['is_published'] ?? false) && !$post->published_at) {
            $post->published_at = Carbon::now();
        }
        
        $post->save();
        
        return $post;
    }
    
    public function delete($id)
    {
        $post = $this->find($id);
        return $post->delete();
    }
}