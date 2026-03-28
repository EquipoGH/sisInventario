{{-- resources/views/layouts/sidebar-dinamico.blade.php --}}
<li class="nav-item">
  <a href="{{ route('dashboard') }}"
     class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <i class="nav-icon fas fa-tachometer-alt"></i>
    <p>Dashboard</p>
  </a>
</li>

@foreach(($sidebarMenu ?? collect()) as $pm)
  @php
    $mod = $pm->modulo;
    if (!$mod) continue;
    if (strtolower(trim($mod->nommodulo)) === 'movimientos') continue;


    $perms = $pm->permisos ?? collect();
    $open = false;

    if (!empty($mod->route_prefix)) {
      $patterns = array_filter(array_map('trim', explode(',', $mod->route_prefix)));
      foreach ($patterns as $pat) {
        if (request()->routeIs($pat)) { $open = true; break; }
      }
    }

    if (!$open && $perms->isNotEmpty()) {
      $open = $perms->contains(function ($perm) {
        $rn = $perm->route_name;
        return $rn ? request()->routeIs($rn . '*') : false;
      });
    }

    $icon = $mod->icono ?: 'fas fa-layer-group';
    $modColor = trim((string) ($mod->color ?? ''));
    $iconStyle = $modColor !== '' ? "color: " . e($modColor) . ";" : '';
  @endphp

  <li class="nav-item {{ $open ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ $open ? 'active' : '' }}">
      <i class="nav-icon {{ e($icon) }}" style="{{ $iconStyle }}"></i>
      <p>
        {{ e($mod->nommodulo) }}
        <i class="right fas fa-angle-left"></i>
      </p>
    </a>

    <ul class="nav nav-treeview" style="{{ $open ? 'display: block;' : 'display: none;' }}">
      @forelse($perms as $perm)
        @if(str_contains(strtolower(trim($perm->nombpermiso)), 'movimiento') && strtolower(trim($mod->nommodulo)) === 'reportes')
            @continue
        @endif
        @php
          $routeName = $perm->route_name;
          $hasRoute = $routeName && \Illuminate\Support\Facades\Route::has($routeName);

          $href = $hasRoute ? route($routeName) : '#';
          $active = $routeName ? request()->routeIs($routeName . '*') : false;
        @endphp

        <li class="nav-item">
          <a href="{{ $href }}"
             class="nav-link {{ $active ? 'active' : '' }} {{ $hasRoute ? '' : 'disabled' }}">
            <i class="far {{ $active ? 'fa-dot-circle text-info' : 'fa-circle' }} nav-icon" style="font-size: 0.8rem; margin-left: 0.2rem;"></i>
            <p>{{ e($perm->nombpermiso) }}</p>
          </a>
        </li>
      @empty
        <li class="nav-item">
          <a href="#" class="nav-link disabled" tabindex="-1" aria-disabled="true">
            <i class="far fa-circle nav-icon" style="font-size: 0.8rem; margin-left: 0.2rem;"></i>
            <p class="text-muted"><small>Sin rutas de acceso</small></p>
          </a>
        </li>
      @endforelse

      {{-- ⭐ INYECCIÓN MANUAL: Mostrar "Movimientos" dentro de "Gestión De Bienes" --}}
      @if(strtolower(trim($mod->nommodulo)) === 'gestión de bienes')
        <li class="nav-item">
          <a href="{{ route('movimiento.index') }}"
             class="nav-link {{ request()->routeIs('movimiento.*') ? 'active' : '' }}">
            <i class="far {{ request()->routeIs('movimiento.*') ? 'fa-dot-circle text-info' : 'fa-circle' }} nav-icon"
               style="font-size: 0.8rem; margin-left: 0.2rem;"></i>
            <p>Movimientos</p>
          </a>
        </li>
      @endif

      {{-- ⭐ INYECCIÓN MANUAL: Mostrar "Generador Masivo QR" dentro de "Reportes" solo para Admin --}}
      @if(strtolower(trim($mod->nommodulo)) === 'reportes' && auth()->check() && auth()->user()->esAdmin())
        <li class="nav-item">
          <a href="{{ route('qr-bienes.index') }}"
             class="nav-link {{ request()->routeIs('qr-bienes.index') ? 'active' : '' }}">
            <i class="far {{ request()->routeIs('qr-bienes.index') ? 'fa-dot-circle text-info' : 'fa-circle' }} nav-icon" style="font-size: 0.8rem; margin-left: 0.2rem;"></i>
            <p>Generador Masivo QR</p>
          </a>
        </li>
      @endif

    </ul>
  </li>
@endforeach
