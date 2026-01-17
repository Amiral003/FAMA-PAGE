<html>
    <head>
        <meta charset="utf-8">
        <title>@yield('title','site officiel')</title>

        <link rel="stylesheet" href="{{ asset('front/css/main.css') }}">
    </head>

    <body>
        @include('front.partials.header')
        <main>
            @yield('content')
        </main>
        @include('front.partials.footer')
        <script src="{asset('front/js/main.js')}"></script>
    </body>
</html>