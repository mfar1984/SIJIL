<?php

namespace Tests\Feature;

use App\Services\CertificateEncryptionService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\CreatesCertificateVerificationSchema;
use Tests\TestCase;

/**
 * The public verification API the marketing website calls from the browser.
 *
 * Runs against an in-memory SQLite schema with fake sample data only. Covers the
 * JSON contract the marketing page depends on, the input rules, the CORS
 * allow-list (and that it never widens to "*"), the rate limit, and that every
 * other API route keeps Laravel's existing CORS behaviour.
 */
class PublicCertificateVerificationApiTest extends TestCase
{
    use CreatesCertificateVerificationSchema;

    private const URL = '/api/public/v1/certificates/verify';

    private const WWW = 'https://www.e-certificate.com.my';

    private const APEX = 'https://e-certificate.com.my';

    private const LOCAL = 'http://127.0.0.1:4173';

    private const INVALID_MESSAGE = 'Provide one certificate number in the format CERT-YYYYMMDDHHMMSS-XXXXXX.';

    protected function setUp(): void
    {
        parent::setUp();

        // QR payloads are encrypted with the app key. A throwaway key is used when
        // the environment provides none, so the tests never depend on a real .env.
        if (! config('app.key')) {
            config(['app.key' => 'base64:' . base64_encode(str_repeat('t', 32))]);
        }

        $this->createVerificationSchema();
        $this->seedSampleCertificates();
    }

    public function test_a_valid_number_returns_the_public_fields_in_page_order(): void
    {
        $response = $this->verifyNumber(self::SAMPLE_NUMBER);

        $response->assertOk()->assertExactJson([
            'status' => 'valid',
            'certificate' => $this->sampleRecord(),
        ]);

        $this->assertSame(
            ['awarded_to', 'event', 'held_on', 'location', 'issued_by', 'issued_on', 'certificate_number'],
            array_keys($response->json('certificate'))
        );

        $this->assertNoStore($response);
    }

    public function test_a_lowercase_padded_number_is_normalised(): void
    {
        $response = $this->getJson(self::URL . '?number=' . rawurlencode('  ' . strtolower(self::SAMPLE_NUMBER) . ' '));

        $response->assertOk()->assertExactJson(['status' => 'valid', 'certificate' => $this->sampleRecord()]);
    }

    public function test_the_encrypted_qr_payload_is_decrypted_and_verified(): void
    {
        $response = $this->verifyQr($this->samplePayload());

        $response->assertOk()->assertExactJson(['status' => 'valid', 'certificate' => $this->sampleRecord()]);
        $this->assertNoStore($response);
    }

    public function test_a_tampered_qr_payload_is_not_found(): void
    {
        Log::spy();

        $payload = $this->samplePayload();
        $position = intdiv(strlen($payload), 2);
        $payload[$position] = $payload[$position] === 'A' ? 'B' : 'A';

        $response = $this->verifyQr($payload);

        $response->assertOk()->assertExactJson(['status' => 'not_found']);
        $this->assertNoStore($response);

        // The existing decrypt() logs the failure with the exception message only.
        Log::shouldHaveReceived('error')->with('Certificate decryption failed', \Mockery::on(
            fn ($context) => array_keys($context) === ['error']
        ));
    }

    public function test_another_value_encrypted_with_the_app_key_is_not_found(): void
    {
        // The session cookie, for example, is encrypted with the same key but is
        // not the JSON a certificate QR carries.
        $response = $this->verifyQr(Crypt::encryptString('not a certificate payload'));

        $response->assertOk()->assertExactJson(['status' => 'not_found']);
    }

    public function test_an_unknown_number_is_not_found(): void
    {
        $response = $this->verifyNumber(self::UNKNOWN_NUMBER);

        $response->assertOk()->assertExactJson(['status' => 'not_found']);
        $this->assertNoStore($response);
    }

