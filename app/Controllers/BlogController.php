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
    
    public function __construct()
    {
    }
    
    public function index(Request $request, Response $response): Response
    {
        $posts = BlogPost::latest()->get();
        $view = Twig::fromRequest($request);

        return $view->render($response, 'admin/blog.twig', ['posts' => $posts]);
    }

    public function showAll(Request $request, Response $response): Response
    {
        $posts = BlogPost::where('is_published', true)->latest()->get();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'blog/index.twig', ['posts' => $posts]);
    }
    
    public function create(Request $request, Response $response): Response
    {

        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/create.twig');
    }
    
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        $post = new BlogPost();
        $post->title = $data['title'];
        // Generate a URL-safe slug from the title
        $post->slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', trim($data['title'])));
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

        $view = Twig::fromRequest($request);
        return $view->render($response, 'blog/show.twig', ['post' => $post]);
    }
    
    public function edit(Request $request, Response $response, array $args): Response
    {
        $post = BlogPost::findOrFail($args['id']);

        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/edit.twig', ['post' => $post]);
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