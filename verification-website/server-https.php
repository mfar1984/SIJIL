<?php
/**
 * Simple HTTPS server for certificate verification website
 * 
 * Usage:
 * 1. Generate self-signed certificate:
 *    openssl req -x509 -newkey rsa:4096 -keyout key.pem -out cert.pem -days 365 -nodes
 * 
 * 2. Run this server:
 *    php server-https.php
 * 
 * 3. Access via: https://192.168.1.33:8443
 *    (Accept the security warning for self-signed certificate)
 */

$host = '0.0.0.0'; // Listen on all interfaces
$port = 8443;
$certFile = __DIR__ . '/cert.pem';
$keyFile = __DIR__ . '/key.pem';

// Check if certificate files exist
if (!file_exists($certFile) || !file_exists($keyFile)) {
    echo "Error: SSL certificate files not found!\n\n";
    echo "Please generate self-signed certificate first:\n";
    echo "openssl req -x509 -newkey rsa:4096 -keyout key.pem -out cert.pem -days 365 -nodes\n\n";
    echo "When prompted, you can press Enter for all questions or fill in:\n";
    echo "- Country Name: MY\n";
    echo "- Common Name: 192.168.1.33 (your IP address)\n";
    exit(1);
}

$context = stream_context_create([
    'ssl' => [
        'local_cert' => $certFile,
        'local_pk' => $keyFile,
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
    ]
]);

$socket = stream_socket_server(
    "ssl://{$host}:{$port}",
    $errno,
    $errstr,
    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
    $context
);

if (!$socket) {
    die("Failed to create server: $errstr ($errno)\n");
}

echo "HTTPS Server running on https://192.168.1.33:{$port}\n";
echo "Press Ctrl+C to stop\n\n";

while ($conn = stream_socket_accept($socket, -1)) {
    $request = fread($conn, 8192);
    
    // Parse request
    preg_match('/^GET (.*?) HTTP/', $request, $matches);
    $path = $matches[1] ?? '/';
    
    // Remove query string
    $path = strtok($path, '?');
    
    // Default to index.php
    if ($path === '/') {
        $path = '/index.php';
    }
    
    $filePath = __DIR__ . $path;
    
    // Security: prevent directory traversal
    $realPath = realpath($filePath);
    if ($realPath === false || strpos($realPath, __DIR__) !== 0) {
        $response = "HTTP/1.1 403 Forbidden\r\n\r\n";
        fwrite($conn, $response);
        fclose($conn);
        continue;
    }
    
    if (file_exists($filePath) && is_file($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $contentType = match($ext) {
            'html', 'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => 'text/plain',
        };
        
        // Execute PHP files
        if ($ext === 'php') {
            ob_start();
            include $filePath;
            $content = ob_get_clean();
        } else {
            $content = file_get_contents($filePath);
        }
        
        $response = "HTTP/1.1 200 OK\r\n";
        $response .= "Content-Type: {$contentType}\r\n";
        $response .= "Content-Length: " . strlen($content) . "\r\n";
        $response .= "Connection: close\r\n\r\n";
        $response .= $content;
    } else {
        $response = "HTTP/1.1 404 Not Found\r\n\r\n404 Not Found";
    }
    
    fwrite($conn, $response);
    fclose($conn);
}

fclose($socket);
