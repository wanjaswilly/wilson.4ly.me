<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class SiteController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'admin/dashboard.twig');
    }
}