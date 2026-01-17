  <!DOCTYPE html>
  <html>
  
  <head>
    <meta charset="utf-8">
    <title>@yield('title', 'FAMA')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Favicons --}}
    <link rel="icon" href="{{ asset('front/assets/img/favicon.png') }}">
    <!-- Vendor CSS Files -->
  <link href="{{ asset('front/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

  <link href="{{asset('front/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
  <link href="{{ asset('front/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{ asset('front/vendor/aos/aos.css')}}" rel="stylesheet">
  <link href="{{ asset('front/vendor/swiper/swiper-bundle.min.css')}}" rel="stylesheet">
  <link href="{{ asset('front/vendor/glightbox/css/glightbox.min.css')}}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{ asset('front/css/main.css')}}" rel="stylesheet">
  @stack('styles')
</head>

<body class="@yield('body-class')">
    {{-- navbar --}}
    @include('partials.navbar')
    {{-- contenu principal --}}
    <main class="main">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    @include('partials.footer')
    
</body>
  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="{{ asset('front/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src=" {{asset('front/vendor/php-email-form/validate.j')}}"></script>
  <script src="{{ asset('front/vendor/aos/aos.js')}}"></script>
  <script src="{{ asset('front/vendor/typed.js/typed.umd.js')}}"></script>
  <script src="{{ asset('front/vendor/waypoints/noframework.waypoints.js')}}"></script>
  <script src="{{ asset('front/vendor/purecounter/purecounter_vanilla.js')}}"></script>
  <script src="{{ asset('front/vendor/swiper/swiper-bundle.min.js')}}"></script>
  <script src="{{ asset('front/vendor/imagesloaded/imagesloaded.pkgd.min.js')}}"></script>
  <script src="{{ asset('front/vendor/isotope-layout/isotope.pkgd.min.js')}}"></script>
  <script src=" {{asset('front/vendor/glightbox/js/glightbox.min.js')}}"></script>

  <!-- Main JS File -->
  <script src="{{ asset('front/js/main.js')}}"></script>

  @stack('scripts')


  </html>
