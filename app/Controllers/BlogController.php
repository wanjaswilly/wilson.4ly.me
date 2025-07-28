<?php

namespace App\Controllers;

use App\Models\BlogPost;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class BlogController
{
    protected $view;
    
    public function __construct(Twig $view)
    {
        $this->view = $view;
    }
    
    public function index(Request $request, Response $response): Response
    {
        $posts = BlogPost::latest()->get();
        return $this->view->render($response, 'admin/index.twig', ['posts' => $posts]);
    }
    
    public function create(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'admin/create.twig');
    }
    
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        $post = new BlogPost();
        $post->title = $data['title'];
        $post->content = $data['content'];
        $post->excerpt = $data['excerpt'] ?? null;
        $post->featured_image = $data['featured_image'] ?? null;
        $post->is_published = isset($data['is_published']);
        
        if ($post->is_published) {
            $post->published_at = Carbon::now();
        }
        
        $post->save();
        
        return $response->withHeader('Location', '/admin/blog')->withStatus(302);
    }
    
    public function show(Request $request, Response $response, array $args): Response
    {
        $post = BlogPost::where('slug', $args['slug'])->firstOrFail();
        return $this->view->render($response, 'blog/show.twig', ['post' => $post]);
    }
    
    public function edit(Request $request, Response $response, array $args): Response
    {
        $post = BlogPost::findOrFail($args['id']);
        return $this->view->render($response, 'admin/edit.twig', ['post' => $post]);
    }
    
    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        
        $post = BlogPost::findOrFail($args['id']);
        $post->title = $data['title'];
        $post->content = $data['content'];
        $post->excerpt = $data['excerpt'] ?? null;
        $post->featured_image = $data['featured_image'] ?? null;
        
        // Handle publish status
        $wasPublished = $post->is_published;
        $post->is_published = isset($data['is_published']);
        
        if ($post->is_published && !$wasPublished) {
            $post->published_at = Carbon::now();
        }
        
        $post->save();
        
        return $response->withHeader('Location', '/admin/blog')->withStatus(302);
    }
    
    public function destroy(Request $request, Response $response, array $args): Response
    {
        $post = BlogPost::findOrFail($args['id']);
        $post->delete();
        
        return $response->withHeader('Location', '/admin/blog')->withStatus(302);
    }
}