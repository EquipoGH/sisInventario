@extends('layouts.main')

@section('title', 'Gestión de Movimientos')

@section('content_header')
    <h1>Gestión de Movimientos</h1>
@stop

@section('css')
{{-- ⭐⭐⭐ COLORES POR TIPO DE MOVIMIENTO (SIN ACTUALIZACIÓN) ⭐⭐⭐ --}}
<style>
/* ==========================================
   COLORES DE FONDO POR TIPO DE MOVIMIENTO
   ========================================== */

/* 🔵 REGISTRO - Celeste claro */
.tipo-registro {
    background-color: #e3f2fd !important;
}

.tipo-registro:hover {
    background-color: #bbdefb !important;
}

/* 🟢 ASIGNACIÓN - Verde claro */
.tipo-asignacion {
    background-color: #e8f5e9 !important;
}

.tipo-asignacion:hover {
    background-color: #c8e6c9 !important;
}

/* 🔴 BAJA - Rojo claro */
.tipo-baja {
    background-color: #ffebee !important;
}

.tipo-baja:hover {
    background-color: #ffcdd2 !important;
}

/* ==========================================
   ⭐ LEYENDA PROFESIONAL MINIMALISTA
   ========================================== */
.leyenda-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-left: 4px solid #007bff;
    border-radius: 8px;
    padding: 15px 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.leyenda-card h6 {
    color: #495057;
    font-weight: 600;
    margin: 0 0 12px 0;
    font-size: 0.9rem;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}

.leyenda-items {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
}

.leyenda-item {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.95);
    padding: 8px 15px;
    border-radius: 20px;
    border: 1px solid #e0e0e0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.leyenda-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.12);
    border-color: #007bff;
}

.leyenda-color {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 2px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.leyenda-item span {
    font-weight: 600;
    font-size: 0.85rem;
    color: #495057;
}

.color-registro {
    background-color: #e3f2fd;
}

.color-asignacion {
    background-color: #e8f5e9;
}

.color-baja {
    background-color: #ffebee;
}

/* ==========================================
   ⭐ BADGES DE TIPO CON COLOR
   ========================================== */
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

/* ==========================================
   ESTILOS GENERALES
   ========================================== */
.fila-movimiento {
    cursor: pointer;
    transition: all 0.2s ease;
}

.fila-movimiento:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transform: translateY(-1px);
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

/* CARD DE FILTROS */
.card-filters {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%);
}

.card-filters .card-body {
    padding: 1.25rem;
}

.filter-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.35rem;
    display: block;
}

.filter-label i {
    color: #007bff;
    font-size: 0.8rem;
}

.card-filters .form-control-sm {
    border-radius: 6px;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.card-filters .form-control-sm:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.15rem rgba(0,123,255,0.15);
}

.card-filters .btn-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
}

.card-filters .btn-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
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

