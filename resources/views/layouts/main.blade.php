<!DOCTYPE html>
<html lang="en">

<head>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="{{ asset('assets/theme/images/logo/favicon.png') }}" rel="icon" type="image/x-icon">
    <link href="{{ asset('assets/theme/images/logo/favicon.png') }}" rel="shortcut icon" type="image/x-icon">

    <title>Blank | ki-admin - Premium Admin Template</title>

    @include('components.assets.fontawesome')

    <!-- Fonts -->
    <link href="{{ asset('assets/theme/css/all.css') }}" rel="stylesheet" type="text/css">

    <!-- tabler icons-->
    <link href="{{ asset('assets/theme/vendor/tabler-icons/tabler-icons.css') }}" rel="stylesheet" type="text/css">

    <!--animation-css-->
    <link href="{{ asset('assets/theme/vendor/animation/animate.min.css') }}" rel="stylesheet">

    <!--flag Icon css-->
    <link href="{{ asset('assets/theme/vendor/flag-icons-master/flag-icon.css') }}" rel="stylesheet" type="text/css">

    <!-- Bootstrap css-->
    <link href="{{ asset('assets/theme/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet" type="text/css">

    <!-- simplebar css-->
    <link href="{{ asset('assets/theme/vendor/simplebar/simplebar.css') }}" rel="stylesheet" type="text/css">

    <!-- App css-->
    <link href="{{ asset('assets/theme/css/style.css') }}" rel="stylesheet" type="text/css">

    <!-- Responsive css-->
    <link href="{{ asset('assets/theme/css/responsive.css') }}" rel="stylesheet" type="text/css">

</head>

<body>
    <div class="app-wrapper">

        <div class="loader-wrapper">
            <div class="loader_24"></div>
        </div>

        {{-- for test --}}
        @include('components.sidebar.admin.default')


        <div class="app-content">
            <div class="">

                <!-- Header Section starts -->
                @include('components.navs.admin.default')
                <!-- Header Section ends -->

                <!-- Body main section starts -->
                <main>
                    <div class="container-fluid">
                        <!-- Breadcrumb start -->
                        <div class="row m-1">
                            <div class="col-12 ">
                                <h4 class="main-title">Blank</h4>
                                <ul class="app-line-breadcrumbs mb-3">
                                    <li class="">
                                        <a class="f-s-14 f-w-500" href="#">
                                            <span>
                                                <i class="ph-duotone  ph-newspaper f-s-16"></i> Other Pages
                                            </span>
                                        </a>
                                    </li>
                                    <li class="active">
                                        <a class="f-s-14 f-w-500" href="#">Blank</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- Breadcrumb end -->

                        <!-- Blank start -->
                        <div class="row">
                            <!-- Default Card start -->
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Default Card</h5>
                                    </div>
                                    <div class="card-body">
                                        <h6>Where does it come from ?</h6>
                                        <p class="text-secondary"> Contrary to popular belief, Lorem Ipsum is not
                                            simply
                                            random text. It has
                                            roots in a piece of classical Latin literature from 45 BC, making it over
                                            2000
                                            years old. Richard
                                            McClinton, a Latin professor at Hampered-Sydney College in Virginia, looked
                                            up
                                            one of the more
                                            obscure Latin words, consectetur, from a Lorem Ipsum passage, and going
                                            through
                                            the cites of the
                                            word in classical literature, discovered the undoubtable source. Lorem Ipsum
                                            comes from sections
                                            1.10.32 and 1.10.33 of "de Minibus Bono rum et Malo rum" (The Extremes of
                                            Good
                                            and Evil) by Cicero,
                                            written in 45 BC. This book is a treatise on the theory of ethics, very
                                            popular
                                            during the
                                            Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..",
                                            comes from a line in
                                            section 1.10.32 </p>
                                    </div>
                                    <div class="card-footer">
                                        <p class="float-start text-secondary p-t-10 mb-0">1 days Ago</p>

                                        <a class="float-end fw-bold" href="#"> Read More </a>
                                    </div>

                                </div>
                            </div>

                            <!-- Default Card end -->
                        </div>
                        <!-- Blank end -->
                    </div>
                </main>
                <!-- Body main section ends -->

                <!-- tap on top -->
                <div class="go-top">
                    <span class="progress-value">
                        <i class="ti ti-arrow-up"></i>
                    </span>
                </div>

                <!-- Footer Section starts-->
                <footer>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-9 col-12">
                                <p class="footer-text f-w-600 mb-0">Copyright © 2025 ki-admin. All rights reserved 💖
                                    V1.0.0</p>
                            </div>
                            <div class="col-md-3">
                                <div class="footer-text text-end">
                                    <a class="f-w-500 text-primary" href="mailto:teqlathemes@gmail.com"> Need Help <i
                                            class="ti ti-help"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- Footer Section ends-->

            </div>
        </div>
    </div>


    <!--customizer-->
    <div id="customizer"></div>

    <!-- latest jquery-->
    <script src="{{ asset('assets/theme/js/jquery-3.6.3.min.js') }}"></script>

    <!-- Simple bar js-->
    <script src="{{ asset('assets/theme/vendor/simplebar/simplebar.js') }}"></script>

    <!-- phosphor js -->
    <script src="{{ asset('assets/theme/vendor/phosphor/phosphor.js') }}"></script>

    <!-- Bootstrap js-->
    <script src="{{ asset('assets/theme/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>

    <!-- App js-->
    <script src="{{ asset('assets/theme/js/script.js') }}"></script>

    <!-- Customizer js-->
    <script src="{{ asset('assets/theme/js/customizer.js') }}"></script>


</body>

</html>
