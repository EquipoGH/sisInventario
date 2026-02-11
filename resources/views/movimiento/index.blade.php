@extends('layouts.main')

@section('title', 'Gestión de Movimientos')

@section('content_header')

@section('css')
{{-- ⭐⭐⭐ COLORES POR TIPO DE MOVIMIENTO + HEADER COMPACTO ⭐⭐⭐ --}}
<style>
/* ==========================================
   COLORES DE FONDO POR TIPO DE MOVIMIENTO
   ========================================== */

/* 🟦 SIN ASIGNAR - Celeste claro (IGUAL QUE REGISTRO) */
.tipo-sin-asignar {
    background-color: #e3f2fd !important;
}

/* 🟦 REGISTRO - Celeste claro (MANTENER POR COMPATIBILIDAD) */
.tipo-registro {
    background-color: #e3f2fd !important;
}


/* 🟢 ASIGNACIÓN - Verde claro */
.tipo-asignacion {
    background-color: #e8f5e9 !important;
}

/* ❌ HOVER ELIMINADO - NO CAMBIA COLOR
.tipo-asignacion:hover {
    background-color: #c8e6c9 !important;
} */

/* 🔴 BAJA - Rojo claro */
.tipo-baja {
    background-color: #ffebee !important;
}

/* ❌ HOVER ELIMINADO - NO CAMBIA COLOR
.tipo-baja:hover {
    background-color: #ffcdd2 !important;
} */

/* Badge SIN ASIGNAR - Celeste azul */
.badge-tipo-sin-asignar {
    background-color: #2196F3 !important;
    color: white !important;
}

/* Badge REGISTRO - Mantener (fallback) */
.badge-tipo-registro {
    background-color: #2196F3 !important;
    color: white !important;
}


.badge-tipo-asignacion {
    background-color: #4CAF50 !important;
    color: white !important;
}

.badge-tipo-baja {
    background-color: #F44336 !important;
    color: white !important;
}

/* ═══════════════════════════════════════════════════════════════ */
/* HEADER ULTRA COMPACTO - TODO EN UNA FILA */
/* ═══════════════════════════════════════════════════════════════ */
.header-ultra-compacto {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 15px 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.row-compacto {
    display: flex;
    align-items: stretch;
    gap: 15px;
}

/* BLOQUE IZQUIERDO: Leyenda de colores */
.leyenda-box {
    flex: 0 0 auto;
    min-width: 180px;
    background: white;
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 12px 16px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.leyenda-box-title {
    font-size: 0.7rem;
    font-weight: 700;
    color: #5a5c69;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
    border-bottom: 2px solid #e3e6f0;
    padding-bottom: 6px;
}

.leyenda-box-items {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.leyenda-box-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #5a5c69;
}

.leyenda-dot-small {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid;
    flex-shrink: 0;
}

.leyenda-dot-small.registro {
    background-color: #e3f2fd;
    border-color: #2196F3;
}

.leyenda-dot-small.asignacion {
    background-color: #e8f5e9;
    border-color: #4CAF50;
}

.leyenda-dot-small.baja {
    background-color: #ffebee;
    border-color: #F44336;
}

/* MINI CARDS DERECHOS */
.mini-cards-container {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.mini-card-simple {
    background: white;
    border-left: 4px solid;
    border-radius: 8px;
    padding: 12px 16px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    text-align: center;
}

.mini-card-simple:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.mini-card-simple.primary {
    border-left-color: #4e73df;
}

.mini-card-simple.success {
    border-left-color: #1cc88a;
}

.mini-card-simple.info {
    border-left-color: #36b9cc;
}

.mini-card-simple.danger {
    border-left-color: #e74a3b;
}

.mini-card-simple-label {
    font-size: 0.7rem;
    font-weight: 700;
    color: #858796;
    text-transform: lowercase;
    letter-spacing: 0.3px;
    margin-bottom: 8px;
}

.mini-card-simple-value {
    font-size: 1.8rem;
    font-weight: 800;
    color: #5a5c69;
    line-height: 1;
}


.responsable-text {
    font-size: 0.85rem;
    color: #495057;
}

.responsable-text i {
    color: #6c757d;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeIn 0.3s ease-in;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.custom-checkbox .custom-control-input:checked ~ .custom-control-label::before {
    background-color: #007bff;
    border-color: #007bff;
}

/* ⭐ ESTILOS PARA TRAZABILIDAD */
.timeline-item {
    border-left: 2px solid #dee2e6;
    padding-left: 15px;
    margin-bottom: 15px;
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -6px;
    top: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #007bff;
}

.nav-tabs .nav-link.active {
    font-weight: bold;
}

/* ==========================================
   ⭐ MEJORAS VISUALES PROFESIONALES
   ========================================== */

/* BOTONES DE ACCIÓN */
.btn-action {
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn-action i {
    font-size: 1rem;
}

.btn-action .badge {
    font-size: 0.75rem;
    padding: 3px 7px;
    border-radius: 10px;
}

/* CONTENEDOR DE BÚSQUEDA */
.search-container {
    position: relative;
}

.input-group-search {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-radius: 8px;
    overflow: hidden;
}

.form-control-search {
    border: 2px solid #e0e6ed;
    padding: 0.625rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control-search:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
}

.input-group-search .input-group-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 0.625rem 1rem;
}

.input-group-search .btn-outline-secondary {
    border: 2px solid #e0e6ed;
    border-left: none;
    background: white;
    color: #6c757d;
    transition: all 0.3s ease;
}

.input-group-search .btn-outline-secondary:hover {
    background: #f8f9fa;
    color: #dc3545;
    border-color: #e0e6ed;
}

/* ==========================================
   ⭐ FILTROS AVANZADOS - DISEÑO PROFESIONAL
   ========================================== */

/* LABELS DE FILTROS */
.filter-label,
.filter-label-inline {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-label i,
.filter-label-inline i {
    margin-right: 5px;
    font-size: 0.85rem;
}

/* SELECTS PERSONALIZADOS */
.custom-select-filter {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    color: #495057;
    background-color: #fff;
    transition: all 0.3s ease;
    cursor: pointer;
}

.custom-select-filter:hover {
    border-color: #d1d3e2;
    background-color: #f8f9fc;
}

.custom-select-filter:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
    background-color: #fff;
}

/* INPUT DATE PERSONALIZADO */
.custom-date-filter {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    color: #495057;
    background-color: #fff;
    transition: all 0.3s ease;
}

.custom-date-filter:hover {
    border-color: #d1d3e2;
    background-color: #f8f9fc;
}

.custom-date-filter:focus {
    border-color: #1cc88a;
    box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.15);
    background-color: #fff;
}

.custom-date-filter::-webkit-calendar-picker-indicator {
    cursor: pointer;
    filter: opacity(0.6);
    transition: filter 0.3s ease;
}

.custom-date-filter::-webkit-calendar-picker-indicator:hover {
    filter: opacity(1);
}

/* BOTÓN APLICAR FILTROS */
.btn-apply-filters {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    font-weight: 600;
    padding: 0.55rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.75rem;
}

.btn-apply-filters:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    color: white;
}

.btn-apply-filters:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
}

.btn-apply-filters i {
    margin-right: 6px;
    font-size: 0.85rem;
}

/* ANIMACIONES */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#accionesMasivas {
    animation: slideDown 0.3s ease;
}

/* MEJORA DE ICONOS */
.fas.fa-database,
.fas.fa-spinner {
    font-size: 0.85rem;
}

/* HOVER EN INFO DE RESULTADOS */
#infoResultados strong {
    color: #007bff;
    font-weight: 700;
}

/* ==========================================
   ⭐ RESPONSIVE
   ========================================== */
@media (max-width: 1200px) {
    .row-compacto {
        flex-direction: column;
    }

    .leyenda-box {
        min-width: 100%;
    }

    .mini-cards-container {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 768px) {
    .btn-action span:not(.badge) {
        display: none !important;
    }

    .btn-action {
        padding: 0.5rem 0.75rem;
    }

    .filter-label,
    .filter-label-inline {
        font-size: 0.75rem;
    }

    .mini-cards-container {
        grid-template-columns: repeat(2, 1fr);
    }

    .mini-card-simple-value {
        font-size: 1.5rem;
    }
}

@media (max-width: 576px) {
    .mini-cards-container {
        grid-template-columns: 1fr;
    }
}

/* ==========================================
   DISEÑO COMPACTO DE FILTROS EN UNA FILA
   ========================================== */

/* Ajustar altura de inputs para uniformidad */
.form-control-sm,
.custom-select-filter,
.custom-date-filter,
.btn-apply-filters {
    height: 38px !important;
    font-size: 0.85rem;
}

/* Mejorar apariencia de placeholders */
.custom-select-filter:invalid,
.custom-select-filter option[value=""] {
    color: #6c757d;
}

/* Icono de calendario más visible */
.custom-date-filter::-webkit-calendar-picker-indicator {
    cursor: pointer;
    filter: invert(0.5);
}

/* Botón de filtrar con efecto hover */
.btn-apply-filters:hover {
    transform: scale(1.05);
}

/* Responsive: ajustar tamaños en tablets */
@media (max-width: 1199px) {
    .form-control-sm,
    .custom-select-filter,
    .custom-date-filter {
        font-size: 0.8rem;
    }
}

/* Responsive: en móviles, filtros en 2 columnas */
@media (max-width: 767px) {
    .col-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

/* Badge de filtros activos */
#filtrosActivos .badge {
    padding: 6px 12px;
    font-size: 0.8rem;
    vertical-align: middle;
}

/* Animación para actualización de cards */
.card-updating {
    animation: cardPulse 0.3s ease-in-out;
}

@keyframes cardPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.15);
        color: #007bff;
        font-weight: bold;
    }
}




/* ==========================================
   ⭐ LEYENDA HORIZONTAL + INFO (NUEVA)
   ========================================== */

.leyenda-horizontal-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    margin-top: 15px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.leyenda-inline {
    display: flex;
    gap: 25px;
    align-items: center;
}

.leyenda-item {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-weight: 600;
    color: #5a5c69;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.leyenda-item:hover {
    transform: translateY(-2px);
}

.dot-inline {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid;
    flex-shrink: 0;
    display: inline-block;
    transition: all 0.3s ease;
}

.leyenda-item:hover .dot-inline {
    transform: scale(1.2);
}

/* Dot leyenda SIN ASIGNAR - Celeste */
.dot-inline.sin-asignar {
    background-color: #e3f2fd;
    border-color: #2196F3;
}

/* Dot leyenda REGISTRO - Mantener (fallback) */
.dot-inline.registro {
    background-color: #e3f2fd;
    border-color: #2196F3;
}


.dot-inline.asignacion {
    background-color: #e8f5e9;
    border-color: #4CAF50;
}

.dot-inline.baja {
    background-color: #ffebee;
    border-color: #F44336;
}

.text-leyenda {
    font-size: 0.85rem;
    text-transform: lowercase;
    letter-spacing: 0.3px;
}

.info-edicion {
    color: #858796;
    font-size: 0.82rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}

.info-edicion i {
    color: #4e73df;
    font-size: 0.9rem;
}

/* ⭐ Responsive: en móviles se apilan verticalmente */
@media (max-width: 768px) {
    .leyenda-horizontal-container {
        flex-direction: column;
        gap: 12px;
        text-align: center;
        padding: 15px;
    }

    .leyenda-inline {
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
    }

    .info-edicion {
        justify-content: center;
    }
}

/* ⭐ En tablets: reducir espaciado */
@media (max-width: 992px) {
    .leyenda-inline {
        gap: 18px;
    }
}


</style>
@endsection

@stop



@section('content')
<h2>Gestión de Movimientos</h2>
<div class="card">

