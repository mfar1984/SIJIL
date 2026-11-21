@extends('emails.partials.payment-layout')

@section('title', 'Set your e-Certificate password')

@section('body')
    @php($primary = \App\Support\Branding::primary())
    <p style="margin:0 0 16px;font-size:14px;">Hello {{ $user->name }},</p>

    <p style="margin:0 0 16px;font-size:14px;line-height:1.6;">
        Thank you for your purchase. Your e-Certificate Organizer account is ready.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;margin-bottom:16px;">
        <tr>
            <td style="padding:14px 16px;font-size:13px;line-height:1.9;">
                <strong>Email</strong><br>{{ $user->email }}<br><br>
                <strong>Package</strong><br>{{ $packageName }}<br><br>
                <strong>Valid until</strong><br>{{ $expiresOn ?? '—' }}
            </td>
        </tr>
    </table>

    <p style="margin:0 0 16px;font-size:14px;line-height:1.6;">
        Set your password to sign in. The link works once and is valid for 7 days.
    </p>

    <p style="margin:0 0 16px;text-align:center;">
        <a href="{{ $url }}" style="display:inline-block;background-color:{{ $primary }};color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:12px 24px;border-radius:6px;">Set my password</a>
    </p>

    <p style="margin:0 0 16px;font-size:12px;color:#6b7280;line-height:1.6;word-break:break-all;">
        If the button does not work, open this address in your browser:<br>{{ $url }}
    </p>

    <p style="margin:0;font-size:12px;color:#6b7280;line-height:1.6;">
        If you did not make this purchase, ignore this email and contact us.
    </p>
@endsection
