<?php

namespace App\Services;

use App\Models\Certificate;

class CertificateNumberGenerator
{
    /**
     * Generate a unique certificate number
     * Format: CERT-YYYYMMDDHHMMSS-XXXXXX
     * 
     * @return string
     */
    public function generate(): string
    {
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(bin2hex(random_bytes(3))); // 6 hex characters
        
        $certificateNumber = "CERT-{$timestamp}-{$random}";
        
        // Ensure uniqueness by checking database
        while (Certificate::where('certificate_number', $certificateNumber)->exists()) {
            $random = strtoupper(bin2hex(random_bytes(3)));
            $certificateNumber = "CERT-{$timestamp}-{$random}";
        }
        
        return $certificateNumber;
    }
}