/* RESPONSIVE */
@media (max-width: 768px) {
    .btn-action span:not(.badge) {
        display: none !important;
    }

    .btn-action {
        padding: 0.5rem 0.75rem;
    }

    .filter-label {
        font-size: 0.8rem;
    }
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
   ⭐ FILTROS AVANZADOS - DISEÑO PROFESIONAL
   ========================================== */

/* CARD DE FILTROS */
.card-filters {
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.card-filters:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
}

.card-filters .card-body {
    padding: 1.5rem;
}

/* GRUPO DE FILTROS */
.filter-group {
    position: relative;
    height: 100%;
}

/* LABELS DE FILTROS */
.filter-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-label i {
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

/* Estilo del ícono del calendario */
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

/* BOTÓN LIMPIAR FILTROS */
#btnLimpiarFiltros {
    border-radius: 8px;
    padding: 0.45rem 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

#btnLimpiarFiltros:hover {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
}

/* TIP TEXT */
.tip-text {
    font-size: 0.8rem;
    color: #858796;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.tip-text i {
    color: #4e73df;
    font-size: 0.85rem;
}

.tip-text strong {
    color: #5a5c69;
}

/* ESPACIADO RESPONSIVE */
.row.g-3 {
    --bs-gutter-x: 1rem;
    --bs-gutter-y: 1rem;
}

.row.g-3 > * {
    padding-right: calc(var(--bs-gutter-x) * 0.5);
    padding-left: calc(var(--bs-gutter-x) * 0.5);
    margin-bottom: var(--bs-gutter-y);
}

/* ANIMACIÓN AL CARGAR */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card-filters {
    animation: slideInUp 0.4s ease-out;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .card-filters .card-body {
        padding: 1rem;
    }

    .filter-label {
        font-size: 0.75rem;
        margin-bottom: 0.4rem;
    }

    .btn-apply-filters {
        font-size: 0.7rem;
        padding: 0.5rem 0.75rem;
    }

    .tip-text {
        font-size: 0.75rem;
        text-align: center;
        display: block;
        margin-top: 0.5rem;
    }
}

/* EFECTOS HOVER EN INPUTS */
.custom-select-filter:not(:disabled):not(.disabled):active,
.custom-date-filter:not(:disabled):not(.disabled):active {
    border-color: #4e73df;
}

/* PLACEHOLDER STYLING */
.custom-date-filter::placeholder {
    color: #a0a0a0;
    font-style: italic;
}



/* ==========================================
   ⭐ CARDS DE ESTADÍSTICAS
   ========================================== */
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-danger {
    border-left: 4px solid #e74a3b !important;
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15) !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.text-xs {
    font-size: 0.7rem;
}

.no-gutters {
    margin-right: 0;
    margin-left: 0;
}

.no-gutters > .col,
.no-gutters > [class*="col-"] {
    padding-right: 0;
    padding-left: 0;
}


</style>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        {{-- ⭐⭐⭐ LEYENDA PROFESIONAL (SIN ACTUALIZACIÓN) ⭐⭐⭐ --}}
        <div class="leyenda-card">
            <h6 class="text-center">CÓDIGO DE COLORES POR TIPO DE MOVIMIENTO</h6>
            <div class="leyenda-items">
                <div class="leyenda-item">
                    <div class="leyenda-color color-registro"></div>
                    <span>Registro</span>
                </div>

                <div class="leyenda-item">
                    <div class="leyenda-color color-asignacion"></div>
                    <span>Asignación</span>
                </div>

                <div class="leyenda-item">
                    <div class="leyenda-color color-baja"></div>
                    <span>Baja</span>
                </div>
            </div>
        </div>


        {{-- ⭐⭐⭐ CARDS DE ESTADÍSTICAS (ACTUALIZABLES EN TIEMPO REAL) ⭐⭐⭐ --}}
        <div class="row mb-4">
            {{-- Card 1: Total de Bienes --}}
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total de Bienes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span id="cardTotalBienes">{{ $totalBienes ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-box fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Bienes Asignados --}}
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Bienes Asignados
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span id="cardBienesAsignados">{{ $bienesAsignados ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Bienes en Registro --}}
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Bienes en Registro
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span id="cardBienesRegistro">{{ $bienesRegistro ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 4: Bienes de Baja --}}
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Bienes de Baja
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span id="cardBienesBaja">{{ $bienesBaja ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        {{-- ==========================================
            ⭐ BARRA DE ACCIONES Y BÚSQUEDA MEJORADA
            ========================================== --}}
        <div class="row mb-4">
            {{-- COLUMNA IZQUIERDA: BOTONES DE ACCIÓN --}}
            <div class="col-lg-6 col-md-12 mb-3">
                <div class="btn-toolbar" role="toolbar">
                    {{-- Botón Nuevo Movimiento --}}
                    <div class="btn-group mr-2" role="group">
                        <button type="button" class="btn btn-primary btn-action" data-toggle="modal" data-target="#modalCreate">
                            <i class="fas fa-plus-circle"></i>
                            <span class="d-none d-sm-inline">Nuevo Movimiento</span>
                        </button>
                    </div>

                    {{-- Botones de Acción Masiva --}}
                    <div class="btn-group mr-2" role="group" id="accionesMasivas" style="display:none;">
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

                        <!-- ⭐ AGREGAR ESTE BOTÓN NUEVO -->
                        <button type="button" class="btn btn-info btn-action" id="btnRevertirBajaSeleccionados">
                            <i class="fas fa-undo-alt"></i>
                            <span class="d-none d-sm-inline">Revertir Baja</span>
                            <span class="badge badge-light ml-1" id="contadorRevertir">0</span>
                        </button>

                        <button type="button" class="btn btn-danger btn-action" id="btnEliminarSeleccionados">
                            <i class="fas fa-trash-alt"></i>
                            <span class="d-none d-sm-inline">Eliminar</span>
                            <span class="badge badge-light ml-1" id="contadorSeleccionados">0</span>
                        </button>
                    </div>

                </div>
            </div>

            {{-- COLUMNA DERECHA: BÚSQUEDA --}}
            <div class="col-lg-6 col-md-12">
                <div class="search-container">
                    <div class="input-group input-group-search">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <input type="text"
                            id="searchInput"
                            class="form-control form-control-search"
                            placeholder="Buscar por código, denominación, tipo..."
                            autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnLimpiar" title="Limpiar búsqueda">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-1 text-right">
                        <span id="infoResultados">
                            <i class="fas fa-database mr-1"></i>
                            Mostrando <strong id="from">{{ $movimientos->firstItem() ?? 0 }}</strong>
                            a <strong id="to">{{ $movimientos->lastItem() ?? 0 }}</strong>
                            de <strong id="resultadosCount">{{ $movimientos->total() }}</strong>
                            (<strong id="totalCount">{{ $total }}</strong> total)
                        </span>
                        <span id="loadingSearch" style="display:none;">
                            <i class="fas fa-spinner fa-spin text-primary mr-1"></i>
                            <span class="text-primary">Buscando...</span>
                        </span>
                    </small>
                </div>
            </div>
        </div>

       {{-- ==========================================
     ⭐ FILTROS AVANZADOS (SIN FILTRO DE BIEN)
     ========================================== --}}
<div class="card card-filters shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            {{-- COLUMNA 1: Tipo de Movimiento (33%) --}}
            <div class="col-lg-4 col-md-6">
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-filter text-primary"></i>
                        Tipo de Movimiento
                    </label>
                    <select id="filtroTipo" class="form-control form-control-sm custom-select-filter">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposMovimiento as $tipo)
                            <option value="{{ $tipo->id_tipo_mvto }}">{{ $tipo->tipo_mvto }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- COLUMNA 2: Fecha Desde (25%) --}}
            <div class="col-lg-3 col-md-4">
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-calendar-alt text-success"></i>
                        Desde
                    </label>
                    <input type="date"
                           id="filtroFechaDesde"
                           class="form-control form-control-sm custom-date-filter"
                           placeholder="dd/mm/aaaa">
                </div>
            </div>

            {{-- COLUMNA 3: Fecha Hasta (25%) --}}
            <div class="col-lg-3 col-md-4">
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-calendar-alt text-success"></i>
                        Hasta
                    </label>
                    <input type="date"
                           id="filtroFechaHasta"
                           class="form-control form-control-sm custom-date-filter"
                           placeholder="dd/mm/aaaa">
                </div>
            </div>

            {{-- COLUMNA 4: Botón Aplicar (17%) --}}
            <div class="col-lg-2 col-md-4">
                <div class="filter-group d-flex align-items-end h-100">
                    <button type="button"
                            id="btnAplicarFiltros"
                            class="btn btn-apply-filters btn-sm w-100">
                        <i class="fas fa-search"></i>
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>

        {{-- FILA 2: Tip y Botón Limpiar --}}
        <div class="row mt-3">
            <div class="col-md-6">
                <button type="button"
                        id="btnLimpiarFiltros"
                        class="btn btn-outline-secondary btn-sm"
                        style="display: none;">
                    <i class="fas fa-eraser"></i>
                    Limpiar Filtros
                </button>
            </div>
            <div class="col-md-6 text-right">
                <small class="text-muted tip-text">
                    <i class="fas fa-info-circle"></i>
                    <strong>Tip:</strong> Doble clic en una fila para editar
                </small>
            </div>
        </div>
    </div>
</div>




        <div class="text-right">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> Doble click en la fila para editar
            </small>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th width="4%">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="checkAll">
                                <label class="custom-control-label" for="checkAll"></label>
                            </div>
                        </th>
                        <th width="6%" class="sortable" data-column="id" style="cursor:pointer;">
                            ID <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="12%" class="sortable" data-column="fecha" style="cursor:pointer;">
                            FECHA/HORA <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="10%" class="sortable" data-column="responsable" style="cursor:pointer;">
                            RESPONSABLE <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="12%">CÓDIGO BIEN</th>
                        <th width="20%">DENOMINACIÓN</th>
                        <th width="11%" class="sortable" data-column="tipo" style="cursor:pointer;">
                            TIPO MVTO <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="13%">UBICACIÓN</th>
                        <th width="8%">ESTADO</th>
                        <th width="4%">ACCION</th>
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
     MODAL CREAR MOVIMIENTO
     ========================================== --}}