<div class="card-header">
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- HEADER ULTRA COMPACTO: Leyenda + Cards en UNA SOLA FILA --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div class="header-ultra-compacto">
        <div class="row-compacto">

            {{-- BLOQUES DERECHOS: Mini Cards de estadísticas --}}
            <div class="mini-cards-container">
                {{-- Card 1: Total --}}
                <div class="mini-card-simple primary">
                    <div class="mini-card-simple-label">Total</div>
                    <div class="mini-card-simple-value" id="cardTotalBienes">{{ $totalBienes ?? 0 }}</div>
                </div>

                {{-- Card 2: Asignación --}}
                <div class="mini-card-simple success">
                    <div class="mini-card-simple-label">Asignación</div>
                    <div class="mini-card-simple-value" id="cardBienesAsignados">{{ $bienesAsignados ?? 0 }}</div>
                </div>

                {{-- Card 3: Registro --}}
                <div class="mini-card-simple info">
                    <div class="mini-card-simple-label">Registro</div>
                    <div class="mini-card-simple-value" id="cardBienesRegistro">{{ $bienesRegistro ?? 0 }}</div>
                </div>

                {{-- Card 4: Baja --}}
                <div class="mini-card-simple danger">
                    <div class="mini-card-simple-label">Baja</div>
                    <div class="mini-card-simple-value" id="cardBienesBaja">{{ $bienesBaja ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card-body">
    {{-- ═══════════════════════════════════════════════════════════ --}}
{{-- FILA ÚNICA: Búsqueda + Filtros en línea horizontal --}}
{{-- ═══════════════════════════════════════════════════════════ --}}

{{-- FILA 1: Botones de acción masiva (solo visible cuando hay selección) --}}
<div class="row mb-2" id="accionesMasivas" style="display:none;">
    <div class="col-12">
        <div class="btn-toolbar justify-content-center bg-light py-2 px-3 rounded" role="toolbar">
            <div class="btn-group mr-2" role="group">
                <button type="button" class="btn btn-success btn-action" id="btnAsignarSeleccionados">
                    <i class="fas fa-share-square"></i>
                    <span class="d-none d-sm-inline">Asignar</span>
                    <span class="badge badge-light ml-1" id="contadorAsignar">0</span>
                </button>
                <button type="button" class="btn btn-warning btn-action" id="btnBajaSeleccionados">
                    <i class="fas fa-times-circle"></i>
                    <span class="d-none d-sm-inline">Dar de Baja</span>
                    <span class="badge badge-light ml-1" id="contadorBaja">0</span>
                </button>
                <button type="button" class="btn btn-info btn-action" id="btnRevertirBajaSeleccionados">
                    <i class="fas fa-undo-alt"></i>
                    <span class="d-none d-sm-inline">Revertir Baja</span>
                    <span class="badge badge-light ml-1" id="contadorRevertir">0</span>
                </button>
                {{-- ⭐⭐⭐ CAMBIO: Eliminar → Anular (Solo Admin) ⭐⭐⭐ --}}
                @if(Auth::user()->esAdmin())
                <button type="button" class="btn btn-danger btn-action" id="btnAnularSeleccionados">
                    <i class="fas fa-ban"></i>
                    <span class="d-none d-sm-inline">Anular</span>
                    <span class="badge badge-light ml-1" id="contadorAnular">0</span>
                </button>
                @endif

            </div>
        </div>
    </div>
</div>

    {{-- FILA 2: Búsqueda + Filtros con LABELS --}}
    <div class="row mb-3 align-items-end">
        {{-- Búsqueda (25% - REDUCIDO) --}}
        <div class="col-xl-2 col-lg-2 col-md-4 col-6 mb-2">
            <label class="filter-label-inline">
                <i class="fas fa-search text-primary"></i> BÚSQUEDA
            </label>
            <div class="input-group input-group-search">
                <input type="text" id="searchInput" class="form-control form-control-search"
                    placeholder="Código, denominación..." autocomplete="off">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="btnLimpiar" title="Limpiar búsqueda">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Filtro: Tipo de Movimiento (12%) --}}
        <div class="col-xl-2 col-lg-2 col-md-4 col-6 mb-2">
            <label class="filter-label-inline">
                <i class="fas fa-filter text-primary"></i> TIPO MOVIMIENTO
            </label>
            <select id="filtroTipo" class="form-control form-control-sm custom-select-filter">
                <option value="activos" selected>Movimientos activos</option>
                @foreach($tiposMovimiento as $tipo)
                    <option value="{{ $tipo->id_tipo_mvto }}">
                        @if(stripos($tipo->tipo_mvto, 'ASIGNACION') !== false || stripos($tipo->tipo_mvto, 'ASIGNACIÓN') !== false)
                            {{ $tipo->tipo_mvto }}
                        @elseif(stripos($tipo->tipo_mvto, 'REGISTRO') !== false)
                            {{ $tipo->tipo_mvto }}
                        @elseif(stripos($tipo->tipo_mvto, 'BAJA') !== false)
                            {{ $tipo->tipo_mvto }}
                        @else
                            {{ $tipo->tipo_mvto }}
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filtro: Estado del Bien (8%) --}}
        <div class="col-xl-1 col-lg-2 col-md-3 col-6 mb-2">
            <label class="filter-label-inline">
                <i class="fas fa-toggle-on text-info"></i> ESTADO
            </label>
            <select id="filtroEstadoBien" class="form-control form-control-sm custom-select-filter">
                <option value="todos">Todos</option>
                <option value="1" selected>Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>

        {{-- ⭐⭐⭐ NUEVO: Filtro por ÁREA (12%) ⭐⭐⭐ --}}
        <div class="col-xl-2 col-lg-2 col-md-4 col-5 mb-2">
            <label class="filter-label-inline">
                <i class="fas fa-building text-warning"></i> ÁREA
            </label>
            <select id="filtroArea" class="form-control form-control-sm custom-select-filter">
                <option value="">Todas las áreas</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id_area }}">
                        {{ Str::limit($area->nombre_area, 20) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filtro: Ubicación (12%) --}}
        <div class="col-xl-1 col-lg-2 col-md-3 col-6 mb-2">
            <label class="filter-label-inline">
                <i class="fas fa-map-marker-alt text-danger"></i> UBICACIÓN
            </label>
            <select id="filtroUbicacion" class="form-control form-control-sm custom-select-filter">
                <option value="">Todas</option>
                @foreach($ubicaciones as $ubicacion)
                    <option value="{{ $ubicacion->id_ubicacion }}">
                        {{ Str::limit($ubicacion->nombre_sede, 20) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filtro: Fecha Desde (8%) --}}
        <div class="col-xl-1 col-lg-2 col-md-3 col-6 mb-2">
            <label class="filter-label-inline">
                <i class="fas fa-calendar-alt text-success"></i> DESDE
            </label>
            <input type="date" id="filtroFechaDesde" class="form-control form-control-sm custom-date-filter">
        </div>

        {{-- Filtro: Fecha Hasta (8%) --}}
        <div class="col-xl-1 col-lg-2 col-md-3 col-6 mb-2">
            <label class="filter-label-inline">
                <i class="fas fa-calendar-alt text-success"></i> HASTA
            </label>
            <input type="date" id="filtroFechaHasta" class="form-control form-control-sm custom-date-filter">
        </div>

        {{-- ⭐⭐⭐ NUEVO: Filtro Ver Anulados (SOLO ADMIN) ⭐⭐⭐ --}}
        @if(Auth::user()->esAdmin())
        <div class="col-xl-1 col-lg-2 col-md-3 col-6 mb-2">
            <label class="filter-label-inline">
                <i class="fas fa-eye-slash text-secondary"></i> ANULADOS
            </label>
            <select id="filtroAnulados" class="form-control form-control-sm custom-select-filter">
                <option value="0" selected>Activos</option>
                <option value="1">Anulados</option>
            </select>
        </div>
        @endif


        {{-- Botón Aplicar Filtros (8%) --}}
        <div class="col-xl-1 col-lg-2 col-md-3 col-6 mb-2">
            <label class="filter-label-inline d-none d-xl-block">&nbsp;</label>
            <button type="button" id="btnAplicarFiltros" class="btn btn-apply-filters btn-sm btn-block">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>


{{-- Información de resultados --}}
<div class="row mb-2">
    <div class="col-12">
        <small class="text-muted">
            <span id="infoResultados">
                <i class="fas fa-database mr-1"></i>
                Mostrando <strong id="from">{{ $movimientos->firstItem() ?? 0 }}</strong>
                a <strong id="to">{{ $movimientos->lastItem() ?? 0 }}</strong>
                de <strong id="resultadosCount">{{ $movimientos->total() }}</strong>
                (<strong id="totalCount">{{ $total }}</strong> Total)
            </span>
            <span id="loadingSearch" style="display:none;">
                <i class="fas fa-spinner fa-spin text-primary mr-1"></i>
                <span class="text-primary">Buscando...</span>
            </span>
        </small>

        {{-- Indicador de filtros activos --}}
        <span id="filtrosActivos" style="display:none;" class="ml-3">
            <span class="badge badge-warning">
                <i class="fas fa-filter"></i>
                <span id="filtrosActivosTexto"></span>
            </span>
            <button type="button" id="btnLimpiarFiltros" class="btn btn-link btn-sm text-danger p-0 ml-1"
                    style="font-size: 0.75rem; vertical-align: middle;">
                <i class="fas fa-times"></i> Limpiar
            </button>
        </span>
    </div>
