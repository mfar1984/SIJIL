<?php

namespace App\Console\Commands;

use App\Support\CertificateFonts;
use Illuminate\Console\Command;
use TCPDF_FONTS;

/**
 * Converts the handwriting TTF files shipped in public/fonts into the PHP
 * definition files TCPDF needs.
 *
 * TCPDF cannot read a .ttf directly. Without the generated definition file it
 * calls die() with "Could not include font definition file", which bypasses
 * Laravel entirely and breaks certificate generation and preview.
 */
class InstallCertificateFonts extends Command
{
    protected $signature = 'certificates:install-fonts {--force : Rebuild fonts that already exist}';

    protected $description = 'Convert certificate TTF fonts into TCPDF font definition files';

    public function handle(): int
    {
        $source = public_path('fonts');
        $target = CertificateFonts::path();

        if (!is_dir($source)) {
            $this->error("Source folder not found: {$source}");

            return self::FAILURE;
        }

        if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
            $this->error("Could not create target folder: {$target}");

            return self::FAILURE;
        }

        $files = glob($source . DIRECTORY_SEPARATOR . '*.ttf') ?: [];

        if (!$files) {
            $this->warn("No .ttf files found in {$source}");

            return self::SUCCESS;
        }

        $built = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($files as $file) {
            $name = CertificateFonts::nameFor($file);
            $definition = $target . DIRECTORY_SEPARATOR . $name . '.php';

            if (file_exists($definition) && !$this->option('force')) {
                $this->line("  <fg=gray>skip</>    {$name} (already installed)");
                $skipped++;
                continue;
            }

            if ($this->option('force')) {
                // addTTFfont() returns early when the definition already exists.
                foreach (glob($target . DIRECTORY_SEPARATOR . $name . '.*') ?: [] as $stale) {
                    @unlink($stale);
                }
            }

            $result = TCPDF_FONTS::addTTFfont($file, 'TrueTypeUnicode', '', 32, $target . DIRECTORY_SEPARATOR);

            if ($result === false) {
                $this->line("  <fg=red>failed</>  " . basename($file));
                $failed++;
                continue;
            }

            $this->line("  <fg=green>built</>   {$result}  <fg=gray>(" . basename($file) . ')</>');
            $built++;
        }

        $this->newLine();
        $this->info("Fonts installed in {$target}");
        $this->line("Built: {$built}   Skipped: {$skipped}   Failed: {$failed}");

        $missing = CertificateFonts::missing();

        if ($missing) {
            $this->newLine();
            $this->warn('These fonts are still referenced by templates but have no TTF source: '
                . implode(', ', $missing));
            $this->line('Text using them falls back to Helvetica.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
