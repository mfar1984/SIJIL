{{--
    Everything the Appearance tab controls, applied.

    Included from every layout. Before this existed the tab was entirely
    decorative: the colours, font, custom CSS and all five uploaded images were
    stored and never read by anything.
--}}
@php
    $branding = \App\Support\Branding::class;
    $favicon = \App\Support\Branding::favicon();
    $fontUrl = \App\Support\Branding::fontUrl();
    $customCss = \App\Support\Branding::customCss();
@endphp

@if($favicon)
    {{-- No page emitted a favicon at all, so browsers fell back to guessing. --}}
    <link rel="icon" href="{{ $favicon }}">
    <link rel="apple-touch-icon" href="{{ $favicon }}">
@endif

@if($fontUrl)
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $fontUrl }}" rel="stylesheet">
@endif

{{--
    Overrides the --brand-* variables that resources/css/app.css declares. The
    Tailwind theme points at these, so a colour change applies everywhere without
    rebuilding the stylesheet.
--}}
<style>
    :root {
        @foreach(\App\Support\Branding::cssVariables() as $variable => $value)
            {{ $variable }}: {{ $value }};
        @endforeach
    }
</style>

@if($customCss)
    {{-- Last, so it can override anything above it. --}}
    <style>
        {!! $customCss !!}
    </style>
@endif
