@php
    $config = \App\Models\GlobalConfig::getConfig();
    $orgName = $config->org_name ?: config('app.name');
    $primary = \App\Support\Branding::primary();
    $logo = \App\Support\Branding::image('org_logo');
    $logo = $logo ? rtrim(config('app.url'), '/') . $logo : null;
    $support = config('payments.support_email');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
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
                            @yield('body')
                            <p style="margin:16px 0 0;font-size:12px;color:#6b7280;line-height:1.6;">
                                Questions? Contact <a href="mailto:{{ $support }}" style="color:{{ $primary }};">{{ $support }}</a>.
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
