<?php

use App\Controllers\BlogController;
use App\Controllers\MediaController;
use App\Controllers\ProjectsController;
use App\Controllers\SiteController;
use App\Models\BlogPost;
use App\Models\SiteStat;
use Slim\App;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    // Static core routes
    $app->get('/', function (Request $request, Response $response, $args) {
        $posts = BlogPost::orderBy('published_at', 'desc')->take(6)->get();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'pages/home.twig', ['posts' => $posts]);
    });

    // Public routes
    $app->post('/subscribe', [SiteController::class, 'subscribe'])->setName('subscribe');
    $app->post('/contact', [SiteController::class, 'saveContact'])->setName('contact.submit');
    $app->get('/blog', [BlogController::class, 'showAll'])->setName('blog');
    $app->get('/blog/{slug}', [BlogController::class, 'show'])->setName('blog.show');
    $app->post('/upload-image', [MediaController::class, 'uploadImage']);

    // Project routes
    $app->get('/projects', [ProjectsController::class, 'showAll'])->setName('projects.show-all');
    $app->get('/projects/{slug}', [ProjectsController::class, 'show'])->setName('projects.show');

    # Admin projects routes
    $app->group('/admin/projects', function ($group) {
        $group->get('', [ProjectsController::class, 'index'])->setName('projects.index');
        $group->get('/create', [ProjectsController::class, 'create'])->setName('projects.create');
        $group->post('/save', [ProjectsController::class, 'store'])->setName('projects.save');
        $group->get('/{slug}/edit', [ProjectsController::class, 'edit'])->setName('projects.edit');
        $group->put('/{slug}/update', [ProjectsController::class, 'update'])->setName('projects.update');
        $group->delete('/{slug}', [ProjectsController::class, 'destroy'])->setName('projects.destroy');
    });


    # Admin routes
    $app->group('/admin', function ($group) {
        $group->get('', [SiteController::class, 'index'])->setName('admin');
    });

    # Admin blog routes
    $app->group('/admin/blog', function ($group) {
        $group->get('', [BlogController::class, 'index'])->setName('admin.blog');
        $group->get('/create', [BlogController::class, 'create'])->setName('admin.blog.create');
        $group->post('/store', [BlogController::class, 'store'])->setName('admin.blog.store');
        $group->get('/{id}/edit', [BlogController::class, 'edit'])->setName('admin.blog.edit');
        $group->post('/save-edit/{id}', [BlogController::class, 'update'])->setName('admin.blog.update');
        $group->delete('/{id}', [BlogController::class, 'destroy'])->setName('admin.blog.destroy');
    });

    //  AUTO-GENERATED ROUTES - DO NOT REMOVE THIS LINE
    // $app->get('/example', fn($req, $res) => $this->get(Twig::class)->render($res, 'pages/example.twig'));

    // Test 500 error
    $app->get('/test-500', function () {
        throw new \Exception("Intentional test error");
    });

    $app->get('/admin/stats', function ($request, $response) {
        $view = \Slim\Views\Twig::fromRequest($request);
        $stats = SiteStat::orderBy('visited_at', 'desc')->limit(100)->get();
        return $view->render($response, 'admin/stats.twig', ['stats' => $stats]);
    });

    // Route for 'contact'
    $app->get('/contact', function ($request, $response) {
        $view = \Slim\Views\Twig::fromRequest($request);
        return $view->render($response, "pages/contact.twig");
    });
};
