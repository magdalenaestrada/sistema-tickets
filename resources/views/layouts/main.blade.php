<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Buses</title>
    <script src="{{ asset('assets/js/color-modes.js') }}"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>


<body>
    <div class="main-wrapper">

        <div class="page-wrapper">
            <x-sidebar />
            <x-navbar />
            <div class="page-content container-xxl">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/theme/vendors/core/core.js') }}"></script>

    <script src="{{ asset('assets/theme/vendors/apexcharts/apexcharts.min.js') }}"></script>

    <script src="{{ asset('assets/js/dashboard.js') }}"></script>

</body>

</html>
