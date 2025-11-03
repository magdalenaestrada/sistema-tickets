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
                  <a class="nav-link" data-bs-toggle="collapse" href="#emails" role="button" aria-expanded="false"
                      aria-controls="emails">
                      <i class="link-icon" data-lucide="building"></i>
                      <span class="link-title">Empresa</span>
                      <i class="link-arrow" data-lucide="chevron-down"></i>
                  </a>
                  <div class="collapse" id="emails">
                      <ul class="nav sub-menu">
                          <li class="nav-item">
                              <a href="{{ route('empresas.index') }}" class="nav-link">Mi empresa</a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('areas.index') }}" class="nav-link">Áreas</a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('cargos.index') }}" class="nav-link">Cargos</a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('empleados.index') }}" class="nav-link">Empleados</a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('vehiculos.index') }}" class="nav-link">Vehiculos</a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('horarios.index') }}" class="nav-link">Horarios</a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('horarios.calendario') }}" class="nav-link">Calendario</a>
                          </li>
                      </ul>
                  </div>
              </li>
              <li class="nav-item">
                  <a href="pages/apps/chat.html" class="nav-link">
                      <i class="link-icon" data-lucide="message-square"></i>
                      <span class="link-title">Chat</span>
                  </a>
              </li>
              <li class="nav-item">
                  <a href="pages/apps/calendar.html" class="nav-link">
                      <i class="link-icon" data-lucide="calendar"></i>
                      <span class="link-title">Calendar</span>
                  </a>
              </li>
          </ul>
      </div>
  </nav>
  <!-- partial -->
