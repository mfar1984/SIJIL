@extends('emails.partials.payment-layout')

@section('title', 'Your e-Certificate package is expiring')

@section('body')
    @php($primary = \App\Support\Branding::primary())
    <p style="margin:0 0 16px;font-size:14px;">Hello {{ $user->name }},</p>

    <p style="margin:0 0 16px;font-size:14px;line-height:1.6;">
        Your e-Certificate package expires on <strong>{{ $expiresOn }}</strong>
        ({{ $daysLeft <= 1 ? 'in less than a day' : 'in '.$daysLeft.' days' }}).
        After that you cannot sign in, and your events stop taking registrations, issuing certificates and checking in participants.
        Certificates already issued keep verifying.
    </p>

    <p style="margin:0 0 16px;text-align:center;">
        <a href="{{ $pricingUrl }}" style="display:inline-block;background-color:{{ $primary }};color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:12px 24px;border-radius:6px;">Renew my package</a>
    </p>

    <p style="margin:0;font-size:13px;line-height:1.6;">
        Renew with this email address ({{ $user->email }}) so the new period is added to your account.
    </p>
@endsection
