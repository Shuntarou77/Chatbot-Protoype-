<?php
/**
 * PHP built-in server router.
 * Routes /api/* requests to the api/ folder outside public/.
 * Run with: php -S localhost:8000 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route /api/* to the api/ directory
if (str_starts_with($uri, '/api/')) {
    $file = __DIR__ . $uri;
    if (file_exists($file)) {
        require $file;
        return true;
    }
    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not found']);
    return true;
}

// Serve static files from public/
$publicFile = __DIR__ . '/public' . $uri;

if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    return false; // Let PHP serve the file normally
}

// Default: serve public/index.html
$indexFile = __DIR__ . '/public/index.html';
if (file_exists($indexFile)) {
    header('Content-Type: text/html');
    readfile($indexFile);
    return true;
}

http_response_code(404);
echo '404 Not Found';
