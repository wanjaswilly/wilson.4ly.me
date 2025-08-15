<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;

class MediaController
{
    public function uploadImage(Request $request, Response $response)
    {
        $uploadedFiles = $request->getUploadedFiles();
        
        // Check if image was uploaded
        if (empty($uploadedFiles['image'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'No image file uploaded'
            ], 400);
        }

        $imageFile = $uploadedFiles['image'];
        
        // Validate the upload
        if ($imageFile->getError() !== UPLOAD_ERR_OK) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => $this->getUploadError($imageFile->getError())
            ], 400);
        }

        // Get file info
        $clientFilename = $imageFile->getClientFilename();
        $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Validate file extension
        if (!in_array($extension, $allowedExtensions)) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.'
            ], 400);
        }

        // Validate file size (max 5MB)
        if ($imageFile->getSize() > 5 * 1024 * 1024) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'File too large. Maximum size is 5MB.'
            ], 400);
        }

        // Generate unique filename with date-based subdirectories
        $datePath = date('Y-m-d');
        $filename = uniqid('img_') . '.' . $extension;
        $uploadDir = __DIR__ . '/../../public/images/uploads/' . $datePath . '/';

        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        try {
            // Move uploaded file
            $imageFile->moveTo($uploadDir . $filename);
            
            return $this->jsonResponse($response, [
                'success' => true,
                'filePath' => '/images/uploads/' . $datePath . '/' . $filename
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Failed to save image: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getUploadError(int $code): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by PHP extension',
        ];
        
        return $errors[$code] ?? 'Unknown upload error';
    }

    private function jsonResponse(Response $response, array $data, int $statusCode = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}