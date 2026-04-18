<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\CertificateEncryptionService;
use Illuminate\Http\Request;

class CertificateVerificationController extends Controller
{
    /**
     * Verify certificate by QR code data or certificate number
     */
    public function verify(Request $request)
    {
        $request->validate([
            'qr_data' => 'required_without:certificate_number|string',
            'certificate_number' => 'required_without:qr_data|string',
        ]);
        
        $certificateNumber = null;
        
        // If QR data provided, decrypt it
        if ($request->has('qr_data')) {
            $encryptionService = app(CertificateEncryptionService::class);
            $decryptedData = $encryptionService->decrypt($request->qr_data);
            
            if (!$decryptedData) {
                return response()->json([
                    'status' => 'INVALID',
                    'message' => 'Invalid QR code data'
                ]);
            }
            
            $certificateNumber = $decryptedData['certificate_number'];
        } else {
            $certificateNumber = $request->certificate_number;
        }
        
        // Query database for certificate
        $certificate = Certificate::where('certificate_number', $certificateNumber)
            ->with(['event', 'participant'])
            ->first();
        
        if (!$certificate) {
            return response()->json([
                'status' => 'INVALID',
                'message' => 'Certificate not found'
            ]);
        }
        
        // Return certificate details
        return response()->json([
            'status' => 'VALID',
            'data' => [
                'certificate_number' => $certificate->certificate_number,
                'participant_name' => $certificate->participant->name,
                'event_name' => $certificate->event->name,
                'event_date' => $certificate->event->start_date->format('d F Y'),
                'event_time' => $certificate->event->start_time ? substr($certificate->event->start_time, 0, 5) : 'N/A',
            ]
        ]);
    }
}
