<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>ratemyestate</title>

    <link href="{{asset('css/app.css')}}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css"/>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/x-icon"/>
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
