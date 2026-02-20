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
    $iconStyle = $modColor !== '' ? "color: {$modColor};" : '';
  @endphp

  <li class="nav-item {{ $open ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ $open ? 'active' : '' }}">
      <i class="nav-icon {{ $icon }}" style="{{ $iconStyle }}"></i>
      <p>
        {{ $mod->nommodulo }}
        <i class="right fas fa-angle-left"></i>
      </p>
    </a>

    <ul class="nav nav-treeview">
      @forelse($perms as $perm)
        @php
          $routeName = $perm->route_name;
          $hasRoute = $routeName && \Illuminate\Support\Facades\Route::has($routeName);

          $href = $hasRoute ? route($routeName) : '#';
          $active = $routeName ? request()->routeIs($routeName . '*') : false;
        @endphp

        <li class="nav-item">
          <a href="{{ $href }}"
             class="nav-link {{ $active ? 'active' : '' }} {{ $hasRoute ? '' : 'disabled' }}">
            <i class="far fa-circle nav-icon"></i>
            <p>{{ $perm->nombpermiso }}</p>
          </a>
        </li>
      @empty
        <li class="nav-item">
          <a href="#" class="nav-link disabled">
            <i class="far fa-circle nav-icon"></i>
            <p class="text-muted">Sin permisos asignados</p>
          </a>
        </li>
      @endforelse
    </ul>
  </li>
@endforeach
