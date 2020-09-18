<!DOCTYPE html>
@langrtl
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
@else
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@endlangrtl
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', app_name())</title>
        <link rel="shortcut icon" href="{{ asset('public/img/backend/brand/favicon.ico') }}"/>
    
        <meta name="description" content="@yield('meta_description', 'Laravel 5 Boilerplate')">
        <meta name="author" content="@yield('meta_author', 'Anthony Rappa')">
        @yield('meta')

        <link rel="stylesheet" href="{{ asset('public/assets/vendors/mdi/css/materialdesignicons.min.css') }}"> 
        <link rel="stylesheet" href="{{ asset('public/assets/vendors/base/vendor.bundle.base.css') }}"> 
        @stack('before-styles') 
        <!-- {{ style(mix('css/frontend.css')) }} --> 
        @stack('after-styles')
        <link rel="stylesheet" href="{{ asset('public/assets/css/vertical-layout-light/style.css') }}">

    </head>
    <body>
        @include('includes.partials.demo')

        <div id="app">
            @include('includes.partials.logged-in-as')
            <!-- @include('frontend.includes.nav') -->

            <!-- <div class="container"> -->
               
                @yield('content')
             <!-- </div> --> <!-- container -->
        </div><!-- #app --> 
        <script src="{{ asset('public/assets/vendors/base/vendor.bundle.base.js') }}"></script>
        <script src="{{ asset('public/assets/js/off-canvas.js') }}"></script>
        <script src="{{ asset('public/assets/js/hoverable-collapse.js') }}"></script>
        <script src="{{ asset('public/assets/js/template.js') }}"></script>
        <script src="{{ asset('public/assets/js/settings.js') }}"></script>
        <script src="{{ asset('public/assets/js/todolist.js') }}"></script> 
        <script src="{{ asset('public/js/main.js') }}"></script>  
        <!-- Scripts -->
        @stack('before-scripts')
        <!-- {!! script(mix('js/manifest.js')) !!}
        {!! script(mix('js/vendor.js')) !!}
        {!! script(mix('js/frontend.js')) !!} -->
        @stack('after-scripts')

        @include('includes.partials.ga')
    </body>
</html>
