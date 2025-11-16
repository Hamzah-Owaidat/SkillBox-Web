<?php

// Get the file path from query parameter
$relativePath = $_GET['path'] ?? '';

if (empty($relativePath)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No file path provided']);
    exit;
}

// Decode URL encoding
$relativePath = urldecode($relativePath);

// Log for debugging
error_log("=== CV Serve Request ===");
error_log("Relative path: " . $relativePath);

// Construct full path - uploads is in public folder
$filePath = __DIR__ . '/uploads/' . $relativePath;

error_log("Full file path: " . $filePath);

// Get real path (resolves .. and symlinks)
$realPath = realpath($filePath);
$uploadsDir = realpath(__DIR__ . '/uploads/');

error_log("Real path: " . ($realPath ?: 'NULL'));
error_log("Uploads dir: " . $uploadsDir);

// Security check - prevent directory traversal
if (!$realPath || !$uploadsDir || strpos($realPath, $uploadsDir) !== 0) {
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
        'path' => $relativePath,
        'tried' => $filePath
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

// Set headers for PDF viewing
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $fileSize);
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Cache-Control: public, max-age=3600');
header('Accept-Ranges: bytes');

// Output file
readfile($realPath);
exit;
