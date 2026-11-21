<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CertificateEncryptionService;
use App\Services\PublicCertificateVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Certificate verification for the marketing website's /verify page.
 *
 * The page on www.e-certificate.com.my calls this from the visitor's browser, so
 * the per-IP rate limit applies to each visitor rather than to one proxy server.
 * It answers exactly what the public /verify page on this app shows, using the
 * same lookup and rows (PublicCertificateVerifier), and nothing more.
 *
 * A visitor may supply either the number printed on the certificate or the
 * encrypted payload from the certificate's QR code. The payload can only be read
 * with this app's key, which is why the decryption happens here and not in the
 * browser.
 *
 * Unknown numbers, deleted certificates and payloads that fail decryption all
 * answer the same "not_found", with 200, so the response says nothing about why
 * a lookup failed. Nothing is logged per request, matching both existing
 * verification controllers.
 */
class PublicCertificateVerificationController extends Controller
{
    /**
     * A Laravel encrypted payload is base64 of a JSON object that starts with
     * {"iv":, so it always begins "eyJpdiI6". The length cap is far above the
     * size of a real QR payload and stops oversized input reaching decryption.
     */
    private const QR_PATTERN = '/^eyJpdiI6[A-Za-z0-9+\/]+={0,2}$/';

    private const QR_MAX_LENGTH = 4096;

    public function verify(
        Request $request,
        PublicCertificateVerifier $verifier,
        CertificateEncryptionService $encryption,
    ): JsonResponse {
        $number = $request->query('number');
        $qr = $request->query('qr');

        // Exactly one of the two, and each a single string. Anything else is a
        // malformed request rather than an unknown certificate.
        if (($number === null) === ($qr === null)) {
            return $this->invalidRequest();
        }

        if ($number !== null) {
            if (! is_string($number)) {
                return $this->invalidRequest();
            }

            $number = strtoupper(trim($number));

            if (! preg_match(PublicCertificateVerifier::NUMBER_PATTERN, $number)) {
                return $this->invalidRequest();
            }
        } else {
            if (! is_string($qr)) {
                return $this->invalidRequest();
            }

            $qr = trim($qr);

            if (strlen($qr) > self::QR_MAX_LENGTH || ! preg_match(self::QR_PATTERN, $qr)) {
                return $this->invalidRequest();
            }

            // decrypt() also checks the payload's HMAC, so a forged or edited
            // payload comes back null and reads as not found.
            //
            // The app encrypts other strings with the same key, the session cookie
            // among them. Those decrypt fine but are not JSON, and decrypt() then
            // hands null to a method typed for an array. That TypeError is not an
            // \Exception, so decrypt() does not catch it; catching it here keeps a
            // visitor's own cookie from turning into a server error.
            try {
                $data = $encryption->decrypt($qr);
            } catch (\TypeError) {
                $data = null;
            }

            $number = is_array($data) ? ($data['certificate_number'] ?? null) : null;

            if (! is_string($number) || ! preg_match(PublicCertificateVerifier::NUMBER_PATTERN, $number)) {
                return $this->respond(['status' => 'not_found']);
            }
        }

        $certificate = $verifier->find($number);

        if (! $certificate) {
            return $this->respond(['status' => 'not_found']);
        }

        return $this->respond([
            'status' => 'valid',
            'certificate' => $verifier->publicRecord($certificate),
        ]);
    }

    private function invalidRequest(): JsonResponse
    {
        return $this->respond([
            'status' => 'invalid_request',
            'message' => 'Provide one certificate number in the format CERT-YYYYMMDDHHMMSS-XXXXXX.',
        ], 422);
    }

    /**
     * A verification answer is true only at the moment it is given, and it names a
     * person, so neither the browser nor anything in between should keep a copy.
     */
    private function respond(array $body, int $status = 200): JsonResponse
    {
        return response()->json($body, $status, ['Cache-Control' => 'no-store']);
    }
}
