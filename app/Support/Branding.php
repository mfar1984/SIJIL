<?php

namespace App\Support;

use App\Models\GlobalConfig;

/**
 * Turns the Appearance settings into something the layouts can render.
 *
 * Nothing on that tab was ever consumed. Five brand images could be uploaded,
 * were stored, and appeared nowhere: the sidebar and the sign-in page both had
 * /images/logo.png hard-coded, and no page emitted a favicon link at all. The
 * colours, font and custom CSS were equally inert.
 */
class Branding
{
    /**
     * Fonts offered on the tab, with the family name and whether Google hosts it.
     *
     * @var array<string, array{label: string, family: string, google: string|null}>
     */
    private const FONTS = [
        'inter' => ['label' => 'Inter', 'family' => "'Inter'", 'google' => 'Inter:wght@300;400;500;600;700'],
        'roboto' => ['label' => 'Roboto', 'family' => "'Roboto'", 'google' => 'Roboto:wght@300;400;500;700'],
        'poppins' => ['label' => 'Poppins', 'family' => "'Poppins'", 'google' => 'Poppins:wght@300;400;500;600;700'],
        'opensans' => ['label' => 'Open Sans', 'family' => "'Open Sans'", 'google' => 'Open+Sans:wght@300;400;500;600;700'],
        'system' => ['label' => 'System default', 'family' => 'ui-sans-serif', 'google' => null],
    ];

    /**
     * Defaults matching resources/css/app.css, used when nothing is configured.
     */
    private const DEFAULT_PRIMARY = '#063c96';
    private const DEFAULT_SECONDARY = '#5170ff';

    private static ?GlobalConfig $config = null;

    private static function config(): ?GlobalConfig
    {
        if (static::$config === null) {
            try {
                static::$config = GlobalConfig::getConfig();
            } catch (\Throwable $e) {
                return null;
            }
        }

        return static::$config;
    }

    public static function flush(): void
    {
        static::$config = null;
    }

    private static function value(string $key, mixed $default = null): mixed
    {
        $config = static::config();
        $value = $config ? ($config->{$key} ?? null) : null;

        return blank($value) ? $default : $value;
    }

    // -----------------------------------------------------------------
    // Images
    // -----------------------------------------------------------------

    /**
     * Reduce a stored value to something safe to put in an attribute.
     *
     * Uploads are stored as absolute URLs built from APP_URL at the time. Serving
     * the app from another hostname would then point every image at the old host,
     * which is the same trap the notification links fell into. Anything holding a
     * host is reduced to its path.
     */
    public static function asPath(?string $stored): ?string
    {
        if (blank($stored)) {
            return null;
        }

        $stored = trim($stored);

        if (str_starts_with($stored, 'http://') || str_starts_with($stored, 'https://')) {
            $path = parse_url($stored, PHP_URL_PATH);

            return $path ?: null;
        }

        // A bare disk path such as "logos/x.png".
        if (! str_starts_with($stored, '/')) {
            return '/storage/' . ltrim($stored, '/');
        }

        return $stored;
    }

    /**
     * The image for a slot, falling back through sensible alternatives.
     *
     * @param string $slot org_logo|favicon|sidebar_logo|login_background|login_logo
     */
    public static function image(string $slot): ?string
    {
        $chain = match ($slot) {
            // A sidebar or sign-in logo is usually the organisation logo, so fall
            // back to it rather than showing nothing.
            'sidebar_logo' => ['sidebar_logo', 'org_logo'],
            'login_logo' => ['login_logo', 'org_logo'],
            default => [$slot],
        };

        foreach ($chain as $key) {
            if ($path = static::asPath(static::value($key))) {
                return $path;
            }
        }

        return null;
    }

    /**
     * A logo to render, including the file shipped with the application.
     *
     * Kept separate from image() so the settings page can tell "nothing
     * configured" apart from "showing the bundled default".
     */
    public static function logo(string $slot = 'sidebar_logo'): string
    {
        return static::image($slot) ?? '/images/logo.png';
    }

    public static function favicon(): ?string
    {
        return static::image('favicon');
    }

    public static function isConfigured(string $slot): bool
    {
        return static::asPath(static::value($slot)) !== null;
    }

    // -----------------------------------------------------------------
    // Colours
    // -----------------------------------------------------------------

