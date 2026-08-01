<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Resolves the custom handwriting fonts used by certificate templates.
 *
 * TCPDF only understands its own PHP font definition files, and when one is
 * missing it calls die() instead of throwing. That kills the request before
 * Laravel can turn it into a JSON error, which is why a missing font used to
 * surface in the browser as "Unexpected token '<'".
 *
 * Everything here therefore checks the file exists before handing the name to
 * TCPDF, and falls back to a built-in font when it does not.
 */
class CertificateFonts
{
    /**
     * Fonts that live outside TCPDF's own folder, mapped to their TTF source.
     *
     * @var array<string, string>
     */
    private const CUSTOM = [
        'amsterdam' => 'Amsterdam.ttf',
        'allura' => 'Allura-Regular.ttf',
        'greatvibes' => 'GreatVibes-Regular.ttf',
        'pacifico' => 'Pacifico-Regular.ttf',
        'sacramento' => 'Sacramento-Regular.ttf',
        'dancingscript' => 'DancingScript-Regular.ttf',
    ];

    /**
     * Font used whenever a custom font cannot be resolved.
     */
    public const FALLBACK = 'helvetica';

    /**
     * Where the generated TCPDF definition files live.
     *
     * Kept out of vendor/ so a composer update cannot wipe them.
     */
    public static function path(): string
    {
        return resource_path('fonts/tcpdf');
    }

    /**
     * Whether the given font name is one of our custom fonts.
     */
    public static function isCustom(string $font): bool
    {
        return array_key_exists(strtolower($font), self::CUSTOM);
    }

    /**
     * Full path to the definition file for a custom font, or null when the font
     * is not custom or has not been installed yet.
     */
    public static function definitionFile(string $font): ?string
    {
        if (!self::isCustom($font)) {
            return null;
        }

        $file = self::path() . DIRECTORY_SEPARATOR . strtolower($font) . '.php';

        return is_file($file) ? $file : null;
    }

    /**
     * Resolve a font name into something TCPDF can definitely render.
     *
     * @return array{0: string, 1: string} font family and font file path ('' for built-ins)
     */
    public static function resolve(string $font): array
    {
        if (!self::isCustom($font)) {
            return [$font, ''];
        }

        $file = self::definitionFile($font);

        if ($file !== null) {
            return [strtolower($font), $file];
        }

        Log::warning('Certificate font not installed, falling back', [
            'font' => $font,
            'expected_at' => self::path() . DIRECTORY_SEPARATOR . strtolower($font) . '.php',
            'hint' => 'Run: php artisan certificates:install-fonts',
        ]);

        return [self::FALLBACK, ''];
    }

    /**
     * Custom fonts that are referenced but cannot be rendered right now.
     *
     * @return array<int, string>
     */
    public static function missing(): array
    {
        $missing = [];

        foreach (array_keys(self::CUSTOM) as $font) {
            if (self::definitionFile($font) === null) {
                $missing[] = $font;
            }
        }

        return $missing;
    }

    /**
     * The TCPDF font name that addTTFfont() will produce for a TTF file.
     *
     * Mirrors TCPDF's own naming rules so the command and the resolver agree.
     */
    public static function nameFor(string $ttfPath): string
    {
        $name = strtolower(pathinfo($ttfPath, PATHINFO_FILENAME));
        $name = preg_replace('/[^a-z0-9_]/', '', $name);

        return str_replace(['bold', 'oblique', 'italic', 'regular'], ['b', 'i', 'i', ''], $name);
    }
}
