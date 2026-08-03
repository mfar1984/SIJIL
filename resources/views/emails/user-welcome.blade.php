@php
    $config = \App\Models\GlobalConfig::getConfig();
    $orgName = $config->org_name ?: config('app.name');
    $primary = \App\Support\Branding::primary();
    $logo = \App\Support\Branding::image('org_logo');
    $logo = $logo ? rtrim(config('app.url'), '/') . $logo : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your {{ $orgName }} account</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background-color:{{ $primary }};padding:20px 24px;text-align:center;">
                            @if($logo)
                                <img src="{{ $logo }}" alt="{{ $orgName }}" style="max-height:48px;max-width:200px;">
                            @else
                                <div style="color:#ffffff;font-size:18px;font-weight:bold;">{{ $orgName }}</div>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 16px;font-size:14px;">Hello {{ $user->name }},</p>

                            <p style="margin:0 0 16px;font-size:14px;line-height:1.6;">
                                An account has been created for you on <strong>{{ $orgName }}</strong>
                                by {{ $createdBy ?: 'an administrator' }}.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;margin-bottom:16px;">
                                <tr>
                                    <td style="padding:14px 16px;font-size:13px;line-height:1.9;">
                                        <strong>Sign in at</strong><br>
                                        <a href="{{ rtrim(config('app.url'), '/') }}" style="color:{{ $primary }};">{{ rtrim(config('app.url'), '/') }}</a>
                                        <br><br>
                                        <strong>Email</strong><br>
                                        {{ $user->email }}
                                        <br><br>
                                        <strong>Role</strong><br>
                                        {{ $roleName ?: 'Not assigned' }}
                                    </td>
                                </tr>
                            </table>

                            {{--
                                The password is deliberately not included. An
                                administrator sets it on the create form, so it is
                                already known to them and can be passed on directly;
                                putting it in an email would leave a working
                                credential sitting in a mailbox indefinitely.
                            --}}
                            <p style="margin:0 0 16px;font-size:13px;line-height:1.6;">
                                Your password is not included in this email. Whoever created the account will give it
                                to you separately. Change it from your profile page once you have signed in.
                            </p>

                            <p style="margin:0;font-size:12px;color:#6b7280;line-height:1.6;">
                                If you were not expecting this, please contact
                                {{ $config->admin_notification_email ?: 'your administrator' }}.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#f9fafb;padding:14px 24px;text-align:center;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:11px;color:#9ca3af;">{{ $orgName }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
