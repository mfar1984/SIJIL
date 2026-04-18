<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class CertificateEncryptionService
{
    /**
     * Encrypt certificate data for QR code
     * 
     * @param array $data Certificate data
     * @return string Encrypted payload
     */
    public function encrypt(array $data): string
    {
        // Add verification hash
        $data['hash'] = $this->generateHash($data);
        
        // Encrypt using Laravel's Crypt facade (AES-256-CBC)
        $encrypted = Crypt::encryptString(json_encode($data));
        
        return $encrypted;
    }
    
    /**
     * Decrypt certificate data from QR code
     * 
     * @param string $encrypted Encrypted payload
     * @return array|null Decrypted data or null if invalid
     */
    public function decrypt(string $encrypted): ?array
    {
        try {
            $decrypted = Crypt::decryptString($encrypted);
            $data = json_decode($decrypted, true);
            
            // Verify hash
            if (!$this->verifyHash($data)) {
                return null;
            }
            
            return $data;
        } catch (\Exception $e) {
            Log::error('Certificate decryption failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Generate verification hash
     * 
     * @param array $data Certificate data
     * @return string Hash
     */
    public function generateHash(array $data): string
    {
        // Create hash from certificate data (excluding hash field itself)
        $hashData = [
            'certificate_number' => $data['certificate_number'] ?? '',
            'participant_name' => $data['participant_name'] ?? '',
            'event_name' => $data['event_name'] ?? '',
        ];
        
        return hash_hmac('sha256', json_encode($hashData), config('app.key'));
    }
    
    /**
     * Verify hash integrity
     * 
     * @param array $data Certificate data with hash
     * @return bool
     */
    public function verifyHash(array $data): bool
    {
        if (!isset($data['hash'])) {
            return false;
        }
        
        $providedHash = $data['hash'];
        unset($data['hash']);
        
        $expectedHash = $this->generateHash($data);
        
        return hash_equals($expectedHash, $providedHash);
    }
}
