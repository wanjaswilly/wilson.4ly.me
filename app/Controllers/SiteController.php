<?php

namespace App\Controllers;

use App\Models\BlogPost;
use App\Models\Message;
use App\Models\SiteStat;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class SiteController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        return $view->render(
            $response,
            'admin/dashboard.twig',
            [
                'title' => 'Dashboard',
                'message' => 'Welcome to the admin dashboard!',
                'stats' => SiteStat::all(),
                'posts' => BlogPost::orderByDesc('created_at')->get(),
                'messages' => Message::orderByDesc('created_at')->get(),
            ]
        );
    }
}
