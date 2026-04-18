<?php

// Auto-detect API endpoint based on environment
function getApiEndpoint() {
    $hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Production
    if (strpos($hostname, 'e-certificate.com.my') !== false) {
        return 'https://apps.e-certificate.com.my/api/certificate/verify';
    }
    
    // Development
    return 'http://localhost:8000/api/certificate/verify';
}

define('API_ENDPOINT', getApiEndpoint());

// Other configuration as needed
