<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Services\Payments\ChipClient;
use App\Services\Payments\ChipException;
use App\Support\PaymentLog;
use App\Support\PaymentSurface;
use App\Support\SecurityPolicy;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Settings > Global Config > Payment (CHIP Collect).
 *
 * Every route here sits behind the "administrator" middleware as well as the
 * payment_settings.* permission. The secret key is accepted, encrypted and
 * stored, and never sent back: responses carry PaymentSurface::payload() only.
 */
class PaymentSettingsController extends Controller
{
    private const DOMAIN_PATTERN = '/^(?=.{3,253}$)[a-z0-9-]+(\.[a-z0-9-]+)+$/';

    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'enabled' => ['nullable', 'boolean'],
            'mode' => ['required', 'in:test,live'],
            'secret_key' => ['nullable', 'string', 'min:20', 'max:512', 'regex:/^\S+$/'],
            'remove_secret_key' => ['nullable', 'boolean'],
            'brand_id' => ['nullable', 'uuid'],
            'webhook_public_key' => ['nullable', 'string', 'max:5000', function ($attribute, $value, $fail) {
                if (! static::isRsaPublicKey((string) $value)) {
                    $fail('The webhook public key must be an RSA public key in PEM format, as shown in the CHIP portal.');
                }
            }],
            'government_email_domains' => ['required', 'string', 'max:1000', function ($attribute, $value, $fail) {
                $domains = PaymentSetting::parseDomains((string) $value);

                if (count($domains) < 1 || count($domains) > 20) {
                    $fail('Enter between 1 and 20 government email domains.');

                    return;
                }

                foreach ($domains as $domain) {
                    if (! preg_match(self::DOMAIN_PATTERN, $domain)) {
                        $fail("\"{$domain}\" is not a valid domain, for example gov.my.");

                        return;
                    }
                }
            }],
        ], [
            'secret_key.regex' => 'The secret key cannot contain spaces.',
            'brand_id.uuid' => 'The Brand ID must be the UUID shown in the CHIP portal.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $settings = PaymentSetting::current();
        $user = $request->user();

        try {
            $currentSecret = $settings->secret_key;
        } catch (DecryptException) {
            // APP_KEY changed since the key was stored. Treat it as absent so a
            // new key can be entered.
            $currentSecret = null;
        }

        $nextSecret = $this->nextSecret(
            $request->input('secret_key'),
            $request->boolean('remove_secret_key'),
            $currentSecret
        );

        $brandId = $request->filled('brand_id') ? strtolower(trim((string) $request->input('brand_id'))) : null;
        $enabled = $request->boolean('enabled');

        if ($enabled && (! filled($nextSecret) || ! filled($brandId))) {
            return response()->json([
                'success' => false,
                'errors' => ['enabled' => ['Enter the secret key and Brand ID before enabling payments.']],
            ], 422);
        }

        $new = [
            'enabled' => $enabled,
            'mode' => $request->input('mode'),
            'brand_id' => $brandId,
            'webhook_public_key' => static::normalisePem($request->input('webhook_public_key')),
            'government_email_domains' => implode("\n", PaymentSetting::parseDomains((string) $request->input('government_email_domains'))),
        ];

        $old = [
            'enabled' => (bool) $settings->enabled,
            'mode' => $settings->mode,
            'brand_id' => $settings->brand_id,
            'webhook_public_key' => static::normalisePem($settings->webhook_public_key),
            'government_email_domains' => implode("\n", $settings->governmentDomains()),
        ];

        $changed = array_keys(array_filter($new, fn ($value, $key) => $old[$key] !== $value, ARRAY_FILTER_USE_BOTH));

        $secretChanged = $nextSecret !== $currentSecret;

        if ($secretChanged) {
            $changed[] = 'secret_key';
        }

        if ($changed === []) {
            return response()->json([
                'success' => true,
                'message' => 'No changes to save.',
                'panel' => PaymentSurface::payload($settings),
            ]);
        }

        $values = $new + ['updated_by' => $user?->id];

        if ($secretChanged) {
            $values['secret_key'] = $nextSecret;
            $values['secret_key_last4'] = $nextSecret === null ? null : substr($nextSecret, -4);
        }

        // A new key or mode can belong to another CHIP account, so the company
        // key that signs callbacks has to be fetched again.
        if ($secretChanged || in_array('mode', $changed, true)) {
            $values['company_public_key'] = null;
            $values['company_public_key_fetched_at'] = null;
        }

        $settings->update($values);

        $labels = array_map(fn ($field) => str_replace('_', ' ', $field), $changed);

        PaymentLog::activity('Payment settings changed: '.implode(', ', $labels), [
            'fields' => $changed,
            'enabled' => $enabled,
            'mode' => $new['mode'],
        ], $settings, $user);

        SecurityPolicy::audit('payment', 'Payment settings changed: '.implode(', ', $labels), [
            'changes' => array_fill_keys($changed, 'changed'),
            'ip_address' => $request->ip(),
        ], $user);

        return response()->json([
            'success' => true,
            'message' => 'Payment settings saved.',
            'panel' => PaymentSurface::payload($settings->fresh()),
        ]);
    }

    public function testConnection(Request $request): JsonResponse
    {
        $settings = PaymentSetting::current();

        if (! $settings->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Save a secret key and a Brand ID first.',
            ], 422);
        }

        $ok = false;
        $updates = ['last_tested_at' => now()];

        try {
            $client = ChipClient::fromSettings($settings);
            $pem = $client->publicKey();
            $methods = $client->paymentMethods((string) $settings->brand_id);

            $available = $methods['available_payment_methods'] ?? [];
            $count = is_array($available) ? count($available) : 0;

            $updates['company_public_key'] = $pem;
            $updates['company_public_key_fetched_at'] = now();

            $ok = true;
            $message = sprintf(
                'Connected to CHIP with the %s key. %d payment method%s available for this Brand ID.',
                $settings->mode === 'live' ? 'live' : 'test',
                $count,
                $count === 1 ? ' is' : 's are'
            );
        } catch (DecryptException) {
            $message = 'The stored secret key cannot be read. Enter it again and save.';
        } catch (ChipException $e) {
            $message = match (true) {
                $e->isAuthentication() => 'CHIP rejected the secret key.',
                $e->operation === 'paymentMethods' && in_array($e->httpStatus, [400, 404], true) => 'CHIP did not accept the Brand ID.',
                $e->isConnection() => 'CHIP could not be reached.',
                default => $e->getMessage(),
            };
        }

        $updates['last_test_ok'] = $ok;
        $updates['last_test_message'] = mb_substr($message, 0, 255);

        $settings->update($updates);

        return response()->json([
            'success' => $ok,
            'message' => $message,
            'panel' => PaymentSurface::payload($settings->fresh()),
        ]);
    }

    /**
     * The secret to store: the posted one when filled, none when removal was
     * asked for, otherwise the current one.
     */
    private function nextSecret(#[\SensitiveParameter] mixed $postedSecret, bool $remove, #[\SensitiveParameter] ?string $currentSecret): ?string
    {
        if (is_string($postedSecret) && trim($postedSecret) !== '') {
            return trim($postedSecret);
        }

        if ($remove) {
            return null;
        }

        return $currentSecret;
    }

    private static function normalisePem(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(str_replace(["\r\n", "\r"], "\n", $value));

        return $value === '' ? null : $value;
    }

    private static function isRsaPublicKey(string $pem): bool
    {
        $key = @openssl_pkey_get_public(trim($pem));

        if ($key === false) {
            return false;
        }

        $details = openssl_pkey_get_details($key);

        return is_array($details) && ($details['type'] ?? null) === OPENSSL_KEYTYPE_RSA;
    }
}
