<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title')</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="{{url('/')}}/vendor/adminlte/dist/img/AdminLTELogo.png" rel="icon">
  <link href="{{url('/')}}/vendor/adminlte/dist/img/AdminLTELogo.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Roboto:300,300i,400,400i,500,500i,700,700i&display=swap" rel="stylesheet">

  <!-- CDN CSS Files -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Vendor CSS Files -->
  <link href="{{url('/')}}/assets/vendor/animate.css/animate.min.css" rel="stylesheet">
  <link href="{{url('/')}}/assets/vendor/aos/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="{{url('/css/style.css')}}?ver={{config('app.version')}}">
  <link href="{{url('/')}}/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="{{url('/')}}/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="{{url('/')}}/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="{{url('/')}}/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="{{url('/')}}/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  @yield('css')
  <!-- Template Main CSS File -->
  <link href="{{url('/')}}/assets/css/style.css?ver={{config('app.version')}}" rel="stylesheet">

  <!-- =======================================================
  * Template Name: L.ACJF - v4.11.0
  * Template URL: https://bootstrapmade.com/free-bootstrap-template-corporate-L.ACJF/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>
<header id="header" class="fixed-top d-flex align-items-center theme-bg-primary">
  @include('site.layout.header')
</header>
<main id="main" class="pb-2">
    @yield('banner-topo')
    @yield('main')
</main>
  @include('site.layout.footer')
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="{{url('/')}}/assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="{{url('/')}}/assets/vendor/aos/aos.js"></script>
  <script src="{{url('/')}}/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="{{url('/')}}/assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="{{url('/')}}/assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="{{url('/')}}/assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="{{url('/')}}/assets/vendor/waypoints/noframework.waypoints.js"></script>
  {{-- <script src="{{url('/')}}/assets/vendor/php-email-form/validate.js"></script> --}}

  <!-- Template Main JS File -->
  @yield('js')
  <script src="{{url('/')}}/assets/js/main.js"></script>

</body>

</html>