    public function test_a_deleted_certificate_is_not_found(): void
    {
        $this->verifyNumber(self::TRASHED_NUMBER)
            ->assertOk()
            ->assertExactJson(['status' => 'not_found']);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function malformedQueries(): array
    {
        return [
            'malformed number' => ['number=CERT-123'],
            'thirteen timestamp digits' => ['number=CERT-2026031309000-A1B2C3'],
            'both parameters' => ['number=' . self::SAMPLE_NUMBER . '&qr=eyJpdiI6abc'],
            'no parameters' => [''],
            'empty number' => ['number='],
            'number as an array' => ['number[]=x'],
            'qr as an array' => ['qr[]=eyJpdiI6abc'],
            'qr over 4096 characters' => ['qr=eyJpdiI6' . str_repeat('A', 4100)],
            'qr without the payload prefix' => ['qr=' . rawurlencode('https://evil.example/verify?number=' . self::SAMPLE_NUMBER)],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('malformedQueries')]
    public function test_malformed_requests_are_rejected(string $query): void
    {
        $response = $this->getJson(self::URL . ($query === '' ? '' : '?' . $query));

        $response->assertStatus(422)->assertExactJson([
            'status' => 'invalid_request',
            'message' => self::INVALID_MESSAGE,
        ]);

        $this->assertNoStore($response);
    }

    public function test_post_is_not_allowed(): void
    {
        $this->postJson(self::URL, ['number' => self::SAMPLE_NUMBER])->assertStatus(405);
    }

    public function test_the_marketing_origins_receive_their_exact_origin(): void
    {
        foreach ([self::WWW, self::APEX] as $origin) {
            $response = $this->verifyNumber(self::SAMPLE_NUMBER, $origin);

            $response->assertOk()
                ->assertHeader('Access-Control-Allow-Origin', $origin)
                ->assertHeader('Access-Control-Expose-Headers', 'Retry-After')
                ->assertHeaderMissing('Access-Control-Allow-Credentials');

            $this->assertStringContainsString('Origin', (string) $response->headers->get('Vary'));
        }
    }

    public function test_a_foreign_origin_receives_no_cors_headers(): void
    {
        $response = $this->verifyNumber(self::SAMPLE_NUMBER, 'https://evil.example');

        $response->assertOk()
            ->assertHeaderMissing('Access-Control-Allow-Origin')
            ->assertHeaderMissing('Access-Control-Allow-Credentials');

        $this->assertStringContainsString('Origin', (string) $response->headers->get('Vary'));
    }

    public function test_the_local_preview_origin_is_refused_outside_local(): void
    {
        $this->assertSame('testing', $this->app->environment());

        $this->verifyNumber(self::SAMPLE_NUMBER, self::LOCAL)
            ->assertOk()
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_the_local_preview_origin_is_allowed_in_local(): void
    {
        $this->app['env'] = 'local';

        $this->verifyNumber(self::SAMPLE_NUMBER, self::LOCAL)
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', self::LOCAL);
    }

    public function test_a_wildcard_in_the_allow_list_is_never_sent(): void
    {
        config(['public_api.cors.allowed_origins' => ['*', 'https://*.e-certificate.com.my', self::WWW . '/path', self::WWW]]);

        $this->verifyNumber(self::SAMPLE_NUMBER, 'https://evil.example')
            ->assertHeaderMissing('Access-Control-Allow-Origin');

        $this->verifyNumber(self::SAMPLE_NUMBER, 'https://app.e-certificate.com.my')
            ->assertHeaderMissing('Access-Control-Allow-Origin');

        $this->verifyNumber(self::SAMPLE_NUMBER, self::WWW)
            ->assertHeader('Access-Control-Allow-Origin', self::WWW);
    }

    public function test_a_preflight_from_an_allowed_origin_is_answered_with_204(): void
    {
        $response = $this->preflight(self::URL, self::WWW, 'GET');

        $response->assertNoContent(204)
            ->assertHeader('Access-Control-Allow-Origin', self::WWW)
            ->assertHeader('Access-Control-Allow-Methods', 'GET')
            ->assertHeader('Access-Control-Allow-Headers', 'Accept')
            ->assertHeader('Access-Control-Max-Age', '600')
            ->assertHeaderMissing('Access-Control-Allow-Credentials');

        $this->assertStringContainsString('Origin', (string) $response->headers->get('Vary'));
    }

    public function test_a_preflight_from_a_foreign_origin_or_for_another_method_gets_no_cors_headers(): void
    {
        $this->preflight(self::URL, 'https://evil.example', 'GET')
            ->assertNoContent(204)
            ->assertHeaderMissing('Access-Control-Allow-Origin');

        $this->preflight(self::URL, self::WWW, 'POST')
            ->assertNoContent(204)
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_the_twenty_first_request_in_a_minute_is_rate_limited(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $this->verifyNumber(self::UNKNOWN_NUMBER, self::WWW)->assertOk();
        }

        $response = $this->verifyNumber(self::UNKNOWN_NUMBER, self::WWW);

        $response->assertStatus(429)
            ->assertExactJson([
                'status' => 'rate_limited',
                'message' => 'Too many verification attempts. Please wait a minute and try again.',
            ])
            ->assertHeader('Retry-After')
            ->assertHeader('Access-Control-Allow-Origin', self::WWW)
            ->assertHeader('Access-Control-Expose-Headers', 'Retry-After');

        $this->assertNoStore($response);
    }

    public function test_other_api_routes_keep_laravels_cors(): void
    {
        $this->preflight('/api/certificate/verify', 'https://evil.example', 'POST')
            ->assertHeader('Access-Control-Allow-Origin', '*');
    }

    private function verifyNumber(string $number, ?string $origin = null): TestResponse
    {
        return $this->withHeaders($origin ? ['Origin' => $origin] : [])
            ->getJson(self::URL . '?number=' . rawurlencode($number));
    }

    private function verifyQr(string $payload): TestResponse
    {
        // rawurlencode matters: a bare "+" in base64 would arrive as a space.
        return $this->getJson(self::URL . '?qr=' . rawurlencode($payload));
    }

    private function preflight(string $uri, string $origin, string $method): TestResponse
    {
        return $this->call('OPTIONS', $uri, [], [], [], [
            'HTTP_ORIGIN' => $origin,
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => $method,
        ]);
    }

    /**
     * The payload in the shape CertificateController::generateQrCodeData() builds.
     */
    private function samplePayload(): string
    {
        return app(CertificateEncryptionService::class)->encrypt([
            'certificate_number' => self::SAMPLE_NUMBER,
            'participant_name' => 'Aisyah Rahman',
            'event_name' => 'Digital Leadership Programme',
            'event_date' => '10 March 2026',
            'event_time' => '09:00',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function sampleRecord(): array
    {
        return [
            'awarded_to' => 'Aisyah Rahman',
            'event' => 'Digital Leadership Programme',
            'held_on' => '10 March 2026 – 12 March 2026',
            'location' => 'Kuala Lumpur',
            'issued_by' => 'Example Training Unit',
            'issued_on' => '13 March 2026',
            'certificate_number' => self::SAMPLE_NUMBER,
        ];
    }

    private function assertNoStore(TestResponse $response): void
    {
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }
}