</div>




    {{-- Separador --}}
    <hr class="mb-3 mt-2" style="border-top: 2px solid #e3e6f0;">

    {{-- ⭐⭐⭐ LEYENDA HORIZONTAL + INFO (DEBAJO DE LA TABLA) ⭐⭐⭐ --}}
    <div class="leyenda-horizontal-container">
        {{-- Leyenda de colores inline --}}
        <div class="leyenda-inline">
            <span class="leyenda-item">
                <span class="dot-inline sin-asignar"></span>
                <span class="text-leyenda">sin asignar</span>
            </span>

            <span class="leyenda-item">
                <span class="dot-inline asignacion"></span>
                <span class="text-leyenda">asignación</span>
            </span>
            <span class="leyenda-item">
                <span class="dot-inline baja"></span>
                <span class="text-leyenda">baja</span>
            </span>
        </div>

        {{-- Texto de información --}}
        <div class="info-edicion">
            <i class="fas fa-info-circle"></i>
            Doble clic en la fila para editar
        </div>
    </div>




    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th width="3%">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="checkAll">
                                <label class="custom-control-label" for="checkAll"></label>
                            </div>
                        </th>
                        <th width="5%" class="sortable" data-column="id" style="cursor:pointer;">
                            ID <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="10%" class="sortable" data-column="fecha" style="cursor:pointer;">
                            FECHA <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="10%">CÓDIGO</th>
                        <th width="20%">DENOMINACIÓN</th>
                        <th width="10%" class="sortable" data-column="tipo" style="cursor:pointer;">
                            TIPO MVTO <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="12%">ÁREA</th>
                        <th width="12%">UBICACIÓN</th>
                        <th width="8%">ESTADO CONSERV.</th>
                        <th width="7%">ESTADO MVTO</th>  {{-- ⭐ NUEVA COLUMNA --}}
                        <th width="10%">ACCIÓN</th>

                    </tr>
                </thead>

                <tbody id="tablaMovimientos">
                    @forelse($movimientos as $movimiento)
                    {{-- ⭐ CLASE DINÁMICA PARA COLOR DE FILA --}}
                    @php
                        $tipoNormalizado = strtolower(str_replace(['á','é','í','ó','ú','ñ',' '], ['a','e','i','o','u','n','-'], $movimiento->tipoMovimiento->tipo_mvto));
                        $badgeClass = 'badge-tipo-' . $tipoNormalizado;
                    @endphp

                    <tr id="row-{{ $movimiento->id_movimiento }}"
                        class="fila-movimiento tipo-{{ $tipoNormalizado }}"
                        data-id="{{ $movimiento->id_movimiento }}">

                        <td class="text-center">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox-item"
                                       id="check-{{ $movimiento->id_movimiento }}"
                                       value="{{ $movimiento->id_movimiento }}"
                                       data-bien-id="{{ $movimiento->idbien }}">
                                <label class="custom-control-label" for="check-{{ $movimiento->id_movimiento }}"></label>
                            </div>
                        </td>

                        <td class="text-center"><strong>{{ $movimiento->id_movimiento }}</strong></td>

                        <td>
                            <strong>{{ \Carbon\Carbon::parse($movimiento->fecha_mvto)->format('d/m/Y') }}</strong><br>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($movimiento->fecha_mvto)->format('H:i:s') }}</small>
                        </td>

                        <td>
                            <span class="responsable-text">
                                <i class="fas fa-user-circle"></i> {{ $movimiento->usuario->name ?? 'N/A' }}
                            </span>
                        </td>

                        <td>
                            <span class="badge badge-info">
                                {{ $movimiento->bien->codigo_patrimonial }}
                            </span>
                        </td>

                        <td>
                            <strong>{{ Str::limit($movimiento->bien->denominacion_bien, 30) }}</strong>
                            <br>
                            <small class="text-muted">{{ $movimiento->bien->tipoBien->nombre_tipo }}</small>
                        </td>

                        <td>
                            <span class="badge {{ $badgeClass }}">
                                {{ $movimiento->tipoMovimiento->tipo_mvto }}
                            </span>
                        </td>

                        <td>
                            @if($movimiento->ubicacion)
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> {{ $movimiento->ubicacion->ubicacion_completa }}
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- ⭐ COLUMNA: ESTADO DE CONSERVACIÓN --}}
                        <td>
                            @if($movimiento->estadoConservacion)
                                @php
                                    $nombreEstado = strtoupper($movimiento->estadoConservacion->nombre_estado);

                                    if (str_contains($nombreEstado, 'BUENO') || str_contains($nombreEstado, 'EXCELENTE') || str_contains($nombreEstado, 'ÓPTIMO')) {
                                        $badgeClass = 'badge-success';
                                    } elseif (str_contains($nombreEstado, 'REGULAR') || str_contains($nombreEstado, 'ACEPTABLE')) {
                                        $badgeClass = 'badge-warning';
                                    } elseif (str_contains($nombreEstado, 'MALO') || str_contains($nombreEstado, 'DEFICIENTE') || str_contains($nombreEstado, 'DETERIORADO')) {
                                        $badgeClass = 'badge-danger';
                                    } else {
                                        $badgeClass = 'badge-secondary';
                                    }
                                @endphp

                                <span class="badge {{ $badgeClass }}">
                                    {{ $movimiento->estadoConservacion->nombre_estado }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- ⭐⭐⭐ NUEVA COLUMNA: ESTADO DEL MOVIMIENTO (VIGENTE/ANULADO) ⭐⭐⭐ --}}
                        <td class="text-center">
                            @if($movimiento->anulado)
                                <span class="badge badge-anulado"
                                    title="Anulado el {{ \Carbon\Carbon::parse($movimiento->fecha_anulacion)->format('d/m/Y H:i') }} por {{ $movimiento->usuarioAnulo->name ?? 'N/A' }}">
                                    <i class="fas fa-ban"></i> ANULADO
                                </span>
                            @else
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> VIGENTE
                                </span>
                            @endif
                        </td>

                        {{-- ⭐⭐⭐ COLUMNA: ACCIÓN (SOLO BOTÓN VER) ⭐⭐⭐ --}}
                        <td class="text-center">
                            <button type="button" class="btn btn-info btn-sm btn-ver"
                                    title="Ver Detalles"
                                    data-id="{{ $movimiento->id_movimiento }}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>



                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-info btn-ver" title="Ver Detalles" data-id="{{ $movimiento->id_movimiento }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No hay movimientos registrados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        <div id="paginacionContainer" class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <small class="text-muted">
                    Mostrando <strong id="paginaInfo">{{ $movimientos->firstItem() ?? 0 }} - {{ $movimientos->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $movimientos->total() }}</strong>
                </small>
            </div>
            <div id="paginacionLinks">
                @if($movimientos->hasPages())
                    <ul class="pagination pagination-sm m-0">
                        @if ($movimientos->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link paginar-inicial" href="#" data-page="{{ $movimientos->currentPage() - 1 }}">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        @endif

                        @foreach(range(1, $movimientos->lastPage()) as $page)
                            @if ($page == $movimientos->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @elseif ($page == 1 || $page == $movimientos->lastPage() || abs($page - $movimientos->currentPage()) <= 2)
                                <li class="page-item">
                                    <a class="page-link paginar-inicial" href="#" data-page="{{ $page }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        @if ($movimientos->hasMorePages())
                            <li class="page-item">
                                <a class="page-link paginar-inicial" href="#" data-page="{{ $movimientos->currentPage() + 1 }}">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>
        </div>

        <div id="noResultados" class="text-center py-4" style="display:none;">
            <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
            <h5>No se encontraron resultados</h5>
            <p class="text-muted">No hay movimientos que coincidan con "<strong id="terminoBuscado"></strong>"</p>
            <button class="btn btn-outline-primary" id="btnMostrarTodo">
                <i class="fas fa-undo"></i> Mostrar todo
            </button>
        </div>
    </div>
</div>


    {{-- ==========================================
     ⭐⭐⭐ MODAL VER DETALLES (CON TRAZABILIDAD) ⭐⭐⭐
     ========================================== --}}
    <div class="modal fade" id="modalVer" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle"></i> Detalles del Movimiento #<span id="ver-id">-</span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    {{-- ⭐ PESTAÑAS --}}
                    <ul class="nav nav-tabs" id="tabsModalVer" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-detalles-tab" data-toggle="tab" href="#tab-detalles" role="tab">
                                <i class="fas fa-file-alt"></i> Detalles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-trazabilidad-tab" data-toggle="tab" href="#tab-trazabilidad" role="tab">
                                <i class="fas fa-history"></i> Trazabilidad del Bien
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="tabsModalVerContent">
                        {{-- ⭐ TAB 1: DETALLES DEL MOVIMIENTO --}}
                        <div class="tab-pane fade show active" id="tab-detalles" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-5">Código Bien:</dt>
                                        <dd class="col-sm-7"><span class="badge badge-info" id="ver-codigo">-</span></dd>

                                        <dt class="col-sm-5">Denominación:</dt>
                                        <dd class="col-sm-7" id="ver-denominacion">-</dd>

                                        <dt class="col-sm-5">Tipo Movimiento:</dt>
                                        <dd class="col-sm-7"><span class="badge badge-primary" id="ver-tipo">-</span></dd>

                                        <dt class="col-sm-5">Fecha:</dt>
                                        <dd class="col-sm-7" id="ver-fecha">-</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-5">Ubicación:</dt>
                                        <dd class="col-sm-7" id="ver-ubicacion">-</dd>

                                        <dt class="col-sm-5">Estado:</dt>
                                        <dd class="col-sm-7" id="ver-estado">-</dd>

                                        <dt class="col-sm-5">Doc. Sustento:</dt>
                                        <dd class="col-sm-7" id="ver-documento">-</dd>

                                        <dt class="col-sm-5">Nro. Documento:</dt>
                                        <dd class="col-sm-7" id="ver-numdoc">-</dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h6><strong>Detalle Técnico:</strong></h6>
                                    <p class="text-muted border p-2 rounded" id="ver-detalle-tecnico">-</p>
                                </div>
                            </div>
                        </div>

                        {{-- ⭐⭐⭐ TAB 2: TRAZABILIDAD DEL BIEN ⭐⭐⭐ --}}
                        <div class="tab-pane fade" id="tab-trazabilidad" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h5 id="trazabilidad-titulo">Historial de Movimientos del Bien</h5>
                                    <p class="text-muted" id="trazabilidad-info">
                                        <i class="fas fa-box"></i> <strong id="trazabilidad-codigo">-</strong> -
                                        <span id="trazabilidad-denominacion">-</span>
                                    </p>
                                </div>
                                <div class="col-md-6 text-right">
                                    <label for="filtroTrazabilidad">Filtrar por:</label>
                                    <select id="filtroTrazabilidad" class="form-control form-control-sm d-inline-block" style="width: auto;">
                                        <option value="mes">Último mes</option>
                                        <option value="trimestre">Último trimestre</option>
                                        <option value="año">Último año</option>
                                    </select>

                                    {{-- ⭐⭐⭐ BOTÓN DE IMPRESIÓN PDF ⭐⭐⭐ --}}
                                    <button type="button" class="btn btn-sm btn-danger ml-2" id="btnImprimirTrazabilidad" title="Generar PDF">
                                        <i class="fas fa-file-pdf"></i> Imprimir PDF
                                    </button>
                                </div>
                            </div>

                            <div id="trazabilidad-loading" class="text-center py-4" style="display:none;">
                                <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                                <p class="mt-2">Cargando historial...</p>
                            </div>

                            <div id="trazabilidad-content">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="8%">ID</th>
                                                <th width="12%">Fecha</th>
                                                <th width="12%">Tipo</th>
                                                <th width="15%">Usuario</th>
                                                <th width="12%">Área</th>
                                                <th width="15%">Ubicación</th>
                                                <th width="8%">Estado</th>
                                                <th width="10%">Documento</th>
                                                <th width="8%">Detalle</th>
                                            </tr>
                                        </thead>

                                        <tbody id="tablaTrazabilidad">
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>No hay historial disponible</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div id="trazabilidad-estadisticas" class="mt-3 p-3 bg-light rounded" style="display:none;">
                                    <h6><i class="fas fa-chart-bar"></i> Estadísticas</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Total de movimientos:</strong> <span id="stat-total">0</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Último movimiento:</strong> <span id="stat-ultimo">-</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Por tipo:</strong>
                                            <ul id="stat-tipos" class="mb-0 pl-3">
                                                <li>Sin datos</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="trazabilidad-error" class="alert alert-warning text-center" style="display:none;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al cargar historial</strong>
                                <p id="trazabilidad-error-msg"></p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ⭐⭐⭐ FOOTER CON BOTONES ANULAR/RESTAURAR ⭐⭐⭐ --}}
                <div class="modal-footer">
                    {{-- ⭐ BOTÓN ANULAR (Solo si es VIGENTE y es ADMIN) --}}
                    @if(Auth::user()->esAdmin())
                
                    {{-- ⭐ BOTÓN RESTAURAR (Solo si está ANULADO y es ADMIN) --}}
                    <button type="button" class="btn btn-success" id="btnRestaurarDesdeModal" style="display:none;">
                        <i class="fas fa-undo"></i> Restaurar Movimiento
                    </button>
                    @endif
                    
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>

            </div>
        </div>
    </div>


{{-- ==========================================
     MODAL EDITAR MOVIMIENTO
     ========================================== --}}
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Movimiento #<span id="edit-id-display">-</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formEdit">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_idbien">Bien <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_idbien" name="idbien" required>
                                    <option value="">Seleccione un bien</option>
                                    @foreach($bienes as $bien)
                                        <option value="{{ $bien->id_bien }}">
                                            {{ $bien->codigo_patrimonial }} - {{ $bien->denominacion_bien }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-edit-idbien d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tipo_mvto">Tipo de Movimiento <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_tipo_mvto" name="tipo_mvto" required>
                                    <option value="">Seleccione tipo</option>
                                    @foreach($tiposMovimiento as $tipo)
                                        <option value="{{ $tipo->id_tipo_mvto }}">{{ $tipo->tipo_mvto }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-edit-tipo_mvto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_fecha_mvto">Fecha <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_fecha_mvto" name="fecha_mvto" required>
                                <span class="text-danger error-edit-fecha_mvto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_idubicacion">Ubicación</label>
                                <select class="form-control" id="edit_idubicacion" name="idubicacion">
                                    <option value="">Sin ubicación</option>
                                    @foreach($ubicaciones as $ubicacion)
                                        <option value="{{ $ubicacion->id_ubicacion }}">
                                            {{ $ubicacion->ubicacion_completa }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-edit-idubicacion d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit_id_estado_conservacion_bien">Estado de Conservación</label>
                                <select class="form-control" id="edit_id_estado_conservacion_bien" name="id_estado_conservacion_bien">
                                    <option value="">Sin estado</option>
                                    @foreach($estadosConservacion as $estado)
                                        <option value="{{ $estado->id_estado }}">{{ $estado->nombre_estado }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-edit-id_estado_conservacion_bien d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_documento_sustentatorio">
                                    <i class="fas fa-file-invoice"></i> Documento Sustento
                                </label>
                                <select class="form-control" id="edit_documento_sustentatorio" name="documento_sustentatorio">
                                    <option value="">Sin documento</option>
                                    @foreach($documentos as $doc)
                                        <option value="{{ $doc->id_documento }}">
                                            {{ $doc->tipo_documento }} - {{ $doc->numero_documento }} ({{ $doc->fecha_formateada }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-edit-documento_sustentatorio d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_NumDocto">
                                    <i class="fas fa-hashtag"></i> Número de Documento
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="edit_NumDocto"
                                    name="NumDocto"
                                    maxlength="20">
                                <span class="text-danger error-edit-NumDocto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit_detalle_tecnico">Detalle Técnico</label>
                                <textarea class="form-control" id="edit_detalle_tecnico" name="detalle_tecnico" rows="2" maxlength="500"></textarea>
                                <span class="text-danger error-edit-detalle_tecnico d-block mt-1"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="btnActualizar">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    {{-- ==========================================
        MODAL ASIGNAR MASIVO
        ========================================== --}}
    <div class="modal fade" id="modalAsignarMasivo" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-share-square"></i> Asignar Bienes Masivamente
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="formAsignarMasivo">
                    @csrf
                    <input type="hidden" id="asignar_bienes_ids" name="bienes_ids">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Se crearán movimientos de <strong>ASIGNACIÓN</strong> para <strong id="cantidadAsignar">0</strong> bienes seleccionados
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="asignar_fecha_mvto">Fecha <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="asignar_fecha_mvto" name="fecha_mvto" required>
                                    <span class="text-danger error-asignar-fecha_mvto d-block mt-1"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="asignar_idubicacion">Ubicación <span class="text-danger">*</span></label>
                                    <select class="form-control" id="asignar_idubicacion" name="idubicacion" required>
                                        <option value="">Seleccione ubicación</option>
                                        @foreach($ubicaciones as $ubicacion)
                                            <option value="{{ $ubicacion->id_ubicacion }}">
                                                {{ $ubicacion->ubicacion_completa }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger error-asignar-idubicacion d-block mt-1"></span>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="asignar_id_estado_conservacion_bien">
                                        Estado de Conservación
                                        <small class="text-muted">(Por defecto: BUENO)</small>
                                    </label>
                                    <select class="form-control" id="asignar_id_estado_conservacion_bien" name="id_estado_conservacion_bien">
                                        <option value="">Sin estado</option>
                                        @foreach($estadosConservacion as $estado)
                                            <option value="{{ $estado->id_estado }}"
                                                    @if(strtoupper(trim($estado->nombre_estado)) === 'BUENO') data-default="true" @endif>
                                                {{ $estado->nombre_estado }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger error-asignar-id_estado_conservacion_bien d-block mt-1"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="asignar_documento_sustentatorio">
                                        Documento Sustento
                                        <small class="text-muted">(Por defecto: OTRO)</small>
                                    </label>
                                    <select class="form-control" id="asignar_documento_sustentatorio" name="documento_sustentatorio">
                                        <option value="">Sin documento</option>
                                        @foreach($documentos as $doc)
                                            <option value="{{ $doc->id_documento }}"
                                                    @if(strtoupper(trim($doc->tipo_documento)) === 'OTRO' || strtoupper(trim($doc->tipo_documento)) === 'OTROS') data-default="true" @endif>
                                                {{ $doc->tipo_documento }} - {{ $doc->numero_documento }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger error-asignar-documento_sustentatorio d-block mt-1"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="asignar_NumDocto">Número de Documento</label>
                                    <input type="text" class="form-control" id="asignar_NumDocto" name="NumDocto" maxlength="20">
                                    <span class="text-danger error-asignar-NumDocto d-block mt-1"></span>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="asignar_detalle_tecnico">Detalle Técnico</label>
                                    <textarea class="form-control" id="asignar_detalle_tecnico" name="detalle_tecnico" rows="2" maxlength="500"></textarea>
                                    <span class="text-danger error-asignar-detalle_tecnico d-block mt-1"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success" id="btnGuardarAsignar">
                            <i class="fas fa-check"></i> Asignar Bienes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


{{-- ==========================================
     ⭐⭐⭐ MODAL BAJA MASIVA (NUEVO) ⭐⭐⭐
     ========================================== --}}
<div class="modal fade" id="modalBajaMasivo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle"></i> Dar de Baja Bienes Masivamente
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formBajaMasivo">
                @csrf
                <input type="hidden" id="baja_bienes_ids" name="bienes_ids">
                <div class="modal-body">
                    <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Se darán de <strong>BAJA</strong> <strong id="cantidadBaja">0</strong> bienes seleccionados.
                    <br>
                    <small class="text-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Los bienes heredarán la ubicación de su última asignación y se marcarán como estado "MALO".</strong>
                    </small>
                </div>


                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="baja_fecha_mvto">Fecha de Baja <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="baja_fecha_mvto" name="fecha_mvto" required>
                                <span class="text-danger error-baja-fecha_mvto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="baja_documento_sustentatorio">Documento Sustento</label>
                                <select class="form-control" id="baja_documento_sustentatorio" name="documento_sustentatorio">
                                    <option value="">Sin documento</option>
                                    @foreach($documentos as $doc)
                                        <option value="{{ $doc->id_documento }}">
                                            {{ $doc->tipo_documento }} - {{ $doc->numero_documento }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-baja-documento_sustentatorio d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="baja_NumDocto">Número de Documento</label>
                                <input type="text" class="form-control" id="baja_NumDocto" name="NumDocto" maxlength="20" placeholder="Ej: BAJA-2026-001">
                                <span class="text-danger error-baja-NumDocto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="baja_detalle_tecnico">Motivo de Baja <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="baja_detalle_tecnico" name="detalle_tecnico" rows="3" maxlength="500" placeholder="Describa el motivo de la baja (obsolescencia, daño irreparable, etc.)" required></textarea>
                                <small class="text-muted">Máximo 500 caracteres. Este campo es obligatorio.</small>
                                <span class="text-danger error-baja-detalle_tecnico d-block mt-1"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger" id="btnGuardarBaja">
                        <i class="fas fa-check"></i> Confirmar Baja
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    {{-- ==========================================
    ⭐ MODAL REVERTIR BAJA (RESTAURA ESTADO ANTERIOR)
    ========================================== --}}
    <div class="modal fade" id="modalRevertirBaja" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-undo-alt"></i> Revertir Baja de Bien
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="formRevertirBaja">
                    @csrf
                    <input type="hidden" id="revertir_bienes_ids" name="bienes_ids">
                    <div class="modal-body">
                        {{-- ⭐ ALERTA INFORMATIVA MEJORADA --}}
                        <div class="alert alert-info border-left-info">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle fa-2x mr-3"></i>
                                <div>
                                    <strong>Se revertirá la baja de <span id="cantidadRevertir">1</span> bien seleccionado.</strong>
                                    <br>
                                    <small class="text-muted">
                                        ✅ El bien volverá a su <strong>estado anterior</strong> (ubicación + estado de conservación previo a la baja).
                                        <br>
                                        ℹ️ Si no existía movimiento anterior, quedará sin ubicación asignada.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- ✅ Fecha de Reversión --}}
                            <div class="col-md-12 mb-3">
                                <label for="revertirfechamvto" class="font-weight-bold">
                                    <i class="fas fa-calendar-alt text-info"></i>
                                    Fecha de Reversión <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="date"
                                    class="form-control"
                                    id="revertirfechamvto"
                                    name="fechamvto"
                                    required
                                >
                                <span class="text-danger error-revertir-fechamvto d-block mt-1"></span>
                            </div>

                            {{-- ✅ Motivo de Reversión --}}
                            <div class="col-md-12 mb-3">
                                <label for="revertirdetalletecnico" class="font-weight-bold">
                                    <i class="fas fa-comment-alt text-warning"></i>
                                    Motivo de Reversión <span class="text-danger">*</span>
                                </label>
                                <textarea
                                    class="form-control"
                                    id="revertirdetalletecnico"
                                    name="detalletecnico"
                                    rows="3"
                                    maxlength="200"
                                    placeholder="Ej: Bien dado de baja por error. Se requiere reactivar para continuar su uso operativo."
                                    required
                                ></textarea>
                                <small class="form-text text-muted">
                                    <i class="fas fa-exclamation-triangle"></i> Máximo 200 caracteres. Este campo es obligatorio.
                                </small>
                                <span class="text-danger error-revertir-detalletecnico d-block mt-1"></span>
                            </div>

                            {{-- ✅ Documento Sustento --}}
                            <div class="col-md-6 mb-3">
                                <label for="revertirdocumentosustentatorio">
                                    <i class="fas fa-file-alt text-secondary"></i> Documento Sustento
                                </label>
                                <select
                                    class="form-control"
                                    id="revertirdocumentosustentatorio"
                                    name="documentosustentatorio"
                                >
                                    <option value="">Sin documento</option>
                                    @foreach($documentos as $doc)
                                        <option value="{{ $doc->id_documento }}">
                                            {{ $doc->tipo_documento }} - {{ $doc->numero_documento }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-revertir-documentosustentatorio d-block mt-1"></span>
                            </div>

                            {{-- ✅ Número de Documento --}}
                            <div class="col-md-6 mb-3">
                                <label for="revertirNumDocto">
                                    <i class="fas fa-hashtag text-secondary"></i> Número de Documento
                                </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="revertirNumDocto"
                                    name="NumDocto"
                                    maxlength="20"
                                    placeholder="Ej: REV-BAJA-2026-001"
                                >
                                <span class="text-danger error-revertir-NumDocto d-block mt-1"></span>
                            </div>
                        </div>

                        {{-- ⭐ NOTA IMPORTANTE --}}
                        <div class="alert alert-warning border-left-warning mt-2">
                            <small>
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Nota:</strong> Esta acción creará un nuevo movimiento de tipo "REVERSIÓN DE BAJA"
                                y restaurará el bien al último estado registrado antes de la baja.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-info" id="btnGuardarRevertir">
                            <i class="fas fa-check"></i> Confirmar Reversión
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    {{-- ==========================================
     ⭐⭐⭐ MODAL ANULAR MOVIMIENTO (NUEVO) ⭐⭐⭐
     ========================================== --}}
    <div class="modal fade" id="modalAnular" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-ban"></i> Anular Movimiento #<span id="anular-id">-</span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="formAnular">
                    @csrf
                    <input type="hidden" id="anular_movimiento_id">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Advertencia:</strong> El movimiento se marcará como anulado pero se mantendrá en el historial para auditoría.
                        </div>

                        <div class="form-group">
                            <label for="motivo_anulacion">
                                <i class="fas fa-comment-alt text-danger"></i>
                                Motivo de Anulación <span class="text-danger">*</span>
                            </label>
                            <textarea
                                class="form-control"
                                id="motivo_anulacion"
                                name="motivo_anulacion"
                                rows="3"
                                minlength="10"
                                maxlength="200"
                                placeholder="Describa el motivo de la anulación (mínimo 10 caracteres)"
                                required></textarea>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Máximo 200 caracteres. Este campo es obligatorio.
                            </small>
                            <span class="text-danger error-anular-motivo_anulacion d-block mt-1"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger" id="btnConfirmarAnular">
                            <i class="fas fa-check"></i> Confirmar Anulación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>







@stop

@section('js')
<script>
$(document).ready(function() {


        // ==========================================
        // ⭐⭐⭐ FUNCIÓN ACTUALIZAR ESTADÍSTICAS ⭐⭐⭐
        // ==========================================
        function actualizarEstadisticas() {
            $.ajax({
                url: '{{ route("movimiento.estadisticas") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        console.log('✅ Estadísticas recibidas:', response.data);

                        // ⭐ ACTUALIZAR SIN ANIMACIÓN (más confiable)
                        $('#cardTotalBienes').text(response.data.totalBienes);
                        $('#cardBienesAsignados').text(response.data.bienesAsignados);
                        $('#cardBienesRegistro').text(response.data.bienesRegistro);
                        $('#cardBienesBaja').text(response.data.bienesBaja);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error al actualizar estadísticas:', error);
                }
            });
        }

        // ==========================================
        // ⭐⭐⭐ FUNCIÓN PROTEGER TÍTULOS DE CARDS (VERSIÓN MEJORADA) ⭐⭐⭐
        // ==========================================
        function protegerTitulosCards() {
            // Definir los títulos correctos
            const titulos = {
                '.border-left-primary .text-primary': 'Total de Bienes',
                '.border-left-success .text-success': 'Bienes Asignados',
                '.border-left-info .text-info': 'Bienes en Registro',
                '.border-left-danger .text-danger': 'Bienes de Baja'
            };

            // Iterar sobre cada título y verificar/restaurar
            $.each(titulos, function(selector, textoCorrect) {
                const elemento = $(selector);

                if (elemento.length > 0) {
                    const textoActual = elemento.text().trim();

                    // Si está vacío o es incorrecto, restaurar
                    if (textoActual === '' || textoActual !== textoCorrect) {
                        elemento.text(textoCorrect);
                        console.log('🔧 Título restaurado: "' + textoCorrect + '"');
                    }
                } else {
                    console.warn('⚠️ Elemento no encontrado: ' + selector);
                }
            });
        }


        // ==========================================
        // ⭐⭐⭐ PROTEGER TÍTULOS AL ABRIR/CERRAR MODALES ⭐⭐⭐
        // ==========================================

        // Cuando SE ABRE cualquier modal
        $('.modal').on('show.bs.modal', function() {
            console.log('🔓 Modal abierto - Protegiendo títulos...');
            setTimeout(function() {
                protegerTitulosCards();
            }, 100); // Esperar 100ms para que el modal termine de abrir
        });

        // Cuando SE CIERRA cualquier modal
        $('.modal').on('hidden.bs.modal', function() {
            console.log('🔒 Modal cerrado - Protegiendo títulos...');
            setTimeout(function() {
                protegerTitulosCards();
            }, 100); // Esperar 100ms para que el modal termine de cerrar
        });

        // Cuando SE ESTÁ MOSTRANDO el modal (animación en progreso)
        $('.modal').on('shown.bs.modal', function() {
            console.log('✅ Modal visible - Verificando títulos...');
            protegerTitulosCards();
        });

        // ⭐ PROTECCIÓN CADA 2 SEGUNDOS (FAILSAFE)
        setInterval(function() {
            protegerTitulosCards();
        }, 2000); // Verifica cada 2 segundos





        // ==========================================
        // VARIABLES GLOBALES
        // ==========================================
        let paginaActual = 1;
        let ordenActual = 'id';        // ✅ CAMBIADO DE 'fecha' A 'id'
        let direccionActual = 'desc';
        let busquedaActual = '';
        let bienesSeleccionados = [];
        let currentBienIdForTrazabilidad = null;


        // ==========================================
        // INICIALIZACIÓN
        // ==========================================
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        const hoy = new Date().toISOString().split('T')[0];
        $('#fecha_mvto').val(hoy);
        $('#asignar_fecha_mvto').val(hoy);
        $('#baja_fecha_mvto').val(hoy);



        // ==========================================
        // BÚSQUEDA EN TIEMPO REAL
        // ==========================================
        let timeoutBusqueda;
        $('#searchInput').on('input', function() {
            clearTimeout(timeoutBusqueda);
            const termino = $(this).val().trim();

            $('#loadingSearch').show();
            $('#infoResultados').hide();

            timeoutBusqueda = setTimeout(function() {
                busquedaActual = termino;
                paginaActual = 1;
                cargarMovimientos();
            }, 500);
        });

        $('#btnLimpiar').click(function() {
            $('#searchInput').val('');
            busquedaActual = '';
            paginaActual = 1;
            cargarMovimientos();
        });

        $('#btnMostrarTodo').click(function() {
            $('#searchInput').val('');
            busquedaActual = '';
            paginaActual = 1;
            cargarMovimientos();
        });

        // ==========================================
        // ORDENAMIENTO
        // ==========================================
        $('.sortable').click(function() {
            const columna = $(this).data('column');

            if (ordenActual === columna) {
                direccionActual = direccionActual === 'asc' ? 'desc' : 'asc';
            } else {
                ordenActual = columna;
                direccionActual = 'desc';
            }

            $('.sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');

            const icon = $(this).find('.sort-icon');
            icon.removeClass('fa-sort').addClass(direccionActual === 'asc' ? 'fa-sort-up' : 'fa-sort-down');

            paginaActual = 1;
            cargarMovimientos();
        });



        // ==========================================
        // SELECCIÓN MASIVA
        // ==========================================
        $('#checkAll').change(function() {
            const isChecked = $(this).is(':checked');
            $('.checkbox-item').prop('checked', isChecked);
            actualizarBienesSeleccionados();
        });

        $(document).on('change', '.checkbox-item', function() {
            actualizarBienesSeleccionados();

            const totalCheckboxes = $('.checkbox-item').length;
            const totalChecked = $('.checkbox-item:checked').length;
            $('#checkAll').prop('checked', totalCheckboxes === totalChecked);
        });

        function actualizarBienesSeleccionados() {
        bienesSeleccionados = [];
        let tieneBaja = false;
        let totalBaja = 0;
        let tieneRegistroOAsignacion = false;

        $('.checkbox-item:checked').each(function() {
            const bienId = $(this).data('bien-id');
            if (bienId && !bienesSeleccionados.includes(bienId)) {
                bienesSeleccionados.push(bienId);
            }

            // ✅ DETECTAR TIPO DE MOVIMIENTO
            const fila = $(this).closest('tr');
            const tipoBadge = fila.find('.badge-tipo-baja');

            if (tipoBadge.length > 0) {
                // Es un movimiento de tipo BAJA
                tieneBaja = true;
                totalBaja++;
            } else {
                // Es REGISTRO o ASIGNACIÓN
                tieneRegistroOAsignacion = true;
            }
        });

        const cantidad = bienesSeleccionados.length;

        // ⭐ ACTUALIZAR CONTADORES
        $('#contadorAsignar').text(cantidad);
        $('#contadorBaja').text(cantidad);
        $('#contadorRevertir').text(totalBaja); // ✅ SOLO MUESTRA CANTIDAD DE BAJAS
        $('#contadorSeleccionados').text(cantidad);

        // ⭐ MOSTRAR/OCULTAR GRUPO COMPLETO CON ANIMACIÓN
        if (cantidad > 0) {
            $('#accionesMasivas').fadeIn(300);
        } else {
            $('#accionesMasivas').fadeOut(300);
        }

        // ✅ LÓGICA INTELIGENTE DE BOTONES SEGÚN TIPO DE MOVIMIENTO

        // 1. BOTÓN ASIGNAR: Solo visible si NO hay ningún bien de BAJA seleccionado
        if (cantidad > 0 && !tieneBaja) {
            $('#btnAsignarSeleccionados').fadeIn(200).removeClass('d-none');
        } else {
            $('#btnAsignarSeleccionados').fadeOut(200).addClass('d-none');
        }

        // 2. BOTÓN DAR DE BAJA: Solo visible si NO hay ningún bien de BAJA seleccionado
        if (cantidad > 0 && !tieneBaja) {
            $('#btnBajaSeleccionados').fadeIn(200).removeClass('d-none');
        } else {
            $('#btnBajaSeleccionados').fadeOut(200).addClass('d-none');
        }

        // 3. BOTÓN REVERTIR BAJA: Solo visible si hay EXACTAMENTE 1 bien de tipo BAJA
        if (cantidad === 1 && tieneBaja && !tieneRegistroOAsignacion) {
            $('#btnRevertirBajaSeleccionados').fadeIn(200).removeClass('d-none');
        } else {
            $('#btnRevertirBajaSeleccionados').fadeOut(200).addClass('d-none');
        }

        // 4. BOTÓN ANULAR: Solo visible si hay selección (SOLO ADMIN)
        if (cantidad > 0) {
            $('#btnAnularSeleccionados').fadeIn(200).removeClass('d-none');
        } else {
            $('#btnAnularSeleccionados').fadeOut(200).addClass('d-none');
        }

    }



    // ==========================================
    // ⭐ BOTÓN BAJA MASIVA
    // ==========================================
    $('#btnBajaSeleccionados').click(function() {
        if (bienesSeleccionados.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin selección',
                text: 'Debe seleccionar al menos un bien'
            });
            return;
        }

        // ✅ VALIDACIÓN: No permitir dar de baja a bienes que YA ESTÁN de BAJA
        let hayBaja = false;
        let bienBaja = null;

        $('.checkbox-item:checked').each(function() {
            const fila = $(this).closest('tr');
            const tipoBadge = fila.find('.badge-tipo-baja');

            if (tipoBadge.length > 0) {
                hayBaja = true;
                bienBaja = fila.find('.badge-info').first().text().trim(); // Código del bien
                return false; // break
            }
        });

        if (hayBaja) {
            Swal.fire({
                icon: 'error',
                title: '❌ Acción no permitida',
                html: `
                    <p>No puedes <strong>Dar de Baja</strong> a bienes que ya están de <strong>BAJA</strong>.</p>
                    <div class="alert alert-warning mt-3 mb-3" style="font-size: 0.9rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Bien detectado: <strong>${bienBaja}</strong>
                    </div>
                    <p class="text-muted small">
                        <i class="fas fa-info-circle"></i>
                        El bien ya fue dado de baja anteriormente.
                    </p>
                `,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#6c757d'
            });
            return;
        }

        // ✅ TODO CORRECTO - CONTINUAR
        $('#cantidadBaja').text(bienesSeleccionados.length);
        $('#baja_bienes_ids').val(JSON.stringify(bienesSeleccionados));
        $('#baja_fecha_mvto').val(new Date().toISOString().split('T')[0]);
        $('#baja_detalle_tecnico').val('');
        $('#baja_documento_sustentatorio').val('');
        $('#baja_NumDocto').val('');

        $('.text-danger').text('');

        $('#modalBajaMasivo').modal('show');
    });

    $('#formBajaMasivo').submit(function(e) {
        e.preventDefault();

        Swal.fire({
            title: '⚠️ ¿Confirmar baja?',
            html: `
                <p>Se darán de <strong class="text-danger">BAJA</strong> ${bienesSeleccionados.length} bien(es).</p>
                <p class="text-muted small mt-2">
                    <i class="fas fa-exclamation-triangle"></i>
                    Los bienes quedarán sin ubicación ni estado de conservación.
                </p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Sí, dar de baja',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                enviarBajaMasivo();
            }
        });
    });

    function enviarBajaMasivo() {
        const formData = {
            bienes_ids: bienesSeleccionados,
            fecha_mvto: $('#baja_fecha_mvto').val(),
            detalle_tecnico: $('#baja_detalle_tecnico').val(),
            documento_sustentatorio: $('#baja_documento_sustentatorio').val() || null,
            NumDocto: $('#baja_NumDocto').val() || null
        };

        $('#btnGuardarBaja').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        $.ajax({
            url: '{{ route("movimiento.baja-masivo") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#modalBajaMasivo').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: '✅ ¡Baja exitosa!',
                        html: `
                            <p>${response.message}</p>
                            <hr>
                            <small class="text-muted">
                                <i class="fas fa-check-circle text-success"></i>
                                ${bienesSeleccionados.length} bien(es) dado(s) de baja correctamente
                            </small>
                        `,
                        timer: 3500,
                        timerProgressBar: true
                    });

                    cargarMovimientos();

                    // ⭐⭐⭐ ACTUALIZAR ESTADÍSTICAS ⭐⭐⭐
                    actualizarEstadisticas();
                    protegerTitulosCards();

                    // Limpiar selección
                    $('.checkbox-item').prop('checked', false);
                    $('#checkAll').prop('checked', false);
                    bienesSeleccionados = [];
                    actualizarBienesSeleccionados();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('.text-danger').text('');

                    $.each(errors, function(key, value) {
                        $(`.error-baja-${key}`).text(value[0]);
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: 'Revise los campos marcados en rojo'
                    });
                } else if (xhr.status === 400) {
                    Swal.fire({
                        icon: 'warning',
                        title: '⚠️ No se puede dar de baja',
                        text: xhr.responseJSON?.message || 'Uno o más bienes no pueden ser dados de baja'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al dar de baja los bienes'
                    });
                }
            },
            complete: function() {
                $('#btnGuardarBaja').prop('disabled', false).html('<i class="fas fa-check"></i> Confirmar Baja');
            }
        });
    }


    // ==========================================
    // ⭐ BOTÓN REVERTIR BAJA MASIVA
    // ==========================================
    $('#btnRevertirBajaSeleccionados').click(function() {
        // ✅ VALIDAR QUE SOLO HAY 1 BIEN SELECCIONADO
        if (bienesSeleccionados.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin selección',
                text: 'Debe seleccionar exactamente UN bien para revertir la baja'
            });
            return;
        }

        if (bienesSeleccionados.length > 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Selección múltiple no permitida',
                text: 'Solo puede revertir la baja de UN bien a la vez. Por favor seleccione solo un registro.'
            });
            return;
        }

        // ✅ VALIDAR QUE EL BIEN SELECCIONADO SEA DE TIPO "BAJA"
        const checkboxSeleccionado = $('.checkbox-item:checked');
        const fila = checkboxSeleccionado.closest('tr');
        const tipoBadge = fila.find('.badge-tipo-baja');

        if (tipoBadge.length === 0) {
            Swal.fire({
                icon: 'error',
                title: '❌ No es un bien dado de baja',
                html: `
                    <p>El bien seleccionado no está en estado de <strong>BAJA</strong>.</p>
                    <p class="text-muted small mt-2">
                        <i class="fas fa-info-circle"></i>
                        Solo puedes revertir bienes que están dados de baja.
                    </p>
                `,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#6c757d'
            });
            return;
        }

        // ✅ OBTENER INFO DEL BIEN PARA MOSTRAR EN EL MODAL
        const codigoBien = fila.find('.badge-info').first().text().trim();

        // ✅ TODO CORRECTO - PROCEDER CON LA REVERSIÓN
        $('#cantidadRevertir').text('1');
        $('#revertir_bienes_ids').val(JSON.stringify(bienesSeleccionados));

        // ✅✅✅ FECHA ACTUAL (SIN BLADE, USA JAVASCRIPT) ✅✅✅
        const hoy = new Date().toISOString().split('T')[0];
        $('#revertirfechamvto').val(hoy);  // ✅ CORREGIDO - Sin guion bajo

        // ✅✅✅ LIMPIAR CAMPOS (IDs CORREGIDOS) ✅✅✅
        $('#revertirdetalletecnico').val('');              // ✅ CORREGIDO
        $('#revertirdocumentosustentatorio').val('');      // ✅ CORREGIDO
        $('#revertirNumDocto').val('');                    // ✅ CORREGIDO

        // Limpiar errores previos
        $('.text-danger').text('');

        // ✅ OPCIONAL: Actualizar título del modal con info del bien
        $('#modalRevertirBaja .modal-title').html(`
            <i class="fas fa-undo-alt"></i> Revertir Baja de Bien
            <small class="d-block mt-1" style="font-size: 0.8rem; font-weight: normal;">
                <i class="fas fa-box"></i> ${codigoBien}
            </small>
        `);

        $('#modalRevertirBaja').modal('show');
    });

    // ==========================================
    // ⭐ SUBMIT DEL FORMULARIO REVERTIR BAJA
    // ==========================================
    $('#formRevertirBaja').submit(function(e) {
        e.preventDefault();

        Swal.fire({
            title: '¿Confirmar reversión?',
            text: `Se revertirá la baja de ${bienesSeleccionados.length} bien(es)`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, revertir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                enviarRevertirBaja();
            }
        });
    });

    /**
     * ⭐ ENVIAR REVERSIÓN DE BAJA
     * Procesa la reversión de un movimiento de baja
     */
    /**
     * ⭐ ENVIAR REVERSIÓN DE BAJA
     * Procesa la reversión de un movimiento de baja
     */
    function enviarRevertirBaja() {
        // ✅ VALIDAR UNA VEZ MÁS (por seguridad)
        if (bienesSeleccionados.length !== 1) {
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'Debe seleccionar exactamente UN bien'
            });
            return;
        }

        const bienId = bienesSeleccionados[0];

        // ✅✅✅ PREPARAR DATOS CON GUIONES BAJOS (PARA EL BACKEND) ✅✅✅
        const formData = {
            detalle_tecnico: $('#revertirdetalletecnico').val().trim(),      // ✅ CON guion bajo
            fecha_mvto: $('#revertirfechamvto').val(),                        // ✅ CON guion bajo
            documento_sustentatorio: $('#revertirdocumentosustentatorio').val() || null,  // ✅ CON guion bajo
            NumDocto: $('#revertirNumDocto').val() || null
        };

        console.log('📤 Datos enviados:', formData);  // DEBUG

        // ✅ VALIDAR QUE NO EXCEDA 200 CARACTERES
        if (formData.detalle_tecnico.length > 200) {
            Swal.fire({
                icon: 'error',
                title: 'Texto demasiado largo',
                text: 'El motivo no puede exceder los 200 caracteres'
            });
            return;
        }

        // ✅ VALIDAR QUE NO ESTÉ VACÍO
        if (formData.detalle_tecnico.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo obligatorio',
                text: 'Debe ingresar un motivo de reversión'
            });
            return;
        }

        // ✅ VALIDAR QUE LA FECHA NO ESTÉ VACÍA
        if (!formData.fecha_mvto || formData.fecha_mvto.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Campo obligatorio',
                text: 'Debe seleccionar una fecha de reversión'
            });
            return;
        }

        // Deshabilitar botón con loading
        $('#btnGuardarRevertir').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        $.ajax({
            url: `/movimiento/revertir-baja/${bienId}`,
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('✅ Respuesta:', response);  // DEBUG

                if (response.success) {
                    $('#modalRevertirBaja').modal('hide');

                    // ✅ MENSAJE DE ÉXITO
                    Swal.fire({
                        icon: 'success',
                        title: '✅ Reversión exitosa!',
                        html: `
                            <p><strong>${response.message}</strong></p>
                            <hr>
                            <div class="text-left" style="font-size: 0.9rem;">
                                <p class="mb-2">
                                    <i class="fas fa-info-circle text-info"></i>
                                    <strong>Bien:</strong> ${response.data.bien.codigo}
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-barcode text-secondary"></i>
                                    <strong>Denominación:</strong> ${response.data.bien.denominacion}
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <strong>Estado:</strong> ${response.data.estadorestaurado}
                                </p>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-clock"></i>
                                    Revertido el ${response.data.movimientooriginal.fechareversion}
                                </p>
                            </div>
                        `,
                        width: '500px',
                        timer: 5000,
                        timerProgressBar: true
                    }).then(() => {
                        // ✅✅✅ RECARGAR DATOS DESPUÉS DEL MODAL ✅✅✅
                        cargarMovimientos();
                        actualizarEstadisticas();
                        protegerTitulosCards();
                    });

                    // ✅ LIMPIAR FORMULARIO
                    $('#formRevertirBaja')[0].reset();
                    $('.text-danger').text('');

                    // ✅ LIMPIAR SELECCIÓN
                    $('.checkbox-item').prop('checked', false);
                    $('#checkAll').prop('checked', false);
                    bienesSeleccionados = [];
                    actualizarBienesSeleccionados();
                }
            },
            error: function(xhr) {
                console.error('❌ Error:', xhr.responseJSON);  // DEBUG

                let mensajeError = 'Error al revertir baja';
                let tituloError = 'Error';
                let icono = 'error';

                // ✅ MANEJO DE ERRORES DETALLADO
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('.text-danger').text('');

                    // ✅✅✅ MAPEAR ERRORES: detalle_tecnico → detalletecnico ✅✅✅
                    $.each(errors, function(key, value) {
                        // Convertir: detalle_tecnico → detalletecnico
                        const keyHtml = key.replace(/_/g, '');
                        $(`.error-revertir-${keyHtml}`).text(value[0]);
                        console.log(`🔴 ${key} → .error-revertir-${keyHtml}: ${value[0]}`);
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        html: '<p>Revise los campos marcados en rojo</p>',
                        timer: 3000
                    });
                    return;
                } else if (xhr.status === 403) {
                    tituloError = 'Acceso denegado';
                    mensajeError = 'Solo el administrador puede revertir bajas';
                } else if (xhr.status === 404) {
                    tituloError = 'No encontrado';
                    mensajeError = xhr.responseJSON?.message || 'El bien o movimiento no fue encontrado';
                } else if (xhr.status === 400) {
                    icono = 'warning';
                    tituloError = 'No se puede revertir';
                    mensajeError = xhr.responseJSON?.message || 'Este movimiento no se puede revertir';
                } else if (xhr.status === 500) {
                    tituloError = 'Error del servidor';
                    mensajeError = xhr.responseJSON?.message || 'Ocurrió un error interno. Contacte al administrador.';
                } else {
                    mensajeError = xhr.responseJSON?.message || 'Error desconocido al procesar la reversión';
                }

                Swal.fire({
                    icon: icono,
                    title: tituloError,
                    text: mensajeError,
                    timer: 4000,
                    timerProgressBar: true
                });
            },
            complete: function() {
                // ✅ REHABILITAR BOTÓN
                $('#btnGuardarRevertir').prop('disabled', false)
                    .html('<i class="fas fa-check"></i> Confirmar Reversión');
            }
        });
    }









    // ==========================================
    // ⭐ BOTÓN ASIGNAR MASIVO
    // ==========================================
    $('#btnAsignarSeleccionados').click(function() {
        if (bienesSeleccionados.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin selección',
                text: 'Debe seleccionar al menos un bien'
            });
            return;
        }

        // ✅ VALIDACIÓN: No permitir asignar bienes que están de BAJA
        let hayBaja = false;
        let bienBaja = null;

        $('.checkbox-item:checked').each(function() {
            const fila = $(this).closest('tr');
            const tipoBadge = fila.find('.badge-tipo-baja');

            if (tipoBadge.length > 0) {
                hayBaja = true;
                bienBaja = fila.find('.badge-info').first().text().trim(); // Código del bien
                return false; // break
            }
        });

        if (hayBaja) {
            Swal.fire({
                icon: 'error',
                title: '❌ Acción no permitida',
                html: `
                    <p>No puedes <strong>Asignar</strong> bienes que están dados de <strong>BAJA</strong>.</p>
                    <div class="alert alert-warning mt-3 mb-3" style="font-size: 0.9rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Bien detectado: <strong>${bienBaja}</strong>
                    </div>
                    <p class="text-muted small">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Solución:</strong> Primero debes <strong>Revertir la Baja</strong> y luego podrás asignar el bien.
                    </p>
                `,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#6c757d'
            });
            return;
        }

        // ✅ TODO CORRECTO - PREPARAR MODAL
        $('#cantidadAsignar').text(bienesSeleccionados.length);
        $('#asignar_bienes_ids').val(JSON.stringify(bienesSeleccionados));
        $('#asignar_fecha_mvto').val(new Date().toISOString().split('T')[0]);
        $('#asignar_idubicacion').val(''); // Usuario debe seleccionar manualmente
        $('#asignar_detalle_tecnico').val('');
        $('#asignar_NumDocto').val('');

        // ⭐⭐⭐ SELECCIÓN AUTOMÁTICA DE VALORES POR DEFECTO ⭐⭐⭐
        // Estado de Conservación → BUENO (automático)
        const estadoDefault = $('#asignar_id_estado_conservacion_bien option[data-default="true"]').first();
        if (estadoDefault.length > 0) {
            estadoDefault.prop('selected', true);
            $('#asignar_id_estado_conservacion_bien').trigger('change'); // Forzar actualización visual
        } else {
            // Fallback: Si no hay data-default, limpiar
            $('#asignar_id_estado_conservacion_bien').val('');
        }

        // Documento Sustentatorio → OTRO (automático)
        const documentoDefault = $('#asignar_documento_sustentatorio option[data-default="true"]').first();
        if (documentoDefault.length > 0) {
            documentoDefault.prop('selected', true);
            $('#asignar_documento_sustentatorio').trigger('change'); // Forzar actualización visual
        } else {
            // Fallback: Si no hay data-default, limpiar
            $('#asignar_documento_sustentatorio').val('');
        }

        // Limpiar mensajes de error previos
        $('.text-danger').text('');

        $('#modalAsignarMasivo').modal('show');
    });

    $('#formAsignarMasivo').submit(function(e) {
        e.preventDefault();

        const formData = {
            bienes_ids: bienesSeleccionados,
            fecha_mvto: $('#asignar_fecha_mvto').val(),
            idubicacion: $('#asignar_idubicacion').val(),
            id_estado_conservacion_bien: $('#asignar_id_estado_conservacion_bien').val() || null,
            detalle_tecnico: $('#asignar_detalle_tecnico').val() || null,
            documento_sustentatorio: $('#asignar_documento_sustentatorio').val() || null,
            NumDocto: $('#asignar_NumDocto').val() || null
        };

        $('#btnGuardarAsignar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        $.ajax({
            url: '{{ route("movimiento.asignar-masivo") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#modalAsignarMasivo').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: '✅ ¡Asignación exitosa!',
                        html: `
                            <p>${response.message}</p>
                            <hr>
                            <small class="text-muted">
                                <i class="fas fa-check-circle text-success"></i>
                                ${bienesSeleccionados.length} bien(es) asignado(s) correctamente
                            </small>
                        `,
                        timer: 3500,
                        timerProgressBar: true
                    });

                    cargarMovimientos();

                    // ⭐⭐⭐ ACTUALIZAR ESTADÍSTICAS ⭐⭐⭐
                    actualizarEstadisticas();
                    protegerTitulosCards();

                    // Limpiar selección
                    $('.checkbox-item').prop('checked', false);
                    $('#checkAll').prop('checked', false);
                    bienesSeleccionados = [];
                    actualizarBienesSeleccionados();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('.text-danger').text('');

                    $.each(errors, function(key, value) {
                        $(`.error-asignar-${key}`).text(value[0]);
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: 'Revise los campos marcados en rojo'
                    });
                } else if (xhr.status === 400) {
                    Swal.fire({
                        icon: 'warning',
                        title: '⚠️ No se puede asignar',
                        text: xhr.responseJSON?.message || 'Uno o más bienes no pueden ser asignados'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al asignar bienes'
                    });
                }
            },
            complete: function() {
                $('#btnGuardarAsignar').prop('disabled', false).html('<i class="fas fa-check"></i> Asignar Bienes');
            }
        });
    });



    // ==========================================
    // ⭐ ELIMINAR MOVIMIENTOS SELECCIONADOS (HARD DELETE)
    // ==========================================
    $('#btnEliminarSeleccionados').click(function() {
        if (bienesSeleccionados.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin selección',
                text: 'Debe seleccionar al menos un movimiento'
            });
            return;
        }

        // ✅ OBTENER IDs DE LOS MOVIMIENTOS SELECCIONADOS (NO DE BIENES)
        let movimientosIds = [];
        $('.checkbox-item:checked').each(function() {
            const fila = $(this).closest('tr');
            const movimientoId = fila.attr('id').replace('row-', ''); // Extraer ID del movimiento
            movimientosIds.push(parseInt(movimientoId));
        });

        Swal.fire({
            title: '¿Eliminar movimientos?',
            html: `
                <p>Se eliminarán (lógico) <strong>${movimientosIds.length}</strong> movimiento(s).</p>
                <p class="text-muted small mt-2">
                    <i class="fas fa-info-circle"></i>
                    Los bienes quedarán inactivos pero conservarán su historial de movimientos.
                </p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarMovimientosMasivo(movimientosIds);
            }
        });
    });

    // ==========================================
    // ⭐⭐⭐ ANULAR MOVIMIENTO INDIVIDUAL ⭐⭐⭐
    // ==========================================
    $(document).on('click', '.btn-anular-individual', function() {
        const movimientoId = $(this).data('id');
        const fila = $(this).closest('tr');
        const codigoBien = fila.find('.badge-info').first().text().trim();
        
        $('#anular-id').text(movimientoId);
        $('#anular_movimiento_id').val(movimientoId);
        $('#motivo_anulacion').val('');
        $('.error-anular-motivo_anulacion').text('');
        
        $('#modalAnular').modal('show');
    });

    // ==========================================
    // ⭐⭐⭐ SUBMIT FORMULARIO ANULAR ⭐⭐⭐
    // ==========================================
    // ==========================================
    // ⭐⭐⭐ SUBMIT FORMULARIO ANULAR (INDIVIDUAL O MASIVO) ⭐⭐⭐
    // ==========================================
    $('#formAnular').submit(function(e) {
        e.preventDefault();
        
        const movimientoIdRaw = $('#anular_movimiento_id').val();
        const motivo = $('#motivo_anulacion').val().trim();
        
        // Validación básica
        if (motivo.length < 10) {
            Swal.fire({
                icon: 'warning',
                title: 'Motivo insuficiente',
                text: 'El motivo debe tener al menos 10 caracteres'
            });
            return;
        }
        
        // ⭐⭐⭐ DETECTAR SI ES INDIVIDUAL O MASIVO ⭐⭐⭐
        let esIndividual = true;
        let movimientoId;
        let movimientosIds = [];
        
        try {
            // Intentar parsear como JSON (si es masivo)
            movimientosIds = JSON.parse(movimientoIdRaw);
            esIndividual = false;
        } catch (e) {
            // Si falla, es individual
            movimientoId = parseInt(movimientoIdRaw);
            esIndividual = true;
        }
        
        $('#btnConfirmarAnular').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Anulando...');
        
        if (esIndividual) {
            // ⭐ ANULAR INDIVIDUAL
            $.ajax({
                url: `/movimiento/${movimientoId}/anular`,
                method: 'POST',
                data: { motivo_anulacion: motivo },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        $('#modalAnular').modal('hide');
                        
                        Swal.fire({
                            icon: 'success',
                            title: '✅ Movimiento anulado',
                            html: `
                                <p>${response.message}</p>
                                <hr>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    El movimiento se mantiene en el historial para auditoría
                                </small>
                            `,
                            timer: 3500,
                            timerProgressBar: true
                        });
                        
                        cargarMovimientos();
                        actualizarEstadisticas();
                        protegerTitulosCards();
                    }
                },
                error: function(xhr) {
                    manejarErrorAnular(xhr);
                },
                complete: function() {
                    $('#btnConfirmarAnular').prop('disabled', false)
                        .html('<i class="fas fa-check"></i> Confirmar Anulación');
                }
            });
        } else {
            // ⭐ ANULAR MASIVO
            $.ajax({
                url: '{{ route("movimiento.anular-masivo") }}',
                method: 'POST',
                data: {
                    movimientos_ids: movimientosIds,
                    motivo_anulacion: motivo
                },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        $('#modalAnular').modal('hide');
                        
                        Swal.fire({
                            icon: 'success',
                            title: '✅ Movimientos anulados',
                            html: `
                                <p>${response.message}</p>
                                <hr>
                                <small class="text-muted">
                                    <i class="fas fa-check-circle"></i>
                                    ${response.cantidad} movimiento(s) anulado(s)
                                </small>
                            `,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        
                        cargarMovimientos();
                        actualizarEstadisticas();
                        protegerTitulosCards();
                        
                        // Limpiar selección
                        $('.checkbox-item').prop('checked', false);
                        $('#checkAll').prop('checked', false);
                        bienesSeleccionados = [];
                        actualizarBienesSeleccionados();
                    }
                },
                error: function(xhr) {
                    manejarErrorAnular(xhr);
                },
                complete: function() {
                    $('#btnConfirmarAnular').prop('disabled', false)
                        .html('<i class="fas fa-check"></i> Confirmar Anulación');
                }
            });
        }
    });

    // ⭐ FUNCIÓN AUXILIAR PARA MANEJAR ERRORES
    function manejarErrorAnular(xhr) {
        if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;
            $('.error-anular-motivo_anulacion').text(errors.motivo_anulacion ? errors.motivo_anulacion[0] : '');
            
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'Revise el motivo ingresado'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: xhr.responseJSON?.message || 'Error al anular',
                text: xhr.status === 403 ? 'Solo el administrador puede anular movimientos' : ''
            });
        }
    }


    // ==========================================
    // ⭐⭐⭐ RESTAURAR MOVIMIENTO ANULADO ⭐⭐⭐
    // ==========================================
    $(document).on('click', '.btn-restaurar', function() {
        const movimientoId = $(this).data('id');
        const fila = $(this).closest('tr');
        const codigoBien = fila.find('.badge-info').first().text().trim();
        
        Swal.fire({
            title: '¿Restaurar movimiento?',
            html: `
                <p>Se restaurará el movimiento <strong>#${movimientoId}</strong></p>
                <p class="text-muted small">
                    <i class="fas fa-info-circle"></i> Bien: ${codigoBien}
                </p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Sí, restaurar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                restaurarMovimiento(movimientoId);
            }
        });
    });

    /**
     * ⭐ FUNCIÓN RESTAURAR MOVIMIENTO
     */
    function restaurarMovimiento(movimientoId) {
        $.ajax({
            url: `/movimiento/${movimientoId}/restaurar`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '✅ Movimiento restaurado',
                        text: response.message,
                        timer: 2500,
                        timerProgressBar: true
                    });
                    
                    // Recargar tabla
                    cargarMovimientos();
                    actualizarEstadisticas();
                    protegerTitulosCards();
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al restaurar',
                    text: xhr.responseJSON?.message || 'Error desconocido',
                    footer: xhr.status === 403 ? '<small>Solo el administrador puede restaurar movimientos</small>' : ''
                });
            }
        });
    }

    // ==========================================
    // ⭐⭐⭐ ANULAR MOVIMIENTOS MASIVAMENTE (CON MODAL DETALLADO) ⭐⭐⭐
    // ==========================================
    $('#btnAnularSeleccionados').click(function() {
        if (bienesSeleccionados.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin selección',
                text: 'Debe seleccionar al menos un movimiento'
            });
            return;
        }
        
        // ⭐⭐⭐ OBTENER IDs DE MOVIMIENTOS SELECCIONADOS ⭐⭐⭐
        let movimientosIds = [];
        $('.checkbox-item:checked').each(function() {
            const fila = $(this).closest('tr');
            const movimientoId = fila.attr('id').replace('row-', '');
            movimientosIds.push(parseInt(movimientoId));
        });
        
        // ⭐⭐⭐ ABRIR MODAL DE ANULACIÓN (IGUAL QUE INDIVIDUAL) ⭐⭐⭐
        $('#anular-id').text(movimientosIds.length + ' movimiento(s)');
        $('#anular_movimiento_id').val(JSON.stringify(movimientosIds)); // Array en JSON
        $('#motivo_anulacion').val('');
        $('.error-anular-motivo_anulacion').text('');
        
        // ⭐ Cambiar título del modal para indicar que es masivo
        $('#modalAnular .modal-title').html(`
            <i class="fas fa-ban"></i> Anular ${movimientosIds.length} Movimiento(s)
        `);
        
        $('#modalAnular').modal('show');
    });









    // ==========================================
    // ⭐ VER MOVIMIENTO (CON TRAZABILIDAD Y BOTONES)
    // ==========================================
    $(document).on('click', '.btn-ver', function() {
        const id = $(this).data('id');

        $.ajax({
            url: `/movimiento/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;

                    $('#ver-id').text(data.id_movimiento);
                    $('#ver-codigo').text(data.bien.codigo_patrimonial);
                    $('#ver-denominacion').text(data.bien.denominacion_bien);
                    $('#ver-tipo').text(data.tipo_movimiento.tipo_mvto);
                    $('#ver-fecha').text(typeof moment !== 'undefined' ? moment(data.fecha_mvto).format('DD/MM/YYYY HH:mm:ss') : data.fecha_mvto);
                    $('#ver-ubicacion').text(data.ubicacion ? data.ubicacion.nombre_sede : 'Sin ubicación');
                    $('#ver-estado').text(data.estado_conservacion ? data.estado_conservacion.nombre_estado : 'Sin estado');
                    $('#ver-usuario').text(data.usuario.name);
                    $('#ver-documento').text(data.documento_sustento ?
                        `${data.documento_sustento.tipo_documento} - ${data.documento_sustento.numero_documento}` :
                        'Sin documento');
                    $('#ver-numdoc').text(data.NumDocto || 'Sin número');
                    $('#ver-detalle-tecnico').text(data.detalle_tecnico || 'Sin detalle');

                    currentBienIdForTrazabilidad = data.bien.id_bien;

                    // ⭐⭐⭐ MOSTRAR/OCULTAR BOTONES SEGÚN ESTADO DEL MOVIMIENTO ⭐⭐⭐
                    if (data.anulado) {
                        // Si está ANULADO → mostrar botón RESTAURAR
                        $('#btnRestaurarDesdeModal').show().data('id', id);
                        $('#btnAnularDesdeModal').hide();
                    } else {
                        // Si está VIGENTE → mostrar botón ANULAR
                        $('#btnAnularDesdeModal').show().data('id', id);
                        $('#btnRestaurarDesdeModal').hide();
                    }

                    $('#tab-detalles-tab').tab('show');
                    $('#modalVer').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el movimiento'
                });
            }
        });
    });



    // ==========================================
    // ⭐⭐⭐ ANULAR DESDE MODAL DE DETALLES ⭐⭐⭐
    // ==========================================
    $(document).on('click', '#btnAnularDesdeModal', function() {
        const movimientoId = $(this).data('id');
        
        // Cerrar modal de detalles
        $('#modalVer').modal('hide');
        
        // Esperar a que el modal se cierre completamente antes de abrir el siguiente
        setTimeout(function() {
            // Abrir modal de anulación
            $('#anular-id').text(movimientoId);
            $('#anular_movimiento_id').val(movimientoId);
            $('#motivo_anulacion').val('');
            $('.error-anular-motivo_anulacion').text('');
            
            // Restaurar título del modal (por si venía de masivo)
            $('#modalAnular .modal-title').html(`
                <i class="fas fa-ban"></i> Anular Movimiento #${movimientoId}
            `);
            
            $('#modalAnular').modal('show');
        }, 300); // 300ms para que termine la animación de cierre
    });

    // ==========================================
    // ⭐⭐⭐ RESTAURAR DESDE MODAL DE DETALLES ⭐⭐⭐
    // ==========================================
    $(document).on('click', '#btnRestaurarDesdeModal', function() {
        const movimientoId = $(this).data('id');
        
        // Cerrar modal de detalles
        $('#modalVer').modal('hide');
        
        // Esperar a que el modal se cierre antes de mostrar el SweetAlert
        setTimeout(function() {
            Swal.fire({
                title: '¿Restaurar movimiento?',
                html: `
                    <p>Se restaurará el movimiento <strong>#${movimientoId}</strong></p>
                    <p class="text-muted small mt-2">
                        <i class="fas fa-info-circle"></i> 
                        El movimiento volverá a estar activo en el sistema
                    </p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check"></i> Sí, restaurar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    restaurarMovimiento(movimientoId);
                }
            });
        }, 300); // 300ms para que termine la animación
    });



    // ==========================================
    // ⭐ CARGAR TRAZABILIDAD AL CAMBIAR TAB
    // ==========================================
    $('#tab-trazabilidad-tab').on('shown.bs.tab', function() {
        if (currentBienIdForTrazabilidad) {
            cargarTrazabilidad(currentBienIdForTrazabilidad, 'todos');
        }
    });

    $('#filtroTrazabilidad').change(function() {
        const filtro = $(this).val();
        if (currentBienIdForTrazabilidad) {
            cargarTrazabilidad(currentBienIdForTrazabilidad, filtro);
        }
    });

    function cargarTrazabilidad(bienId, filtro = 'todos') {
        $('#trazabilidad-loading').show();
        $('#trazabilidad-content').hide();
        $('#trazabilidad-error').hide();

        $.ajax({
            url: `/movimiento/trazabilidad/${bienId}`,
            method: 'GET',
            data: { filtro: filtro },
            success: function(response) {
                if (response.success) {
                    const bien = response.bien;
                    let movimientos = response.data;
                    const stats = response.estadisticas;

                    $('#trazabilidad-codigo').text(bien.codigo_patrimonial);
                    $('#trazabilidad-denominacion').text(bien.denominacion_bien);

                    $('#tablaTrazabilidad').empty();

                    if (movimientos.length === 0) {
                    $('#tablaTrazabilidad').html(`
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No hay movimientos en este rango de tiempo</p>
                                </td>
                            </tr>
                        `);
                    } else {
                        // ✅✅✅ ORDENAR POR ID DESCENDENTE (MÁS RECIENTE PRIMERO) ✅✅✅
                        movimientos.sort((a, b) => {
                            // Primero ordenar por fecha (descendente)
                            const fechaA = new Date(a.fecha_mvto);
                            const fechaB = new Date(b.fecha_mvto);

                            if (fechaB.getTime() !== fechaA.getTime()) {
                                return fechaB - fechaA;
                            }

                            // Si las fechas son iguales, ordenar por ID (descendente)
                            return b.id_movimiento - a.id_movimiento;
                        });

                        movimientos.forEach(function(mov) {
                            const fecha = typeof moment !== 'undefined' ?
                                moment(mov.fecha_mvto).format('DD/MM/YYYY') :
                                mov.fecha_mvto;
                            const tipo = mov.tipo_movimiento ? mov.tipo_movimiento.tipo_mvto : '-';
                            const usuario = mov.usuario ? mov.usuario.name : '-';

                            // ⭐⭐⭐ NUEVO: EXTRAER ÁREA ⭐⭐⭐
                            const area = (mov.ubicacion && mov.ubicacion.area) ?
                                mov.ubicacion.area.nombre_area : '-';

                            const ubicacion = mov.ubicacion ? mov.ubicacion.nombre_sede : '-';
                            const estado = mov.estado_conservacion ? mov.estado_conservacion.nombre_estado : '-';
                            const documento = mov.documento_sustento ?
                                `${mov.documento_sustento.tipo_documento} ${mov.documento_sustento.numero_documento}` : '-';

                            // ⭐ EXTRAER MOTIVO/DETALLE
                            const detalle = mov.detalle_tecnico ?
                                (mov.detalle_tecnico.length > 30 ?
                                    mov.detalle_tecnico.substring(0, 30) + '...' :
                                    mov.detalle_tecnico) :
                                '-';


                            let badgeClass = 'badge-secondary';

                                // 🟦 SIN ASIGNAR o REGISTRO = Celeste/Azul
                                if (tipo.toLowerCase().includes('sin asignar') || tipo.toLowerCase().includes('registro')) {
                                    badgeClass = 'badge-primary';
                                }
                                // 🟩 ASIGNACIÓN = Verde
                                else if (tipo.toLowerCase().includes('asignaci')) {
                                    badgeClass = 'badge-success';
                                }
                                // 🟥 BAJA = Rojo
                                else if (tipo.toLowerCase().includes('baja')) {
                                    badgeClass = 'badge-danger';
                                }
                                // 🔵 REVERSIÓN = Azul Info
                                else if (tipo.toLowerCase().includes('revers')) {
                                    badgeClass = 'badge-info';
                                }


                            $('#tablaTrazabilidad').append(`
                                <tr>
                                    <td class="text-center"><strong>${mov.id_movimiento}</strong></td>
                                    <td><strong>${fecha}</strong></td>
                                    <td><span class="badge ${badgeClass}">${tipo}</span></td>
                                    <td><i class="fas fa-user"></i> ${usuario}</td>
                                    <td>
                                        <i class="fas fa-building text-warning"></i>
                                        <strong>${area}</strong>
                                    </td>
                                    <td><i class="fas fa-map-marker-alt text-danger"></i> ${ubicacion}</td>
                                    <td><small>${estado}</small></td>
                                    <td><small>${documento}</small></td>
                                    <td>
                                        <small class="text-muted" title="${mov.detalle_tecnico || 'Sin detalle'}">
                                            <i class="fas fa-comment-dots"></i> ${detalle}
                                        </small>
                                    </td>
                                </tr>
                            `);

                        });

                        $('#stat-total').text(stats.total_movimientos);
                        $('#stat-ultimo').text(stats.ultimo_movimiento ?
                            (typeof moment !== 'undefined' ?
                                moment(stats.ultimo_movimiento).format('DD/MM/YYYY HH:mm') :
                                stats.ultimo_movimiento) : '-');

                        $('#stat-tipos').empty();
                        if (stats.tipos && Object.keys(stats.tipos).length > 0) {
                            $.each(stats.tipos, function(tipo, cantidad) {
                                $('#stat-tipos').append(`<li>${tipo}: ${cantidad}</li>`);
                            });
                        } else {
                            $('#stat-tipos').append('<li>Sin datos</li>');
                        }

                        $('#trazabilidad-estadisticas').show();
                    }

                    $('#trazabilidad-loading').hide();
                    $('#trazabilidad-content').show();
                }
            },
            error: function(xhr) {
                $('#trazabilidad-loading').hide();
                $('#trazabilidad-error').show();
                $('#trazabilidad-error-msg').text(xhr.responseJSON?.message || 'Error al cargar historial');
            }
        });
    }




    // ==========================================
    // ⭐⭐⭐ IMPRIMIR TRAZABILIDAD EN PDF ⭐⭐⭐
    // ==========================================
    $('#btnImprimirTrazabilidad').on('click', function() {
        if (!currentBienIdForTrazabilidad) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'No hay un bien seleccionado'
            });
            return;
        }

        const filtro = $('#filtroTrazabilidad').val();

        // Mostrar loading
        Swal.fire({
            title: 'Generando PDF...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-danger"></i><br><small>Esto puede tardar unos segundos</small>',
            showConfirmButton: false,
            allowOutsideClick: false
        });

        // Generar URL con filtro
        const url = `/movimiento/pdf-trazabilidad/${currentBienIdForTrazabilidad}?filtro=${filtro}`;

        // Abrir en nueva pestaña (el navegador lo descargará automáticamente)
        window.open(url, '_blank');

        // Cerrar el loading después de 1 segundo
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: '¡PDF Generado!',
                text: 'El documento se está descargando',
                timer: 2000,
                showConfirmButton: false
            });
        }, 1000);
    });


    // ==========================================
    // EDITAR MOVIMIENTO
    // ==========================================
    $(document).on('dblclick', '.fila-movimiento', function() {
        const id = $(this).data('id');

        $.ajax({
            url: `/movimiento/${id}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;

                    $('#edit_id').val(data.id_movimiento);
                    $('#edit-id-display').text(data.id_movimiento);
                    $('#edit_idbien').val(data.idbien);
                    $('#edit_tipo_mvto').val(data.tipo_mvto);
                    $('#edit_fecha_mvto').val(typeof moment !== 'undefined' ? moment(data.fecha_mvto).format('YYYY-MM-DD') : data.fecha_mvto.split(' ')[0]);
                    $('#edit_idubicacion').val(data.idubicacion || '');
                    $('#edit_id_estado_conservacion_bien').val(data.id_estado_conservacion_bien || '');
                    $('#edit_detalle_tecnico').val(data.detalle_tecnico || '');
                    $('#edit_documento_sustentatorio').val(data.documento_sustentatorio || '');
                    $('#edit_NumDocto').val(data.NumDocto || '');

                    $('.text-danger').text('');

                    $('#modalEdit').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el movimiento'
                });
            }
        });
    });

    $('#formEdit').submit(function(e) {
        e.preventDefault();

        const id = $('#edit_id').val();
        const formData = $(this).serialize();

        $('#btnActualizar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
        $('.text-danger').text('');

        $.ajax({
            url: `/movimiento/${id}`,
            method: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#modalEdit').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: response.message,
                        timer: 2000
                    });

                    cargarMovimientos();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $(`.error-edit-${key}`).text(value[0]);
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: 'Revise los campos marcados'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al actualizar'
                    });
                }
            },
            complete: function() {
                $('#btnActualizar').prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar');
            }
        });
    });

    // ==========================================
    // PAGINACIÓN
    // ==========================================
    $(document).on('click', '.paginar-inicial', function(e) {
        e.preventDefault();
        paginaActual = $(this).data('page');
        cargarMovimientos();
    });


    // ==========================================
    // ⭐ ACTUALIZAR CARDS DE ESTADÍSTICAS (NUEVA)
    // ==========================================
    function actualizarCards(estadisticas) {
        if (!estadisticas) return;

        // Animación al cambiar números
        const animarCambio = (selector, valorNuevo) => {
            const $elemento = $(selector);
            const valorActual = parseInt($elemento.text()) || 0;

            if (valorActual !== valorNuevo) {
                $elemento.addClass('card-updating');

                setTimeout(() => {
                    $elemento.text(valorNuevo);
                    $elemento.removeClass('card-updating');
                }, 150);
            }
        };

        // Actualizar cada card con animación
        animarCambio('#cardTotalBienes', estadisticas.totalBienes);
        animarCambio('#cardBienesAsignados', estadisticas.bienesAsignados);
        animarCambio('#cardBienesRegistro', estadisticas.bienesRegistro);
        animarCambio('#cardBienesBaja', estadisticas.bienesBaja);

        console.log('✅ Cards actualizados:', estadisticas);
    }







    // ==========================================
    // ⭐ CARGAR MOVIMIENTOS (CORREGIDO CON CARDS DINÁMICOS + ÁREA)
    // ==========================================
    function cargarMovimientos() {
        // ✅ OBTENER VALOR DEL FILTRO DE TIPO (sin conversión especial)
        let filtroTipoValor = $('#filtroTipo').val();

        // ✅ PARÁMETROS PARA ENVIAR AL BACKEND
        const params = {
            search: busquedaActual,
            orden: ordenActual,
            direccion: direccionActual,
            page: paginaActual,
            tipo_mvto: filtroTipoValor,
            estado_bien: $('#filtroEstadoBien').val(),
            area: $('#filtroArea').val(),
            ubicacion: $('#filtroUbicacion').val(),
            fecha_desde: $('#filtroFechaDesde').val(),
            fecha_hasta: $('#filtroFechaHasta').val(),
            mostrar_anulados: $('#filtroAnulados').val() || '0'  // ⭐ NUEVO
        };


        $.ajax({
            url: '{{ route("movimiento.index") }}',
            method: 'GET',
            data: params,
            success: function(response) {
                if (response.success) {
                    // 1️⃣ RENDERIZAR TABLA
                    renderizarMovimientos(response.data);
                    actualizarPaginacion(response);

                    // 2️⃣ ⭐⭐⭐ ACTUALIZAR CARDS DINÁMICAMENTE ⭐⭐⭐
                    if (response.estadisticas) {
                        actualizarCards(response.estadisticas);
                    }

                    // 3️⃣ MANEJO DE UI
                    $('#loadingSearch').hide();
                    $('#infoResultados').show();

                    if (response.data.length === 0) {
                        $('#noResultados').show();
                        $('#terminoBuscado').text(busquedaActual);
                        $('#paginacionContainer').hide();
                    } else {
                        $('#noResultados').hide();
                        $('#paginacionContainer').show();
                    }

                    // 4️⃣ PROTEGER TÍTULOS DESPUÉS DE CARGAR
                    setTimeout(function() {
                        protegerTitulosCards();
                        console.log('✅ Títulos protegidos después de cargar movimientos');
                    }, 150);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar movimientos:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar los movimientos'
                });
            }
        });
    }





    function renderizarMovimientos(movimientos) {
    const tbody = $('#tablaMovimientos');
    tbody.empty();

    if (!movimientos || movimientos.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="11" class="text-center text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>No hay movimientos registrados</p>
                </td>
            </tr>
        `);
        return;
    }

    movimientos.forEach(function(mov) {
        const tipoNormalizado = mov.tipo_movimiento.tipo_mvto.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, '-');

        const badgeClass = `badge-tipo-${tipoNormalizado}`;
        const fecha = typeof moment !== 'undefined' ? moment(mov.fecha_mvto).format('DD/MM/YYYY') : mov.fecha_mvto.split(' ')[0];

        let estadoBadge = 'badge-secondary';
        if (mov.estado_conservacion) {
            const estado = mov.estado_conservacion.nombre_estado.toUpperCase();
            if (estado.includes('BUENO') || estado.includes('EXCELENTE')) {
                estadoBadge = 'badge-success';
            } else if (estado.includes('REGULAR')) {
                estadoBadge = 'badge-warning';
            } else if (estado.includes('MALO') || estado.includes('DETERIORADO')) {
                estadoBadge = 'badge-danger';
            }
        }

        const denominacion = mov.bien.denominacion_bien || '';
        const denominacionCorta = denominacion.length > 30 ? denominacion.substring(0, 30) + '...' : denominacion;
        const tipoNombre = mov.bien.tipo_bien ? mov.bien.tipo_bien.nombre_tipo : '';
        const ubicacionNombre = mov.ubicacion ? mov.ubicacion.nombre_sede : '';

        // ✅ OBTENER ÁREA DE LA UBICACIÓN
        const areaNombre = (mov.ubicacion && mov.ubicacion.area) ? mov.ubicacion.area.nombre_area : '-';

        // ⭐⭐⭐ BADGE ESTADO MOVIMIENTO (VIGENTE/ANULADO) ⭐⭐⭐
        const badgeEstadoMovimiento = mov.anulado ? `
            <span class="badge badge-anulado" title="Anulado el ${mov.fecha_anulacion || 'N/A'} por ${mov.usuario_anulo ? mov.usuario_anulo.name : 'N/A'}">
                <i class="fas fa-ban"></i> ANULADO
            </span>
        ` : `
            <span class="badge badge-success">
                <i class="fas fa-check-circle"></i> VIGENTE
            </span>
        `;

        // ⭐ APLICAR CLASE CSS ESPECIAL SI ESTÁ ANULADO
        const claseAnulado = mov.anulado ? 'tipo-anulado' : '';

        const row = `
            <tr id="row-${mov.id_movimiento}" 
                class="fila-movimiento tipo-${tipoNormalizado} ${claseAnulado}" 
                data-id="${mov.id_movimiento}">
                
                <td class="text-center">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input checkbox-item"
                            id="check-${mov.id_movimiento}"
                            value="${mov.id_movimiento}"
                            data-bien-id="${mov.idbien}"
                            ${mov.anulado ? 'disabled' : ''}>
                        <label class="custom-control-label" for="check-${mov.id_movimiento}"></label>
                    </div>
                </td>
                
                <td class="text-center"><strong>${mov.id_movimiento}</strong></td>
                <td><strong>${fecha}</strong></td>
                <td><span class="badge badge-info">${mov.bien.codigo_patrimonial}</span></td>
                
                <td>
                    <strong>${denominacionCorta}</strong><br>
                    <small class="text-muted">${tipoNombre}</small>
                </td>
                
                <td><span class="badge ${badgeClass}">${mov.tipo_movimiento.tipo_mvto}</span></td>
                <td><small class="text-muted"><i class="fas fa-building"></i> ${areaNombre}</small></td>
                
                <td>
                    ${ubicacionNombre ? `<small class="text-muted"><i class="fas fa-map-marker-alt"></i> ${ubicacionNombre}</small>` : '<span class="text-muted">-</span>'}
                </td>
                
                <td>
                    ${mov.estado_conservacion ? `<span class="badge ${estadoBadge}">${mov.estado_conservacion.nombre_estado}</span>` : '<span class="text-muted">-</span>'}
                </td>
                
                <td class="text-center">${badgeEstadoMovimiento}</td>
                
                <!-- ⭐⭐⭐ SOLO BOTÓN VER (OJO) ⭐⭐⭐ -->
                <td class="text-center">
                    <button type="button" class="btn btn-info btn-sm btn-ver" 
                            title="Ver Detalles" 
                            data-id="${mov.id_movimiento}">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;

        tbody.append(row);
    });

    }


        function actualizarPaginacion(response) {
        $('#from').text(response.from || 0);
        $('#to').text(response.to || 0);
        $('#resultadosCount').text(response.resultados);
        $('#totalCount').text(response.total);
        $('#paginaInfo').text(`${response.from || 0} - ${response.to || 0}`);

        const linksContainer = $('#paginacionLinks');
        linksContainer.empty();

        if (response.last_page > 1) {
            let paginationHTML = '<ul class="pagination pagination-sm m-0">';

            if (response.current_page === 1) {
                paginationHTML += `
                    <li class="page-item disabled">
                        <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                    </li>
                `;
            } else {
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link paginar-inicial" href="#" data-page="${response.current_page - 1}">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                `;
            }

            for (let i = 1; i <= response.last_page; i++) {
                if (i === response.current_page) {
                    paginationHTML += `
                        <li class="page-item active">
                            <span class="page-link">${i}</span>
                        </li>
                    `;
                } else if (i === 1 || i === response.last_page || Math.abs(i - response.current_page) <= 2) {
                    paginationHTML += `
                        <li class="page-item">
                            <a class="page-link paginar-inicial" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                }
            }

            if (response.current_page === response.last_page) {
                paginationHTML += `
                    <li class="page-item disabled">
                        <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                    </li>
                `;
            } else {
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link paginar-inicial" href="#" data-page="${response.current_page + 1}">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                `;
            }

            paginationHTML += '</ul>';
            linksContainer.html(paginationHTML);
        }
    }

    // ==========================================
    // ⭐ VERIFICAR FILTROS ACTIVOS (CON ÁREA)
    // ==========================================
    function verificarFiltrosActivos() {
        const tipoSeleccionado = $('#filtroTipo').val();
        const estadoBien = $('#filtroEstadoBien').val();
        const areaSeleccionada = $('#filtroArea').val();  // ⭐ NUEVO
        const ubicacionSeleccionada = $('#filtroUbicacion').val();
        const fechaDesde = $('#filtroFechaDesde').val();
        const fechaHasta = $('#filtroFechaHasta').val();

        let filtrosTexto = [];

        // ✅ FILTRO DE TIPO DE MOVIMIENTO
        if (tipoSeleccionado && tipoSeleccionado !== '' && tipoSeleccionado !== 'activos') {
            // Solo mostrar si NO es el filtro por defecto
            const textoTipo = $('#filtroTipo option:selected').text()
                .replace(/📋|📂|✅|📝|❌/g, '') // Quitar emojis
                .trim();
            filtrosTexto.push(`Tipo: ${textoTipo}`);
        }

        // ✅ FILTRO DE ESTADO DEL BIEN
        if (estadoBien && estadoBien !== '1' && estadoBien !== '') {
            const textoEstado = $('#filtroEstadoBien option:selected').text();
            filtrosTexto.push(`Estado: ${textoEstado}`);
        }

        // ⭐⭐⭐ FILTRO DE ÁREA (NUEVO) ⭐⭐⭐
        if (areaSeleccionada && areaSeleccionada !== '') {
            const textoArea = $('#filtroArea option:selected').text();
            filtrosTexto.push(`Área: ${textoArea}`);
        }

        // ✅ FILTRO DE UBICACIÓN
        if (ubicacionSeleccionada && ubicacionSeleccionada !== '' && ubicacionSeleccionada !== 'todas') {
            const textoUbicacion = $('#filtroUbicacion option:selected').text();
            filtrosTexto.push(`Ubicación: ${textoUbicacion}`);
        }

        // ✅ FILTRO DE RANGO DE FECHAS
        if (fechaDesde && fechaHasta) {
            filtrosTexto.push(`Período: ${fechaDesde} al ${fechaHasta}`);
        } else if (fechaDesde) {
            filtrosTexto.push(`Desde: ${fechaDesde}`);
        } else if (fechaHasta) {
            filtrosTexto.push(`Hasta: ${fechaHasta}`);
        }

        // ✅ MOSTRAR/OCULTAR INDICADOR DE FILTROS
        if (filtrosTexto.length > 0) {
            $('#filtrosActivosTexto').html(filtrosTexto.join(' <span class="text-muted">|</span> '));
            $('#filtrosActivos').fadeIn(200);
        } else {
            $('#filtrosActivos').fadeOut(200, function() {
                $('#filtrosActivosTexto').empty();
            });
        }
    }

    // ✅ DETECTAR CAMBIOS EN FILTROS
        $('#filtroTipo, #filtroEstadoBien, #filtroArea, #filtroUbicacion, #filtroFechaDesde, #filtroFechaHasta').on('change', function() {
        verificarFiltrosActivos();
    });


    // ✅ APLICAR FILTROS CON VALIDACIÓN
    $('#btnAplicarFiltros').click(function() {
        const fechaDesde = $('#filtroFechaDesde').val();
        const fechaHasta = $('#filtroFechaHasta').val();

        // 1. VALIDAR RANGO DE FECHAS
        if (fechaDesde && fechaHasta && fechaDesde > fechaHasta) {
            Swal.fire({
                icon: 'warning',
                title: 'Rango de fechas inválido',
                text: 'La fecha "Desde" debe ser menor o igual a la fecha "Hasta"',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#f39c12'
            });
            return;
        }

        // 2. ACTUALIZAR INDICADOR
        verificarFiltrosActivos();

        // 3. RESETEAR Y RECARGAR
        paginaActual = 1;
        cargarMovimientos();

        // 4. FEEDBACK VISUAL
        const btnTexto = $(this).html();
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ');

        setTimeout(() => {
            $(this).prop('disabled', false).html(btnTexto);
        }, 500);
    });

    // ✅ LIMPIAR FILTROS (CORREGIDO)
    $('#btnLimpiarFiltros').click(function() {
        // 1. RESTAURAR VALORES POR DEFECTO
        $('#filtroTipo').val('activos'); // ← Volver a "Movimientos activos"
        $('#filtroEstadoBien').val('1');
        $('#filtroArea').val('');  // ⭐ NUEVO
        $('#filtroUbicacion').val('');
        $('#filtroFechaDesde').val('');
        $('#filtroFechaHasta').val('');

        // 2. OCULTAR BADGE INMEDIATAMENTE
        $('#filtrosActivos').hide();
        $('#filtrosActivosTexto').empty();

        // 3. VERIFICAR ESTADO
        verificarFiltrosActivos();

        // 4. RECARGAR TABLA
        paginaActual = 1;
        cargarMovimientos();

        // 5. NOTIFICACIÓN
        Swal.fire({
            icon: 'info',
            title: 'Filtros restaurados',
            text: 'Mostrando movimientos activos (Registro y Asignación)',
            timer: 1500,
            showConfirmButton: false
        });
    });


    // ==========================================
    // 🚀 INICIALIZACIÓN FINAL
    // ==========================================

    // 1. ⭐ OCULTAR BADGE DE FILTROS AL CARGAR LA PÁGINA
    $('#filtrosActivos').hide();
    $('#filtrosActivosTexto').empty();
    // ✅ CORRECTO: No muestra badge al inicio (filtro default no necesita badge)

    // 2. ⭐ VERIFICAR ESTADO INICIAL DE FILTROS
    verificarFiltrosActivos();
    // ✅ CORRECTO: Verifica si hay filtros activos (no debería haber ninguno al inicio)

    // 3. ⭐ CARGAR MOVIMIENTOS CON FILTROS DEFAULT (solo activos)
    cargarMovimientos();
    // ✅ CORRECTO: Carga movimientos con el filtro por defecto (activos)

    // 4. ⭐ PROTEGER TÍTULOS DE LAS CARDS (failsafe inicial)
    setTimeout(function() {
        protegerTitulosCards();
        console.log('✅ Sistema de movimientos inicializado correctamente');
    }, 150);
    // ✅ CORRECTO: Protección contra modificaciones accidentales






}); // ✅ CIERRE ÚNICO DE $(document).ready()

</script>
@stop

