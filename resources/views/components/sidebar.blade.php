  <!-- partial:partials/_sidebar.html -->
  <nav class="sidebar">
      <div class="sidebar-header">
          <a href="#" class="sidebar-brand">
              Noble<span>UI</span>
          </a>
          <div class="sidebar-toggler">
              <span></span>
              <span></span>
              <span></span>
          </div>
      </div>

      <div class="sidebar-body">
          <ul class="nav" id="sidebarNav">
              <li class="nav-item nav-category">HOME</li>
              <li class="nav-item">
                  <a href="dashboard-1.html" class="nav-link">
                      <i class="link-icon" data-lucide="box"></i>
                      <span class="link-title">Dashboard</span>
                  </a>
              </li>
              <li class="nav-item nav-category">GESTIÓN</li>
              <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="collapse" href="#gestion" role="button" aria-expanded="false"
                      aria-controls="gestion">
                      <i class="link-icon" data-lucide="building"></i>
                      <span class="link-title">Empresa</span>
                      <i class="link-arrow" data-lucide="chevron-down"></i>
                  </a>
                  <div class="collapse" id="gestion">
                      <ul class="nav sub-menu">
                          <li class="nav-item">
                              <a href="{{ route('empresas.index') }}" class="nav-link">Mi empresa</a>
                          </li>
                      </ul>
                  </div>
              </li>

              <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="collapse" href="#personal" role="button" aria-expanded="false"
                      aria-controls="personal">
                      <i class="link-icon" data-lucide="users"></i>
                      <span class="link-title">Personal</span>
                      <i class="link-arrow" data-lucide="chevron-down"></i>
                  </a>
                  <div class="collapse" id="personal">
                      <ul class="nav sub-menu">
                          <li class="nav-item">
                              <a href="{{ route('cargos.index') }}" class="nav-link">Cargos</a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('empleados.index') }}" class="nav-link">Empleados</a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('eventos.index') }}" class="nav-link">Cumpleaños</a>
                          </li>
                      </ul>
                  </div>
              </li>
              <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="collapse" href="#vehiculos" role="button" aria-expanded="false"
                      aria-controls="vehiculos">
                      <i class="link-icon" data-lucide="car-front"></i>
                      <span class="link-title">Vehiculos</span>
                      <i class="link-arrow" data-lucide="chevron-down"></i>
                  </a>
                  <div class="collapse" id="vehiculos">
                      <ul class="nav sub-menu">
                          <li class="nav-item">
                              <a href="{{ route('vehiculos.index') }}" class="nav-link">Vehiculos</a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('horarios.index') }}" class="nav-link">Horarios</a>
                          </li>
                      </ul>
                  </div>
              </li>
              <li class="nav-item nav-category">VENTAS</li>
              <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="collapse" href="#ventas" role="button" aria-expanded="false"
                      aria-controls="ventas">
                      <i class="link-icon" data-lucide="newspaper"></i>
                      <span class="link-title">Ventas</span>
                      <i class="link-arrow" data-lucide="chevron-down"></i>
                  </a>
                  <div class="collapse" id="ventas">
                      <ul class="nav sub-menu">
                          <li class="nav-item">
                              <a href="{{ route('tipo-encomienda.index') }}" class="nav-link">Tipo encomiendas</a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('encomiendas.index') }}" class="nav-link">Encomiendas</a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('asignaciones.index') }}" class="nav-link">Asignaciones</a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('horarios.calendario') }}" class="nav-link">Calendario</a>
                          </li>
                      </ul>
                  </div>
              </li>
              <li class="nav-item nav-category">CAJA</li>
              <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="collapse" href="#caja" role="button" aria-expanded="false"
                      aria-controls="caja">
                      <i class="link-icon" data-lucide="newspaper"></i>
                      <span class="link-title">Caja</span>
                      <i class="link-arrow" data-lucide="chevron-down"></i>
                  </a>
                  <div class="collapse" id="caja">
                      <ul class="nav sub-menu">
                          <li class="nav-item">
                              <a href="{{ route('caja.index') }}" class="nav-link">Caja</a>
                          </li>
                      </ul>
                  </div>
              </li>
          </ul>
      </div>
  </nav>
