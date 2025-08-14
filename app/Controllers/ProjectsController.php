<?php

namespace App\Controllers;

use App\Models\Project;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;
use Slim\Psr7\Request;
use Slim\Views\Twig;

class ProjectsController
{

    public function index(Request $request, Response $response): Response
    {
        $projects = Project::ordered()->get();
        $view = Twig::fromRequest($request);

        return $view->render($response, 'projects/blog.twig', ['projects' => $projects]);
    }


    public function show(Request $request, Response $response, $slug)
    {
        $project = Project::where('slug', $slug)->firstOrFail();

        $view = Twig::fromRequest($request);

        return $view->render($response, 'projects/blog.twig', ['project' => $project]);
    }
    public function create(Request $request, Response $response, $slug)
    {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'projects/create.twig');
    }

    public function edit(Request $request, Response $response, $slug)
    {
        $project = Project::where('slug', $slug)->firstOrFail();

        $view = Twig::fromRequest($request);

        return $view->render($response, 'projects/create.twig', [
            'project' => $project
        ]);
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        $project = Project::create([
            'title' => $data['title'],
            'slug' => $this->generateSlug($data['title']),
            'description' => $data['description'],
            'content' => $data['content'],
            'github_url' => $data['github_url'] ?? null,
            'live_url' => $data['live_url'] ?? null,
            'is_featured' => isset($data['is_featured']),
            'technologies' => isset($data['technologies']) ? json_encode(explode(',', $data['technologies'])) : null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null
        ]);

        // Handle image upload if needed

        return $response
            ->withHeader('Location', '/projects/' . $project->slug)
            ->withStatus(302);
    }

    public function update(Request $request, Response $response, $slug)
    {
        $project = Project::where('slug', $slug)->firstOrFail();
        $data = $request->getParsedBody();

        $project->update([
            'title' => $data['title'],
            'slug' => $this->generateSlug($data['title'], $project->id),
            'description' => $data['description'],
            'content' => $data['content'],
            'github_url' => $data['github_url'] ?? null,
            'live_url' => $data['live_url'] ?? null,
            'is_featured' => isset($data['is_featured']),
            'technologies' => isset($data['technologies']) ? json_encode(explode(',', $data['technologies'])) : null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null
        ]);

        // Handle image upload if needed

        return $response
            ->withHeader('Location', '/projects/' . $project->slug)
            ->withStatus(302);
    }

    public function destroy(Request $request, Response $response, $slug)
    {
        $project = Project::where('slug', $slug)->firstOrFail();
        $project->delete();

        return $response
            ->withHeader('Location', '/projects')
            ->withStatus(302);
    }


    private function generateSlug($title, $id = null)
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));

        $query = Project::where('slug', $slug);
        if ($id) {
            $query->where('id', '!=', $id);
        }

        if ($query->exists()) {
            $slug .= '-' . uniqid();
        }

        return $slug;
    }
}
