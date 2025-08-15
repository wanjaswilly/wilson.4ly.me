<?php

namespace App\Controllers;

use App\Models\Project;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ProjectsController
{

    public function index(Request $request, Response $response): Response
    {
        $projects = Project::ordered()->get();
        $view = Twig::fromRequest($request);

        return $view->render($response, 'projects/index.twig', ['projects' => $projects]);
    }


    public function show(Request $request, Response $response, array $args)
    {
        $project = Project::where('slug', $args['slug'])->firstOrFail();
        $project->technologies = json_decode($project->technologies, true);

        $view = Twig::fromRequest($request);

        return $view->render($response, 'projects/show.twig', ['project' => $project]);
    }
    public function create(Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'projects/create.twig');
    }

    public function edit(Request $request, Response $response, array $args)
    {
        $project = Project::where('slug', $args['slug'])->firstOrFail();

        $view = Twig::fromRequest($request);

        return $view->render($response, 'projects/edit.twig', [
            'project' => $project
        ]);
    }

    public function store(Request $request, Response $response)
{
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    
    // Validate required fields
    if (empty($data['title'])) {
        $response->getBody()->write(json_encode(['error' => 'Title is required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $imagePath = null;
    // Handle image upload
    if (!empty($uploadedFiles['image'])) {
        $imageFile = $uploadedFiles['image'];
        if ($imageFile->getError() === UPLOAD_ERR_OK) {
            $extension = pathinfo($imageFile->getClientFilename(), PATHINFO_EXTENSION);
            $filename = $this->generateSlug($data['title']) . '.' . $extension;
            $uploadDir = __DIR__ . '/../../public/images/projects/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $imageFile->moveTo($uploadDir . $filename);
            $imagePath = '/images/projects/' . $filename;
        }
    }

    $project = Project::create([
        'title' => $data['title'],
        'slug' => $this->generateSlug($data['title']),
        'description' => $data['description'] ?? null,
        'content' => $data['content'] ?? null,
        'image_path' => $imagePath,
        'github_url' => $data['github_url'] ?? null,
        'live_url' => $data['live_url'] ?? null,
        'is_featured' => isset($data['is_featured']),
        'technologies' => isset($data['technologies']) ? json_encode(explode(',', $data['technologies'])) : null,
        'start_date' => $data['start_date'] ?? null,
        'end_date' => $data['end_date'] ?? null
    ]);

    return $response
        ->withHeader('Location', '/projects/' . $project->slug)
        ->withStatus(302);
}

public function update(Request $request, Response $response, array $args)
{
    $project = Project::where('slug', $args['slug'])->firstOrFail();
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    
    $imagePath = $project->image_path;
    // Handle image upload
    if (!empty($uploadedFiles['image'])) {
        $imageFile = $uploadedFiles['image'];
        if ($imageFile->getError() === UPLOAD_ERR_OK) {
            // Delete old image if exists
            if ($imagePath) {
                $oldImagePath = __DIR__ . '/../../../public' . $imagePath;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            $extension = pathinfo($imageFile->getClientFilename(), PATHINFO_EXTENSION);
            $filename = $this->generateSlug($data['title'], $project->id) . '.' . $extension;
            $uploadDir = __DIR__ . '/../../../public/images/projects/';
            
            $imageFile->moveTo($uploadDir . $filename);
            $imagePath = '/images/projects/' . $filename;
        }
    }

    $project->update([
        'title' => $data['title'],
        'slug' => $this->generateSlug($data['title'], $project->id),
        'description' => $data['description'] ?? $project->description,
        'content' => $data['content'] ?? $project->content,
        'image_path' => $imagePath,
        'github_url' => $data['github_url'] ?? $project->github_url,
        'live_url' => $data['live_url'] ?? $project->live_url,
        'is_featured' => isset($data['is_featured']) ? (bool)$data['is_featured'] : $project->is_featured,
        'technologies' => isset($data['technologies']) ? json_encode(explode(',', $data['technologies'])) : $project->technologies,
        'start_date' => $data['start_date'] ?? $project->start_date,
        'end_date' => $data['end_date'] ?? $project->end_date
    ]);

    return $response
        ->withHeader('Location', '/projects/' . $project->slug)
        ->withStatus(302);
}

    public function destroy(Request $request, Response $response, array $args)
    {
        $project = Project::where('slug', $args['slug'])->firstOrFail();
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