    public static function primary(): string
    {
        return static::sanitiseHex(static::value('primary_color'), self::DEFAULT_PRIMARY);
    }

    public static function secondary(): string
    {
        return static::sanitiseHex(static::value('secondary_color'), self::DEFAULT_SECONDARY);
    }

    /**
     * Only a six digit hex is accepted. This value is written straight into a
     * style block, so anything else must not be echoed back.
     */
    private static function sanitiseHex(mixed $value, string $fallback): string
    {
        $value = is_string($value) ? trim($value) : '';

        return preg_match('/^#[0-9A-Fa-f]{6}$/', $value) ? $value : $fallback;
    }

    /**
     * Shift a hex colour towards black or white.
     *
     * @param float $amount -1 darkens fully, 1 lightens fully.
     */
    public static function shade(string $hex, float $amount): string
    {
        $hex = ltrim($hex, '#');

        $channels = array_map(
            fn ($pair) => (int) hexdec($pair),
            [substr($hex, 0, 2), substr($hex, 2, 2), substr($hex, 4, 2)]
        );

        $target = $amount < 0 ? 0 : 255;
        $weight = min(1, abs($amount));

        $shifted = array_map(
            fn (int $channel) => (int) round($channel + ($target - $channel) * $weight),
            $channels
        );

        return '#' . implode('', array_map(
            fn (int $channel) => str_pad(dechex(max(0, min(255, $channel))), 2, '0', STR_PAD_LEFT),
            $shifted
        ));
    }

    /**
     * The :root overrides the layouts emit.
     *
     * @return array<string, string>
     */
    public static function cssVariables(): array
    {
        $primary = static::primary();
        $secondary = static::secondary();

        return [
            '--brand-primary' => $primary,
            // Table headers and highlights use primary-light; the tab calls this
            // the secondary colour, which is the same slot.
            '--brand-primary-light' => $secondary,
            '--brand-primary-accent' => $secondary,
            // Hover states need something darker than the primary, and there is no
            // setting for it, so it is derived.
            '--brand-primary-dark' => static::shade($primary, -0.35),
            '--brand-font' => static::fontStack(),
        ];
    }

    // -----------------------------------------------------------------
    // Typography
    // -----------------------------------------------------------------

    public static function fontKey(): string
    {
        $key = (string) static::value('font_family', 'poppins');

        return array_key_exists($key, self::FONTS) ? $key : 'poppins';
    }

    public static function fontStack(): string
    {
        $family = self::FONTS[static::fontKey()]['family'];

        return $family . ", ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'";
    }

    /**
     * The stylesheet to load, or null when the family needs no download.
     */
    public static function fontUrl(): ?string
    {
        $google = self::FONTS[static::fontKey()]['google'] ?? null;

        return $google
            ? 'https://fonts.googleapis.com/css2?family=' . $google . '&display=swap'
            : null;
    }

    /**
     * @return array<string, string>
     */
    public static function fontOptions(): array
    {
        return array_map(fn ($font) => $font['label'], self::FONTS);
    }

    // -----------------------------------------------------------------
    // Behaviour
    // -----------------------------------------------------------------

    public static function showsHelpIcons(): bool
    {
        return (bool) static::value('show_help_icons', true);
    }

    public static function showsWelcomeMessage(): bool
    {
        return (bool) static::value('show_welcome_message', true);
    }

    public static function sidebarStartsCollapsed(): bool
    {
        return static::value('sidebar_default', 'expanded') === 'collapsed';
    }

    /**
     * Classes added to <body> so CSS can react to the settings.
     */
    public static function bodyClasses(): string
    {
        $classes = [];

        if (! static::showsHelpIcons()) {
            $classes[] = 'hide-help-icons';
        }

        return implode(' ', $classes);
    }

    /**
     * Operator supplied CSS.
     *
     * Returned as-is apart from removing anything that would close the style
     * element and start writing HTML. This is an administrator-only field, but a
     * setting that can inject markup into every page is worth constraining.
     */
    public static function customCss(): ?string
    {
        $css = static::value('custom_css');

        if (blank($css)) {
            return null;
        }

        $css = preg_replace('~</\s*style~i', '', (string) $css);

        // The stored default is a comment placeholder; rendering it achieves
        // nothing and makes the head noisier to read.
        return trim(preg_replace('~/\*.*?\*/~s', '', $css)) === '' ? null : $css;
    }
}
