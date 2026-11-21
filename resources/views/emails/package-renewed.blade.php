@extends('emails.partials.payment-layout')

@section('title', 'Your e-Certificate package has been renewed')

@section('body')
    <p style="margin:0 0 16px;font-size:14px;">Hello {{ $user->name }},</p>

    <p style="margin:0 0 16px;font-size:14px;line-height:1.6;">
        Thank you for your payment. Your e-Certificate package has been renewed.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;margin-bottom:16px;">
        <tr>
            <td style="padding:14px 16px;font-size:13px;line-height:1.9;">
                <strong>Package</strong><br>{{ $packageName }}<br><br>
                <strong>Valid until</strong><br>{{ $expiresOn }}
            </td>
        </tr>
    </table>

    <p style="margin:0;font-size:14px;line-height:1.6;">
        Sign in at <a href="{{ rtrim(config('app.url'), '/') }}">{{ rtrim(config('app.url'), '/') }}</a> with your existing password.
    </p>
@endsection
