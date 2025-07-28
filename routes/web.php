<?php

use App\Controllers\BlogController;
use App\Models\SiteStat;
use Slim\App;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    // Static core routes
    $pages = [
        '/'          => 'home',

    ];
    foreach ($pages as $route => $template) {
        $app->get($route, function (Request $request, Response $response) use ($template) {
            $view = Twig::fromRequest($request);
            return $view->render($response, "pages/{$template}.twig");
        });
    }

     // Public routes
    $app->get('/blog', [BlogController::class, 'showAll']);
    $app->get('/blog/{slug}', [BlogController::class, 'show']);
    
    // Admin routes
    $app->group('/admin/blog', function ($group) {
        $group->get('', [BlogController::class, 'index']);
        $group->get('/create', [BlogController::class, 'create']);
        $group->post('', [BlogController::class, 'store']);
        $group->get('/{id}/edit', [BlogController::class, 'edit']);
        $group->put('/{id}', [BlogController::class, 'update']);
        $group->delete('/{id}', [BlogController::class, 'destroy']);
    });

    //  AUTO-GENERATED ROUTES - DO NOT REMOVE THIS LINE
    // $app->get('/example', fn($req, $res) => $this->get(Twig::class)->render($res, 'pages/example.twig'));

    // Test 500 error
    $app->get('/test-500', function () {
        throw new \Exception("Intentional test error");
    });

    $app->get('/stats', function ($request, $response) {
        $view = \Slim\Views\Twig::fromRequest($request);
        $stats = SiteStat::orderBy('visited_at', 'desc')->limit(100)->get();
        return $view->render($response, 'pages/stats.twig', ['stats' => $stats]);
    });
};
