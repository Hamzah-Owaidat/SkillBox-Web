<?php

namespace App\Controllers\Api;

class FileController
{
    public function serveCv($path = null)
    {
        // Enable error logging
        error_log("=== CV Request Received ===");
        error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
        
        // Get everything after /api/cv/ from the URL
        $requestUri = $_SERVER['REQUEST_URI'];
        
        // Remove query string if present
        $requestUri = explode('?', $requestUri)[0];
        
        // Extract the file path after /api/cv/
        if (preg_match('#/api/cv/(.+)$#', $requestUri, $matches)) {
            $relativePath = urldecode($matches[1]);
        } else {
            error_log("ERROR: Could not extract path from URL");
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'File path not found in URL']);
            exit;
        }
        
        error_log("Relative path: " . $relativePath);
        
        // Construct full path - uploads is in public folder
        // __DIR__ is app/Controllers/Api, so go back to public
        $filePath = __DIR__ . '/../../../public/uploads/' . $relativePath;
        
        error_log("Full file path: " . $filePath);
        
        // Get real path (resolves .. and symlinks)
        $realPath = realpath($filePath);
        $uploadsDir = realpath(__DIR__ . '/../../../public/uploads/');
        
        error_log("Real path: " . ($realPath ?: 'NULL'));
        error_log("Uploads dir: " . $uploadsDir);
        
        // Check if uploads directory exists
        if (!$uploadsDir) {
            error_log("ERROR: Uploads directory not found");
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Uploads directory not configured']);
            exit;
        }
        
        // Security check - prevent directory traversal
        if (!$realPath || strpos($realPath, $uploadsDir) !== 0) {
            error_log("ERROR: Security check failed");
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Access denied',
                'debug' => [
                    'realPath' => $realPath,
                    'uploadsDir' => $uploadsDir
                ]
            ]);
            exit;
        }
        
        // Check if file exists
        if (!file_exists($realPath)) {
            error_log("ERROR: File does not exist");
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'File not found',
                'path' => $relativePath
            ]);
            exit;
        }
        
        error_log("SUCCESS: File found, serving...");
        
        // Get file info
        $mimeType = mime_content_type($realPath);
        $fileSize = filesize($realPath);
        $filename = basename($realPath);
        
        error_log("MIME type: " . $mimeType);
        error_log("File size: " . $fileSize);
        
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . $fileSize);
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Cache-Control: public, max-age=3600');
        header('Accept-Ranges: bytes');
        
        // Output file
        readfile($realPath);
        exit;
    }
}