<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Nuevo Movimiento
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCreate">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="idbien">Bien <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="idbien" name="idbien" required>
                                    <option value="">Seleccione un bien</option>
                                    @foreach($bienes as $bien)
                                        <option value="{{ $bien->id_bien }}">
                                            {{ $bien->codigo_patrimonial }} - {{ $bien->denominacion_bien }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-idbien d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_mvto">Tipo de Movimiento <span class="text-danger">*</span></label>
                                <select class="form-control" id="tipo_mvto" name="tipo_mvto" required>
                                    <option value="">Seleccione tipo</option>
                                    @foreach($tiposMovimiento as $tipo)
                                        <option value="{{ $tipo->id_tipo_mvto }}">{{ $tipo->tipo_mvto }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-tipo_mvto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_mvto">Fecha de Movimiento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_mvto" name="fecha_mvto" required>
                                <span class="text-danger error-fecha_mvto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="idubicacion">Ubicación</label>
                                <select class="form-control" id="idubicacion" name="idubicacion">
                                    <option value="">Sin ubicación</option>
                                    @foreach($ubicaciones as $ubicacion)
                                        <option value="{{ $ubicacion->id_ubicacion }}">
                                            {{ $ubicacion->ubicacion_completa }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-idubicacion d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="id_estado_conservacion_bien">Estado de Conservación</label>
                                <select class="form-control" id="id_estado_conservacion_bien" name="id_estado_conservacion_bien">
                                    <option value="">Sin estado</option>
                                    @foreach($estadosConservacion as $estado)
                                        <option value="{{ $estado->id_estado }}">{{ $estado->nombre_estado }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-id_estado_conservacion_bien d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="documento_sustentatorio">
                                    <i class="fas fa-file-invoice"></i> Documento Sustento (Opcional)
                                </label>
                                <select class="form-control" id="documento_sustentatorio" name="documento_sustentatorio">
                                    <option value="">Sin documento</option>
                                    @foreach($documentos as $doc)
                                        <option value="{{ $doc->id_documento }}">
                                            {{ $doc->tipo_documento }} - {{ $doc->numero_documento }} ({{ $doc->fecha_formateada }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Selecciona un documento sustento existente</small>
                                <span class="text-danger error-documento_sustentatorio d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="NumDocto">
                                    <i class="fas fa-hashtag"></i> Número de Documento (Opcional)
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="NumDocto"
                                    name="NumDocto"
                                    placeholder="Ej: DOC-2026-001"
                                    maxlength="20">
                                <small class="text-muted">Número de documento en texto libre</small>
                                <span class="text-danger error-NumDocto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="detalle_tecnico">Detalle Técnico</label>
                                <textarea class="form-control" id="detalle_tecnico" name="detalle_tecnico" rows="2" maxlength="500" placeholder="Descripción técnica del movimiento..."></textarea>
                                <small class="text-muted">Máximo 500 caracteres</small>
                                <span class="text-danger error-detalle_tecnico d-block mt-1"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="btnGuardar">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
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

                                        <dt class="col-sm-5">Responsable:</dt>
                                        <dd class="col-sm-7" id="ver-usuario">-</dd>

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
                                        <option value="todos">Todos los movimientos</option>
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
                                                <th width="10%">ID</th>
                                                <th width="15%">Fecha/Hora</th>
                                                <th width="15%">Tipo</th>
                                                <th width="20%">Usuario</th>
                                                <th width="20%">Ubicación</th>
                                                <th width="10%">Estado</th>
                                                <th width="10%">Documento</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaTrazabilidad">
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
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
                        <br><small>Los bienes quedarán sin ubicación ni estado de conservación.</small>
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
     ⭐ MODAL REVERTIR BAJA MASIVA
     ========================================== --}}
<div class="modal fade" id="modalRevertirBaja" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-undo-alt"></i> Revertir Baja de Bienes
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formRevertirBaja">
                @csrf
                <input type="hidden" id="revertir_bienes_ids" name="bienes_ids">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Se revertirá la baja de <strong id="cantidadRevertir">0</strong> bien(es) seleccionados.
                        <br><small>Los bienes volverán al estado <strong>"Registro"</strong> sin ubicación asignada.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="revertir_fecha_mvto">Fecha de Reversión <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="revertir_fecha_mvto" name="fecha_mvto" required>
                                <span class="text-danger error-revertir-fecha_mvto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="revertir_documento_sustentatorio">Documento Sustento</label>
                                <select class="form-control" id="revertir_documento_sustentatorio" name="documento_sustentatorio">
                                    <option value="">Sin documento</option>
                                    @foreach($documentos as $doc)
                                        <option value="{{ $doc->id_documento }}">
                                            {{ $doc->tipo_documento }} - {{ $doc->numero_documento }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-revertir-documento_sustentatorio d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="revertir_NumDocto">Número de Documento</label>
                                <input type="text" class="form-control" id="revertir_NumDocto" name="NumDocto" maxlength="20" placeholder="Ej: REV-BAJA-2026-001">
                                <span class="text-danger error-revertir-NumDocto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="revertir_detalle_tecnico">Motivo de Reversión <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="revertir_detalle_tecnico" name="detalle_tecnico" rows="3" maxlength="200" placeholder="Describa el motivo de la reversión de baja..." required></textarea>
                                <small class="text-muted">Máximo 200 caracteres. Este campo es obligatorio.</small>
                                <span class="text-danger error-revertir-detalle_tecnico d-block mt-1"></span>
                            </div>
                        </div>
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
        let ordenActual = 'fecha';
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

        // 4. BOTÓN ELIMINAR: Siempre visible cuando hay selección
        if (cantidad > 0) {
            $('#btnEliminarSeleccionados').fadeIn(200).removeClass('d-none');
        } else {
            $('#btnEliminarSeleccionados').fadeOut(200).addClass('d-none');
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
        const tipoBien = tipoBadge.text().trim();

        // ✅ TODO CORRECTO - PROCEDER CON LA REVERSIÓN
        $('#cantidadRevertir').text('1');
        $('#revertir_bienes_ids').val(JSON.stringify(bienesSeleccionados));
        $('#revertir_fecha_mvto').val(new Date().toISOString().split('T')[0]);
        $('#revertir_detalle_tecnico').val('');
        $('#revertir_documento_sustentatorio').val('');
        $('#revertir_NumDocto').val('');

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

    function enviarRevertirBaja() {
    // ⭐ VALIDAR UNA VEZ MÁS (por seguridad)
        if (bienesSeleccionados.length !== 1) {
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'Debe seleccionar exactamente UN bien'
            });
            return;
        }

        const bienId = bienesSeleccionados[0]; // Extraer el ID del único bien

        // ⭐⭐⭐ PREPARAR DATOS DEL FORMULARIO (NOMBRES CORREGIDOS) ⭐⭐⭐
        const formData = {
            detalle_tecnico: $('#revertir_detalle_tecnico').val().trim(),  // ✅ CAMBIO 1: detalle_motivo → detalle_tecnico
            fechamvto: $('#revertir_fecha_mvto').val(),                     // ✅ CAMBIO 2: fecha_mvto → fechamvto
            documentosustentatorio: $('#revertir_documento_sustentatorio').val() || null,  // ✅ CAMBIO 3: sin guiones bajos
            NumDocto: $('#revertir_NumDocto').val() || null
        };

        // ⭐ VALIDAR QUE NO EXCEDA 200 CARACTERES (seguridad adicional)
        if (formData.detalle_tecnico.length > 200) {
            Swal.fire({
                icon: 'error',
                title: 'Texto demasiado largo',
                text: 'El motivo no puede exceder los 200 caracteres'
            });
            return;
        }

        // ⭐ VALIDAR QUE NO ESTÉ VACÍO
        if (formData.detalle_tecnico.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo obligatorio',
                text: 'Debe ingresar un motivo de reversión'
            });
            return;
        }

        $('#btnGuardarRevertir').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        $.ajax({
            url: `/movimiento/revertir-baja/${bienId}`,  // ✅ CORRECTO (backticks)
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#modalRevertirBaja').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: '✅ Reversión exitosa!',
                        html: `
                            <p>${response.message}</p>
                            <hr>
                            <small class="text-muted">
                                <i class="fas fa-check-circle text-success"></i>
                                El bien ha sido revertido correctamente
                            </small>
                        `,
                        timer: 4000
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
                        $(`.error-revertir-${key}`).text(value[0]);
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: 'Revise los campos marcados'
                    });
                } else if (xhr.status === 403) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Acceso denegado',
                        text: 'Solo el administrador puede revertir bajas'
                    });
                } else if (xhr.status === 404) {
                    Swal.fire({
                        icon: 'error',
                        title: 'No encontrado',
                        text: xhr.responseJSON?.message || 'Bien no encontrado'
                    });
                } else if (xhr.status === 400) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No se puede revertir',
                        text: xhr.responseJSON?.message || 'Este movimiento no se puede revertir'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al revertir baja'
                    });
                }
            },
            complete: function() {
                $('#btnGuardarRevertir').prop('disabled', false).html('<i class="fas fa-check"></i> Confirmar Reversión');
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
    // BOTÓN ELIMINAR MASIVO
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

        const idsMovimientos = [];
        $('.checkbox-item:checked').each(function() {
            idsMovimientos.push($(this).val());
        });

        Swal.fire({
            title: '¿Eliminar movimientos?',
            text: `Se eliminarán ${idsMovimientos.length} movimiento(s)`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("movimiento.eliminar-masivo") }}',
                    method: 'POST',
                    data: {
                        ids: idsMovimientos
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: response.message,
                                timer: 2000
                            });

                            cargarMovimientos();

                            // ⭐⭐⭐ ACTUALIZAR ESTADÍSTICAS ⭐⭐⭐
                            actualizarEstadisticas();
                            protegerTitulosCards();

                            $('.checkbox-item').prop('checked', false);
                            $('#checkAll').prop('checked', false);
                            bienesSeleccionados = [];
                            actualizarBienesSeleccionados();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Error al eliminar'
                        });
                    }
                });
            }
        });
    });

    // ==========================================
    // CREAR MOVIMIENTO
    // ==========================================
    $('#formCreate').submit(function(e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $('#btnGuardar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        $('.text-danger').text('');

        $.ajax({
            url: '{{ route("movimiento.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#modalCreate').modal('hide');
                    $('#formCreate')[0].reset();

                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
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
                        $(`.error-${key}`).text(value[0]);
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
                        text: xhr.responseJSON?.message || 'Error al guardar'
                    });
                }
            },
            complete: function() {
                $('#btnGuardar').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            }
        });
    });

    // ==========================================
    // ⭐ VER MOVIMIENTO (CON TRAZABILIDAD)
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
                    const movimientos = response.data;
                    const stats = response.estadisticas;

                    $('#trazabilidad-codigo').text(bien.codigo_patrimonial);
                    $('#trazabilidad-denominacion').text(bien.denominacion_bien);

                    $('#tablaTrazabilidad').empty();

                    if (movimientos.length === 0) {
                        $('#tablaTrazabilidad').html(`
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No hay movimientos en este rango de tiempo</p>
                                </td>
                            </tr>
                        `);
                    } else {
                        movimientos.forEach(function(mov) {
                            const fecha = typeof moment !== 'undefined' ? moment(mov.fecha_mvto).format('DD/MM/YYYY') : mov.fecha_mvto;
                            const hora = typeof moment !== 'undefined' ? moment(mov.fecha_mvto).format('HH:mm:ss') : '';
                            const tipo = mov.tipo_movimiento ? mov.tipo_movimiento.tipo_mvto : '-';
                            const usuario = mov.usuario ? mov.usuario.name : '-';
                            const ubicacion = mov.ubicacion ? mov.ubicacion.nombre_sede : '-';
                            const estado = mov.estado_conservacion ? mov.estado_conservacion.nombre_estado : '-';
                            const documento = mov.documento_sustento ?
                                `${mov.documento_sustento.tipo_documento} ${mov.documento_sustento.numero_documento}` : '-';

                            let badgeClass = 'badge-secondary';
                            if (tipo.toLowerCase().includes('registro')) badgeClass = 'badge-primary';
                            else if (tipo.toLowerCase().includes('asignaci')) badgeClass = 'badge-success';
                            else if (tipo.toLowerCase().includes('baja')) badgeClass = 'badge-danger';

                            $('#tablaTrazabilidad').append(`
                                <tr>
                                    <td class="text-center"><strong>${mov.id_movimiento}</strong></td>
                                    <td>
                                        <strong>${fecha}</strong><br>
                                        <small class="text-muted">${hora}</small>
                                    </td>
                                    <td><span class="badge ${badgeClass}">${tipo}</span></td>
                                    <td><i class="fas fa-user"></i> ${usuario}</td>
                                    <td><i class="fas fa-map-marker-alt"></i> ${ubicacion}</td>
                                    <td>${estado}</td>
                                    <td><small>${documento}</small></td>
                                </tr>
                            `);
                        });

                        $('#stat-total').text(stats.total_movimientos);
                        $('#stat-ultimo').text(stats.ultimo_movimiento ?
                            (typeof moment !== 'undefined' ? moment(stats.ultimo_movimiento).format('DD/MM/YYYY HH:mm') : stats.ultimo_movimiento) : '-');

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
    // ⭐ CARGAR MOVIMIENTOS
    // ==========================================
    function cargarMovimientos() {
        const params = {
            search: busquedaActual,
            orden: ordenActual,
            direccion: direccionActual,
            page: paginaActual,
            tipo_mvto: $('#filtroTipo').val(),
            fecha_desde: $('#filtroFechaDesde').val(),
            fecha_hasta: $('#filtroFechaHasta').val()
        };

        $.ajax({
            url: '{{ route("movimiento.index") }}',
            method: 'GET',
            data: params,
            success: function(response) {
                if (response.success) {
                    renderizarMovimientos(response.data);
                    actualizarPaginacion(response);

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

                    // ⭐⭐⭐ PROTEGER TÍTULOS DESPUÉS DE CARGAR ⭐⭐⭐
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
                    <td colspan="10" class="text-center text-muted">
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
            const hora = typeof moment !== 'undefined' ? moment(mov.fecha_mvto).format('HH:mm:ss') : mov.fecha_mvto.split(' ')[1] || '';

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

            const row = `
                <tr id="row-${mov.id_movimiento}" class="fila-movimiento tipo-${tipoNormalizado}" data-id="${mov.id_movimiento}">
                    <td class="text-center">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input checkbox-item"
                                id="check-${mov.id_movimiento}"
                                value="${mov.id_movimiento}"
                                data-bien-id="${mov.idbien}">
                            <label class="custom-control-label" for="check-${mov.id_movimiento}"></label>
                        </div>
                    </td>
                    <td class="text-center"><strong>${mov.id_movimiento}</strong></td>
                    <td>
                        <strong>${fecha}</strong><br>
                        <small class="text-muted">${hora}</small>
                    </td>
                    <td>
                        <span class="responsable-text">
                            <i class="fas fa-user-circle"></i> ${mov.usuario.name}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-info">${mov.bien.codigo_patrimonial}</span>
                    </td>
                    <td>
                        <strong>${denominacionCorta}</strong><br>
                        <small class="text-muted">${tipoNombre}</small>
                    </td>
                    <td>
                        <span class="badge ${badgeClass}">${mov.tipo_movimiento.tipo_mvto}</span>
                    </td>
                    <td>
                        ${ubicacionNombre ? `<small class="text-muted"><i class="fas fa-map-marker-alt"></i> ${ubicacionNombre}</small>` : '<span class="text-muted">-</span>'}
                    </td>
                    <td>
                        ${mov.estado_conservacion ? `<span class="badge ${estadoBadge}">${mov.estado_conservacion.nombre_estado}</span>` : '<span class="text-muted">-</span>'}
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-info btn-ver" title="Ver Detalles" data-id="${mov.id_movimiento}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
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
    // ⭐ FUNCIONALIDAD PARA FILTROS
    // ==========================================

    function verificarFiltrosActivos() {
        const tipoSeleccionado = $('#filtroTipo').val();
        const fechaDesde = $('#filtroFechaDesde').val();
        const fechaHasta = $('#filtroFechaHasta').val();

        if (tipoSeleccionado || fechaDesde || fechaHasta) {
            $('#btnLimpiarFiltros').fadeIn(200);
        } else {
            $('#btnLimpiarFiltros').fadeOut(200);
        }
    }

    // Detectar cambios en filtros
    $('#filtroTipo, #filtroFechaDesde, #filtroFechaHasta').on('change', function() {
        verificarFiltrosActivos();
    });

    // Aplicar filtros con validación de fechas
    $('#btnAplicarFiltros').click(function() {
        const fechaDesde = $('#filtroFechaDesde').val();
        const fechaHasta = $('#filtroFechaHasta').val();

        // Validar rango de fechas
        if (fechaDesde && fechaHasta && fechaDesde > fechaHasta) {
            Swal.fire({
                icon: 'warning',
                title: 'Rango de fechas inválido',
                text: 'La fecha "Desde" debe ser menor o igual a la fecha "Hasta"',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        verificarFiltrosActivos();
        paginaActual = 1;
        cargarMovimientos();
    });

    // Limpiar todos los filtros
    $('#btnLimpiarFiltros').click(function() {
        $('#filtroTipo').val('');
        $('#filtroFechaDesde').val('');
        $('#filtroFechaHasta').val('');

        $(this).fadeOut(200);
        paginaActual = 1;
        cargarMovimientos();

        Swal.fire({
            icon: 'info',
            title: 'Filtros limpiados',
            text: 'Mostrando todos los movimientos',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // ==========================================
    // 🚀 INICIALIZACIÓN FINAL
    // ==========================================
    verificarFiltrosActivos();
    cargarMovimientos(); // ✅ LLAMADA AL FINAL DEL document.ready

}); // ✅ CIERRE ÚNICO DE $(document).ready()

</script>
@stop

