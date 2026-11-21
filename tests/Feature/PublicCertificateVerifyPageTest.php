<?php

namespace Tests\Feature;

use Tests\Concerns\CreatesCertificateVerificationSchema;
use Tests\TestCase;

/**
 * Pins the exact HTML of the public /verify pages.
 *
 * The lookup and the list of public fields behind these pages are shared with the
 * public verification API. The snapshots were written from the page as it stood
 * before that sharing, so any change to what a visitor sees shows up here as a
 * diff rather than as a surprise in production.
 *
 * A missing snapshot is written and the test is marked incomplete, so a snapshot
 * can only ever be created deliberately and must then be reviewed and committed.
 */
class PublicCertificateVerifyPageTest extends TestCase
{
    use CreatesCertificateVerificationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // The page prints the app name and builds absolute links. Fixing both, and
        // requesting an absolute URL below, keeps the snapshots independent of
        // whatever APP_NAME and APP_URL the local environment happens to set.
        config(['app.name' => 'e-Certificate']);

        $this->createVerificationSchema();
        $this->seedSampleCertificates();
    }

    public function test_a_genuine_certificate_page_is_unchanged(): void
    {
        $response = $this->get('http://localhost/verify/' . self::SAMPLE_NUMBER);

        $response->assertOk()->assertSeeInOrder([
            'Awarded to', 'Aisyah Rahman',
            'Event', 'Digital Leadership Programme',
            'Held on', '10 March 2026 – 12 March 2026',
            'Location', 'Kuala Lumpur',
            'Issued by', 'Example Training Unit',
            'Issued on', '13 March 2026',
            'Certificate number', self::SAMPLE_NUMBER,
        ]);

        $this->assertMatchesSnapshot('valid', $this->normalise($response->getContent()));
    }

    public function test_an_unknown_number_page_is_unchanged(): void
    {
        $this->assertMatchesSnapshot('not-found', $this->page('/verify/' . self::UNKNOWN_NUMBER));
    }

    public function test_a_deleted_certificate_page_is_unchanged(): void
    {
        $this->assertMatchesSnapshot('trashed', $this->page('/verify/' . self::TRASHED_NUMBER));
    }

    public function test_the_form_page_is_unchanged(): void
    {
        $this->assertMatchesSnapshot('form', $this->page('/verify'));
    }

    private function page(string $path): string
    {
        $response = $this->get('http://localhost' . $path);

        $response->assertOk();

        return $this->normalise($response->getContent());
    }

    /**
     * The CSRF token is new for every session, so it is the one value replaced.
     * Line endings are unified because a Windows checkout may store the view with
     * CRLF, which would otherwise make the same page differ between machines.
     */
    private function normalise(string $html): string
    {
        $html = preg_replace('/(<meta name="csrf-token" content=")[^"]*(")/', '$1CSRF$2', $html);
        $html = preg_replace('/(name="_token" value=")[^"]*(")/', '$1CSRF$2', $html);

        return str_replace("\r\n", "\n", $html);
    }

    private function assertMatchesSnapshot(string $name, string $html): void
    {
        $path = base_path("tests/Fixtures/public-certificate-verify/{$name}.html");

        if (! is_file($path)) {
            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            file_put_contents($path, $html);

            $this->markTestIncomplete('snapshot written');
        }

        $this->assertSame(str_replace("\r\n", "\n", file_get_contents($path)), $html);
    }
}
