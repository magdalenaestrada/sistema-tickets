<!-- Menu Navigation starts -->
<nav>
    <div class="app-logo">
        <a class="logo d-inline-block" href="#!">
            <img alt="#" src="{{ asset('assets/theme/images/logo/1.png') }}">
        </a>

        <span class="bg-light-primary toggle-semi-nav d-flex-center">
            <i class="fa-solid fa-chevron-right fs-5"></i>
        </span>

        <div class="d-flex align-items-center nav-profile p-3">
            <span class="h-45 w-45 d-flex-center b-r-10 position-relative bg-danger m-auto">
                <img alt="avatar" class="img-fluid b-r-10" src="{{ asset('assets/theme/images/avatar/woman.jpg') }}">
                <span class="position-absolute top-0 end-0 p-1 bg-success border border-light rounded-circle"></span>
            </span>
            <div class="flex-grow-1 ps-2">
                <h6 class="text-primary mb-0"> Ninfa Monaldo</h6>
                <p class="text-muted f-s-12 mb-0">Web Developer</p>
            </div>

            @include('components.sidebar.admin.userdropdown')

        </div>
    </div>
    <div class="app-nav" id="app-simple-bar">
        <ul class="main-nav p-0 mt-2">
            <li class="menu-title">
                <span>Dashboard</span>
            </li>
            <li>
                <a aria-expanded="false" data-bs-toggle="collapse" href="#dashboard">
                    <i class="fa-duotone fa-solid fa-house pe-4 fs-5"></i>
                    dashboard
                    <span class="badge bg-danger  badge-dashboard badge-notification ms-2">New</span>
                </a>
                <ul class="collapse" id="dashboard">
                    <li><a href="index.html">Ecommerce</a></li>
                    <li><a href="project_dashboard.html">Project</a></li>
                </ul>
            </li>
            {{-- <li class="no-sub">
                <a href="widget.html">
                    <svg stroke="currentColor" stroke-width="1.5">
                        <use xlink:href={{ asset('assets/theme/svg/_sprite.svg#squares')}}"></use>
                    </svg>
                    Widgets
                </a>
            </li> --}}
            {{-- <li class="menu-title"><span>Others</span></li>
            <li>
                <a aria-expanded="false" data-bs-toggle="collapse" href="#level">
                    <svg stroke="currentColor" stroke-width="1.5">
                        <use xlink:href={{ asset('assets/theme/svg/_sprite.svg#arrow-down')}}"></use>
                    </svg>
                    2 level
                </a>
                <ul class="collapse" id="level">
                    <li><a href="#">Blank</a></li>
                    <li class="another-level">
                        <a aria-expanded="false" data-bs-toggle="collapse" href="#level2">
                            Another level
                        </a>
                        <ul class="collapse" id="level2">
                            <li><a href="blank.html">Blank</a></li>
                        </ul>
                    </li>

                </ul>
            </li> --}}
        </ul>
    </div>

    <div class="menu-navs">
        <span class="menu-previous"><i class="fa-solid fa-chevron-left"></i></span>
        <span class="menu-next"><i class="fa-solid fa-chevron-right"></i></span>
    </div>

</nav>
<!-- Menu Navigation ends -->
