<?php
require_once 'config.php';

header('Content-Type: application/json');

// Get input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['status' => 'ERROR', 'message' => 'Invalid request']);
    exit;
}

// Prepare data for API call
$postData = [];
if (isset($input['qr_data'])) {
    $postData['qr_data'] = $input['qr_data'];
} elseif (isset($input['certificate_number'])) {
    $postData['certificate_number'] = $input['certificate_number'];
} else {
    echo json_encode(['status' => 'ERROR', 'message' => 'Missing verification data']);
    exit;
}

// Call main application API
$ch = curl_init(API_ENDPOINT);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 seconds timeout

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Log for debugging
error_log("API Call - HTTP Code: $httpCode, Response: $response, cURL Error: $curlError");

if ($response === false || $httpCode === 0) {
    echo json_encode([
        'status' => 'ERROR', 
        'message' => 'Cannot connect to verification service. Please ensure the main application is running.',
        'debug' => $curlError
    ]);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode([
        'status' => 'ERROR', 
        'message' => 'Verification service returned error (HTTP ' . $httpCode . ')',
        'debug' => $response
    ]);
    exit;
}

// Return API response
echo $response;
