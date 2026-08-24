<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    {{-- Primary SEO meta. Section values are treated as plain text and escaped on output. --}}
    @php
        $seoTitle = trim(View::yieldContent('meta_title', seoSiteName() . ' | Property & Real Estate Reviews in Australia'));
        $seoDescription = trim(View::yieldContent('meta_description', 'Read and write honest, verified reviews of Australian property developers, builders and new estates. Seek reviews and buy with confidence on ' . seoSiteName() . '.'));
        $seoKeywords = trim(View::yieldContent('meta_keywords', 'property reviews, real estate reviews, builder reviews, developer reviews, new estates, off the plan, Australia property'));
        $seoRobots = trim(View::yieldContent('meta_robots', 'index, follow'));
        $seoCanonical = trim(View::yieldContent('canonical', url()->current()));
        $seoOgTitle = trim(View::yieldContent('og_title', $seoTitle));
        $seoOgDescription = trim(View::yieldContent('og_description', $seoDescription));
        $seoOgType = trim(View::yieldContent('og_type', 'website'));
        $seoOgImage = trim(View::yieldContent('og_image', asset('images/logo.png')));
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <meta name="robots" content="{{ $seoRobots }}">
    <link rel="canonical" href="{{ $seoCanonical }}">
    <meta name="author" content="{{ seoSiteName() }}">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ seoSiteName() }}">
    <meta property="og:type" content="{{ $seoOgType }}">
    <meta property="og:title" content="{{ $seoOgTitle }}">
    <meta property="og:description" content="{{ $seoOgDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $seoOgImage }}">
    <meta property="og:locale" content="en_AU">

    {{-- Twitter card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoOgTitle }}">
    <meta name="twitter:description" content="{{ $seoOgDescription }}">
    <meta name="twitter:image" content="{{ $seoOgImage }}">

    {{-- Performance: warm up third-party origins used below --}}
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://code.jquery.com">

    <link href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: time() }}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css"/>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/x-icon"/>
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}"/>
    <link rel="manifest" href="{{ asset('site.webmanifest') }}"/>
    <link href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css"/>

    <link rel="stylesheet" href="https://kidsysco.github.io/jquery-ui-month-picker/MonthPicker.min.css"/>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    <![endif]-->
    @yield('extra-style')

    @if(app()->environment('production'))
        <script async
                src="https://www.googletagmanager.com/gtag/js?id={{config('app.google_analytics_track_id')}}">
        </script>

        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }

            gtag('js', new Date());
            gtag('config', '{{config('app.google_analytics_track_id')}}');
        </script>
    @endif

    {{-- Structured data (schema.org) --}}
    {!! seoOrganizationJsonLd() !!}
    @yield('structured_data')
</head>
<body @if(!Route::is('root')) class="inner-page" @endif>

@include('layouts.app-header')

@yield('content')

@include('layouts.app-footer')

<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
{{--<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>--}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"></script>

<script type="text/javascript" src="{{asset('js/common.js')}}"></script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>

@yield('extra-script')

</body>
</html>
