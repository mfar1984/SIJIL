<?php

namespace App\Support;

use App\Models\GlobalConfig;
use Illuminate\Http\Request;

/**
 * The single reader of the General tab settings.
 *
 * Thirteen of the fifteen controls on that tab were stored and read by nothing:
 * the timezone, date format, cache lifetime, default page size, activity logging,
 * maintenance mode, the event defaults and the registration message all had no
 * effect whichever way they were set. Only the organisation name and email were
 * genuinely used, and mostly by code added recently.
 */
class SystemSettings
{
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

        return blank($value) && ! is_numeric($value) && ! is_bool($value) ? $default : $value;
    }

    // -----------------------------------------------------------------
    // Organisation
    // -----------------------------------------------------------------

    public static function orgName(): string
    {
        return (string) static::value('org_name', config('app.name', 'E-Certificate'));
    }

    public static function orgEmail(): ?string
    {
        $email = static::value('org_email');

        return filled($email) && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * The timezone every date in the application is rendered in.
     *
     * Validated against the system list, because an invalid value passed to
     * date_default_timezone_set() would raise a warning on every request.
     */
    public static function timezone(): string
    {
        $zone = (string) static::value('timezone', config('app.timezone', 'UTC'));

        return in_array($zone, timezone_identifiers_list(), true)
            ? $zone
            : (string) config('app.timezone', 'UTC');
    }

    // -----------------------------------------------------------------
    // Presentation
    // -----------------------------------------------------------------

    /**
     * Date format for list and report columns.
     *
     * Restricted to a known set. This value is handed to Carbon::format(), so
     * accepting arbitrary input would let a settings row emit anything at all,
     * including the time of day where only a date belongs.
     *
     * @return array<string, string> format => example
     */
    public static function dateFormatOptions(): array
    {
        /*
         * The examples are produced by the formats rather than written by hand.
         * Hand-written ones drifted immediately: "d F Y" was labelled
         * "3 August 2026" when it actually renders "03 August 2026", because d is
         * the zero-padded day. A label that disagrees with the value it describes
         * is worse than no label at all.
         */
        $sample = \Illuminate\Support\Carbon::create(2026, 8, 3, 14, 30);

        $options = [];

        foreach (['d-M-Y', 'd/m/Y', 'd M Y', 'j F Y', 'Y-m-d', 'm/d/Y'] as $format) {
            $options[$format] = $sample->format($format);
        }

        return $options;
    }

    public static function dateFormat(): string
    {
        $format = (string) static::value('date_format', 'd M Y');

        return array_key_exists($format, static::dateFormatOptions()) ? $format : 'd M Y';
    }

    /**
     * Render a date using the configured format. Returns the placeholder when
     * there is nothing to show, so callers do not each invent their own dash.
     */
    public static function date(mixed $value, string $empty = '—'): string
    {
        if (blank($value)) {
            return $empty;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)
                ->timezone(static::timezone())
                ->format(static::dateFormat());
        } catch (\Throwable $e) {
            return $empty;
        }
    }

    /**
     * The same, with the time appended. Kept separate so the date format setting
     * cannot accidentally strip a time that a column depends on.
     */
    public static function dateTime(mixed $value, string $empty = '—'): string
    {
        if (blank($value)) {
            return $empty;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)
                ->timezone(static::timezone())
                ->format(static::dateFormat() . ' H:i');
        } catch (\Throwable $e) {
            return $empty;
        }
    }

    /**
     * Default rows per page for the listing tables.
     */
    public static function perPage(?Request $request = null, int $fallback = 10): int
    {
        $configured = (int) static::value('pagination', $fallback);
        $configured = max(5, min(1000, $configured ?: $fallback));

        $requested = $request?->get('per_page');

        if ($requested === null || $requested === '') {
            return $configured;
        }

        return max(5, min(1000, (int) $requested ?: $configured));
    }

    // -----------------------------------------------------------------
    // System behaviour
    // -----------------------------------------------------------------

    /**
     * How long the settings row itself stays cached, in minutes.
     */
    public static function cacheLifetimeMinutes(): int
    {
        return max(1, min(1440, (int) static::value('cache_lifetime', 60)));
    }

    public static function activityLoggingEnabled(): bool
    {
        return (bool) static::value('activity_logging', true);
    }

    public static function maintenanceMode(): bool
    {
        return (bool) static::value('maintenance_mode', false);
    }

    // -----------------------------------------------------------------
    // Event defaults
    // -----------------------------------------------------------------

    /**
     * The statuses an event may actually hold.
     *
     * events.status is enum('active','pending','completed'). The General tab used
     * to offer draft, published and archived, none of which the column accepts, so
     * the setting could never have been applied: storing 'published' into that
     * enum is the same failure that broke the gender column elsewhere.
     *
     * @return array<string, string>
     */
    public static function eventStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'active' => 'Active',
            'completed' => 'Completed',
        ];
    }

    public static function defaultEventStatus(): string
    {
        $status = (string) static::value('default_event_status', 'pending');

        return array_key_exists($status, static::eventStatusOptions()) ? $status : 'pending';
    }

    /**
     * Hours a public registration link stays open after an event is created.
     */
    public static function eventExpiryHours(): int
    {
        return max(1, min(720, (int) static::value('event_expiry', 48)));
    }

    public static function registrationMessage(): string
    {
        return (string) static::value(
            'registration_message',
            'Thank you for registering for this event.'
        );
    }

    /**
     * Whether the same person may hold more than one registration for one event.
     *
     * Off means App\Support\DuplicateRegistration applies, which is the shipped
     * behaviour: one IC or passport per event, while an email may repeat freely so
     * that a parent or a company can register several people.
     *
     * On removes that check entirely. It does not loosen it selectively.
     */
    public static function allowsMultipleRegistrations(): bool
    {
        return (bool) static::value('allow_multiple_registrations', false);
    }
}
