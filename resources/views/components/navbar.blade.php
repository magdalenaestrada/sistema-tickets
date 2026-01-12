<!-- partial:partials/_navbar.html -->
 <nav class="navbar">
     <div class="navbar-content">

         <div class="logo-mini-wrapper">
             <img src="../assets/images/logo-mini-light.png" class="logo-mini logo-mini-light" alt="logo">
             <img src="../assets/images/logo-mini-dark.png" class="logo-mini logo-mini-dark" alt="logo">
         </div>
         <ul class="navbar-nav">
             <li class="theme-switcher-wrapper nav-item">
                 <input type="checkbox" value="" id="theme-switcher">
                 <label for="theme-switcher">
                     <div class="box">
                         <div class="ball"></div>
                         <div class="icons">
                             <i data-lucide="sun"></i>
                             <i data-lucide="moon"></i>
                         </div>
                     </div>
                 </label>
             </li>
             <li class="nav-item dropdown">
                 <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button"
                     data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                     <i data-lucide="bell"></i>

                     @if ($notificacionesCumpleaños->count())
                         <div class="indicator">
                             <div class="circle"></div>
                         </div>
                     @endif
                 </a>

                 <div class="dropdown-menu p-0" aria-labelledby="notificationDropdown">
                     <div class="px-3 py-2 border-bottom">
                         <p class="mb-0 fw-bold">
                             {{ $notificacionesCumpleaños->count() }} Cumpleaños hoy 🎉
                         </p>
                     </div>

                     <div class="p-1">
                         @forelse($notificacionesCumpleaños as $evento)
                             <a href="{{ route('eventos.index') }}"
                                 class="dropdown-item d-flex align-items-center py-2">
                                 <div class="me-3">
                                     <i data-lucide="cake"></i>
                                 </div>
                                 <div>
                                     <p class="mb-0">
                                         Cumpleaños de
                                         <strong>
                                             {{ $evento->persona->razon_social ?? $evento->persona->nombres . ' ' . $evento->persona->apellidos }}
                                         </strong>
                                     </p>
                                     <small class="text-muted">Hoy 🎂</small>
                                 </div>
                             </a>
                         @empty
                             <div class="px-3 py-2 text-center text-muted">
                                 No hay cumpleaños hoy
                             </div>
                         @endforelse
                     </div>

                     <div class="border-top text-center py-2">
                         <a href="{{ route('eventos.index') }}">Ver eventos</a>
                     </div>
                 </div>

             </li>
             <li class="nav-item dropdown">
                 <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                     data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <i data-lucide="user"></i>
                 </a>

                 <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                     <div class="d-flex flex-column align-items-center border-bottom px-4 py-3">
                         <div class="text-center">
                             <p class="fw-bold mb-0">
                                 {{ auth()->user()->persona->razon_social ??
                                     auth()->user()->persona->nombres . ' ' . auth()->user()->persona->apellidos }}
                             </p>
                             <p class="fs-12px text-secondary mb-0">
                                 {{ auth()->user()->username }}
                             </p>
                         </div>
                     </div>

                     <ul class="list-unstyled p-1 mb-0">
                         <li>
                             <a href="{{ route('logout') }}" class="dropdown-item py-2"
                                 onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                 <i class="me-2 icon-md" data-lucide="log-out"></i>
                                 Cerrar sesión
                             </a>

                             <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                 @csrf
                             </form>
                         </li>
                     </ul>
                 </div>

             </li>
         </ul>

         <a href="#" class="sidebar-toggler">
             <i data-lucide="menu"></i>
         </a>

     </div>
 </nav>
 <!-- partial -->