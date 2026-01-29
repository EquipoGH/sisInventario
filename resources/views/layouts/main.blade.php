<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config_sistema('nombre_sistema', 'GesInventario') }}</title>

    <!-- 1️⃣ Google Font: Inter (similar a SF Pro) -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">

    <!-- 2️⃣ Font Awesome LOCAL (sin depender de CDN) -->
    <link rel="stylesheet" href="{{ asset('fonts/fontawesome-free-6.5.1-web/css/all.min.css') }}">

    <!-- 3️⃣ AdminLTE 3.2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- 4️⃣ CSS GLOBAL PERSONALIZADO -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">


    <!-- 5️⃣ CSS DINÁMICO DESDE BD -->
    @include('components.dynamic-styles')

    <!-- 6️⃣ CSS ESPECÍFICO DE CADA VISTA -->
    @yield('css')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-user"></i> {{ Auth::user()->name }}
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> Mi Perfil
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">

    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
        <i class="fas fa-box-open brand-image"></i>
        <span class="brand-text font-weight-light"><b>Ges</b>Inventario</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x text-white"></i>
            </div>
            <div class="info">
                <a href="{{ route('profile.edit') }}" class="d-block">{{ Auth::user()->name }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- =========================
                   INVENTARIO
                   ========================= --}}
                @canany('permiso', [
                    'Areas','Responsables','Asignaciones','Ubicaciones',
                    'Movimientos','Tipo De Movimientos','Bienes','Documentos Sustento'
                ])
                    <li class="nav-header">INVENTARIO</li>

                    @can('permiso','Areas')
                    <li class="nav-item">
                        <a href="{{ route('area.index') }}" class="nav-link {{ request()->routeIs('area.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-building"></i>
                            <p>Áreas</p>
                        </a>
                    </li>
                    @endcan

                    @can('permiso','Responsables')
                    <li class="nav-item">
                        <a href="{{ route('responsable.index') }}" class="nav-link {{ request()->routeIs('responsable.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tie"></i>
                            <p>Responsables</p>
                        </a>
                    </li>
                    @endcan

                    @can('permiso','Asignaciones')
                    <li class="nav-item">
                        <a href="{{ route('responsable-area.index') }}" class="nav-link {{ request()->routeIs('responsable-area.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tag"></i>
                            <p>Asignaciones</p>
                        </a>
                    </li>
                    @endcan

                    @can('permiso','Ubicaciones')
                    <li class="nav-item">
                        <a href="{{ route('ubicacion.index') }}" class="nav-link {{ request()->routeIs('ubicacion.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-map-marker-alt"></i>
                            <p>Ubicaciones</p>
                        </a>
                    </li>
                    @endcan

                    @can('permiso','Movimientos')
                    <li class="nav-item">
                        <a href="{{ route('movimiento.index') }}" class="nav-link {{ request()->routeIs('movimiento.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-exchange-alt"></i>
                            <p>Movimientos</p>
                        </a>
                    </li>
                    @endcan

                    @can('permiso','Tipo De Movimientos')
                    <li class="nav-item">
                        <a href="{{ route('tipo-mvto.index') }}" class="nav-link {{ request()->routeIs('tipo-mvto.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-exchange-alt"></i>
                            <p>Tipo de Movimientos</p>
                        </a>
                    </li>
                    @endcan

                    @can('permiso','Bienes')
                    <li class="nav-item">
                        <a href="{{ route('bien.index') }}" class="nav-link {{ request()->routeIs('bien.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-box"></i>
                            <p>Bienes</p>
                        </a>
                    </li>
                    @endcan

                    @can('permiso','Documentos Sustento')
                    <li class="nav-item">
                        <a href="{{ route('documento-sustento.index') }}" class="nav-link {{ request()->routeIs('documento-sustento.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-invoice"></i>
                            <p>Documentos Sustento</p>
                        </a>
                    </li>
                    @endcan
                @endcanany


                {{-- =========================
                   CATÁLOGOS
                   ========================= --}}
                @canany('permiso', ['Tipos De Bien', 'Estados Del Bien'])
                    <li class="nav-header">CATÁLOGOS</li>

                    @can('permiso','Tipos De Bien')
                    <li class="nav-item">
                        <a href="{{ route('tipo-bien.index') }}" class="nav-link {{ request()->routeIs('tipo-bien.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tags"></i>
                            <p>Tipos de Bien</p>
                        </a>
                    </li>
                    @endcan

                    @can('permiso','Estados Del Bien')
                    <li class="nav-item">
                        <a href="{{ route('estado-bien.index') }}" class="nav-link {{ request()->routeIs('estado-bien.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-check-circle"></i>
                            <p>Estados del Bien</p>
                        </a>
                    </li>
                    @endcan
                @endcanany


                @can('permiso','Reportes')
    <li class="nav-header">REPORTES</li>

    <li class="nav-item">
        <a href="{{ route('reportes.kardex.index') }}"
           class="nav-link {{ request()->routeIs('reportes.kardex.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>Kardex de Movimientos</p>
        </a>
    </li>
@endcan




                {{-- =========================
                   SEGURIDAD
                   ========================= --}}
                @canany('permiso', ['Usuarios','Perfiles','Permisos','Modulos'])
                    <li class="nav-header">SEGURIDAD</li>

                    @can('permiso','Usuarios')
                    <li class="nav-item">
                        <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Usuarios</p>
                        </a>
                    </li>
                    @endcan

                    @can('permiso','Perfiles')
                    <li class="nav-item">
                        <a href="{{ route('perfil.index') }}" class="nav-link {{ request()->routeIs('perfil.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-id-card"></i>
                            <p>Perfiles</p>
                        </a>
                    </li>
                    @endcan

                    @can('permiso','Permisos')
                    <li class="nav-item">
                        <a href="{{ route('permiso.index') }}" class="nav-link {{ request()->routeIs('permiso.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-key"></i>
                            <p>Permisos</p>
                        </a>
                    </li>
                    @endcan

                    @can('permiso','Modulos')
                    <li class="nav-item">
                        <a href="{{ route('modulo.index') }}" class="nav-link {{ request()->routeIs('modulo.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cubes"></i>
                            <p>Módulos</p>
                        </a>
                    </li>
                    @endcan
                @endcanany


                {{-- =========================
                   CONFIGURACIÓN (módulo nuevo)
                   ========================= --}}
                @can('permiso','Apariencia Del Sistema')
                    <li class="nav-header">CONFIGURACIÓN</li>

                    <li class="nav-item">
                        <a href="{{ route('configuracion.index') }}" class="nav-link {{ request()->routeIs('configuracion.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-palette"></i>
                            <p>Apariencia del Sistema</p>
                        </a>
                    </li>
                @endcan

            </ul>
        </nav>
    </div>
</aside>



    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        @hasSection('content_header')
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        @yield('content_header')
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Version</b> 1.0.0
        </div>
        <strong>{{ config_sistema('nombre_sistema', 'Sistema de Inventario') }} &copy; {{ date('Y') }}</strong>
    </footer>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>


@yield('js')
</body>
</html>
