@extends('layouts.main')

@section('title', 'Catálogo del Sistema')

@section('css')
<style>
/* ===== CATÁLOGO: RESET Y BASE ===== */
.catalogo-wrap {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: #f8f9fa;
    min-height: 100vh;
    padding: 0;
}

/* ===== HERO HEADER ===== */
.cat-hero {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 60%, #1e3a5f 100%);
    padding: 52px 48px 44px;
    position: relative;
    overflow: hidden;
}
.cat-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 340px; height: 340px;
    background: radial-gradient(circle, rgba(245,158,11,0.12) 0%, transparent 70%);
    border-radius: 50%;
}
.cat-hero::after {
    content: '';
    position: absolute;
    bottom: -80px; left: 40%;
    width: 260px; height: 260px;
    background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 70%);
    border-radius: 50%;
}
.cat-hero-badge {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(245,158,11,0.15);
    border: 1px solid rgba(245,158,11,0.3);
    color: #f59e0b;
    font-size: 11px; font-weight: 700; letter-spacing: 1.2px;
    text-transform: uppercase;
    padding: 5px 14px; border-radius: 20px;
    margin-bottom: 18px;
}
.cat-hero h1 {
    color: #fff; font-size: 2.4rem; font-weight: 800;
    margin-bottom: 10px; line-height: 1.2;
    letter-spacing: -0.5px;
}
.cat-hero h1 span { color: #f59e0b; }
.cat-hero p {
    color: rgba(255,255,255,0.65); font-size: 1rem;
    margin-bottom: 28px; max-width: 700px; line-height: 1.7;
}
.cat-stats {
    display: flex; gap: 28px; flex-wrap: wrap;
}
.cat-stat {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    padding: 12px 22px;
    text-align: center;
}
.cat-stat-num {
    font-size: 1.7rem; font-weight: 800; color: #f59e0b;
    line-height: 1;
}
.cat-stat-lbl {
    font-size: 0.72rem; color: rgba(255,255,255,0.5);
    text-transform: uppercase; letter-spacing: 0.8px;
    margin-top: 3px;
}

/* ===== LAYOUT PRINCIPAL ===== */
.cat-body {
    display: flex;
    gap: 0;
    max-width: 1280px;
    margin: 0 auto;
    padding: 32px 24px;
    align-items: flex-start;
}

/* ===== TABLA DE CONTENIDOS (TOC) ===== */
.cat-toc {
    width: 230px;
    flex-shrink: 0;
    position: sticky;
    top: 70px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px 0;
    box-shadow: 0 1px 8px rgba(0,0,0,0.06);
    max-height: calc(100vh - 90px);
    overflow-y: auto;
}
.cat-toc h6 {
    font-size: 10px; font-weight: 700; letter-spacing: 1.2px;
    text-transform: uppercase; color: #9ca3af;
    padding: 0 18px 10px; margin: 0;
    border-bottom: 1px solid #f3f4f6;
}
.cat-toc-link {
    display: flex; align-items: center; gap: 9px;
    padding: 7px 18px;
    color: #6b7280; font-size: 0.8rem; font-weight: 500;
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: all 0.18s;
    cursor: pointer;
}
.cat-toc-link:hover {
    color: #1e293b; background: #f9fafb;
    border-left-color: #e5e7eb; text-decoration: none;
}
.cat-toc-link.active {
    color: #f59e0b; background: #fffbeb;
    border-left-color: #f59e0b; font-weight: 600;
}
.cat-toc-link i { font-size: 11px; width: 14px; text-align: center; }

/* ===== CONTENIDO PRINCIPAL ===== */
.cat-content {
    flex: 1;
    padding-left: 32px;
    min-width: 0;
}

/* ===== SECCIÓN ===== */
.cat-section {
    margin-bottom: 48px;
    scroll-margin-top: 80px;
}
.cat-section-title {
    display: flex; align-items: center; gap: 12px;
    font-size: 1.25rem; font-weight: 800; color: #1e293b;
    margin-bottom: 6px; padding-bottom: 12px;
    border-bottom: 2px solid #f1f5f9;
}
.cat-section-title .sec-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.cat-section-sub {
    color: #6b7280; font-size: 0.85rem;
    margin-bottom: 20px; padding-left: 48px;
}

/* ===== TARJETA DE MÓDULO ===== */
.mod-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 24px 28px;
    margin-bottom: 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}
.mod-card:hover {
    box-shadow: 0 6px 24px rgba(0,0,0,0.09);
    transform: translateY(-2px);
    border-color: #d1d5db;
}
.mod-card::before {
    content: '';
    position: absolute; top: 0; left: 0;
    width: 4px; height: 100%;
    border-radius: 14px 0 0 14px;
}
.mod-card-header {
    display: flex; align-items: flex-start; gap: 16px;
    margin-bottom: 16px;
}
.mod-icon-wrap {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.mod-card-info { flex: 1; min-width: 0; }
.mod-card-title {
    font-size: 1.05rem; font-weight: 700; color: #1e293b;
    margin-bottom: 4px;
}
.mod-card-desc {
    font-size: 0.85rem; color: #6b7280; line-height: 1.6;
}
.mod-badges { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 14px; }
.mod-badge {
    font-size: 10px; font-weight: 700; letter-spacing: 0.4px;
    padding: 3px 9px; border-radius: 20px;
    text-transform: uppercase;
}
.badge-admin { background: #fee2e2; color: #dc2626; }
.badge-info-c { background: #dbeafe; color: #2563eb; }
.badge-user { background: #d1fae5; color: #059669; }
.badge-inv { background: #f3f4f6; color: #6b7280; }

/* ===== FEATURES GRID ===== */
.mod-features {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 8px;
}
.mod-feature {
    display: flex; align-items: flex-start; gap: 8px;
    font-size: 0.8rem; color: #374151; line-height: 1.5;
    padding: 6px 10px;
    background: #f9fafb;
    border: 1px solid #f3f4f6;
    border-radius: 8px;
}
.mod-feature i { color: #3b82f6; font-size: 10px; margin-top: 3px; flex-shrink: 0; }

/* ===== TABLA INFO DENTRO DE TARJETA ===== */
.mod-table {
    width: 100%; font-size: 0.78rem;
    border-collapse: collapse; margin-top: 14px;
}
.mod-table th {
    background: #f8f9fa; color: #6b7280;
    font-size: 10px; text-transform: uppercase; letter-spacing: 0.6px;
    padding: 7px 12px; text-align: left; border-bottom: 1px solid #e5e7eb;
}
.mod-table td {
    padding: 7px 12px; border-bottom: 1px solid #f3f4f6;
    color: #374151; vertical-align: top;
}
.mod-table tr:last-child td { border-bottom: none; }

/* ===== SECCIÓN TECH STACK ===== */
.tech-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
}
.tech-card {
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 12px; padding: 18px 16px;
    display: flex; align-items: center; gap: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.tech-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px; flex-shrink: 0;
}
.tech-name { font-size: 0.82rem; font-weight: 700; color: #1e293b; }
.tech-desc { font-size: 0.72rem; color: #9ca3af; }

/* ===== ROL TABLA ===== */
.roles-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.roles-table th {
    background: #1e293b; color: #fff;
    padding: 10px 14px; text-align: left; font-weight: 600;
}
.roles-table th:first-child { border-radius: 10px 0 0 0; }
.roles-table th:last-child { border-radius: 0 10px 0 0; }
.roles-table td {
    padding: 9px 14px; border-bottom: 1px solid #f3f4f6; color: #374151;
}
.roles-table tr:last-child td { border-bottom: none; }
.roles-table tr:hover td { background: #f9fafb; }
.check-yes { color: #10b981; font-weight: 700; }
.check-partial { color: #f59e0b; font-weight: 700; }
.check-no { color: #e5e7eb; }

/* COLORES POR CATEGORÍA */
.color-blue  { background: #eff6ff; border-left-color: #3b82f6 !important; }
.color-green { background: #f0fdf4; border-left-color: #10b981 !important; }
.color-purple{ background: #faf5ff; border-left-color: #8b5cf6 !important; }
.color-amber { background: #fffbeb; border-left-color: #f59e0b !important; }
.color-rose  { background: #fff1f2; border-left-color: #f43f5e !important; }
.color-slate { background: #f8fafc; border-left-color: #64748b !important; }
.color-teal  { background: #f0fdfa; border-left-color: #14b8a6 !important; }
.color-indigo{ background: #eef2ff; border-left-color: #6366f1 !important; }

.icon-blue   { background: #eff6ff; color: #3b82f6; }
.icon-green  { background: #dcfce7; color: #16a34a; }
.icon-purple { background: #f3e8ff; color: #9333ea; }
.icon-amber  { background: #fef3c7; color: #d97706; }
.icon-rose   { background: #ffe4e6; color: #f43f5e; }
.icon-slate  { background: #f1f5f9; color: #475569; }
.icon-teal   { background: #ccfbf1; color: #0d9488; }
.icon-indigo { background: #e0e7ff; color: #4f46e5; }

.sec-blue   { background: #eff6ff; color: #3b82f6; }
.sec-green  { background: #dcfce7; color: #16a34a; }
.sec-amber  { background: #fef3c7; color: #d97706; }
.sec-purple { background: #f3e8ff; color: #9333ea; }
.sec-rose   { background: #ffe4e6; color: #f43f5e; }
.sec-slate  { background: #f1f5f9; color: #475569; }
.sec-teal   { background: #ccfbf1; color: #0d9488; }

/* RESPONSIVE */
@media (max-width: 900px) {
    .cat-toc { display: none; }
    .cat-content { padding-left: 0; }
    .cat-hero { padding: 32px 20px 28px; }
    .cat-hero h1 { font-size: 1.7rem; }
    .cat-body { padding: 20px 12px; }
}
@media (max-width: 600px) {
    .mod-features { grid-template-columns: 1fr; }
    .tech-grid { grid-template-columns: 1fr 1fr; }
    .cat-stats { gap: 14px; }
}
</style>
@endsection

@section('content_header')
<div class="d-flex align-items-center">
    <i class="fas fa-book-open text-warning mr-2"></i>
    <h1 class="mb-0" style="font-weight:700; font-size:1.3rem;">Catálogo del Sistema</h1>
</div>
@endsection

@section('content')
<div class="catalogo-wrap">

{{-- ===== HERO ===== --}}
<div class="cat-hero">
    <div class="cat-hero-badge">
        <i class="fas fa-book-open"></i> Documentación Institucional
    </div>
    <h1>Sistema de <span>Gestión de Inventarios</span></h1>
    <p>Plataforma integral para el control patrimonial del IESTP Enrique López Albújar. Conoce todos los módulos, funcionalidades y capacidades del sistema.</p>
    <div class="cat-stats">
        <div class="cat-stat"><div class="cat-stat-num">21</div><div class="cat-stat-lbl">Módulos</div></div>
        <div class="cat-stat"><div class="cat-stat-num">4</div><div class="cat-stat-lbl">Roles</div></div>
        <div class="cat-stat"><div class="cat-stat-num">PDF</div><div class="cat-stat-lbl">Reportes</div></div>
        <div class="cat-stat"><div class="cat-stat-num">QR</div><div class="cat-stat-lbl">Trazabilidad</div></div>
        <div class="cat-stat"><div class="cat-stat-num">APK</div><div class="cat-stat-lbl">App Móvil</div></div>
    </div>
</div>

{{-- ===== CUERPO ===== --}}
<div class="cat-body">

    {{-- TOC --}}
    <nav class="cat-toc">
        <h6>Contenido</h6>
        <a class="cat-toc-link active" onclick="scrollCat('sec-bienes')"><i class="fas fa-boxes"></i> Gestión de Bienes</a>
        <a class="cat-toc-link" onclick="scrollCat('sec-movimientos')"><i class="fas fa-exchange-alt"></i> Movimientos</a>
        <a class="cat-toc-link" onclick="scrollCat('sec-organizacion')"><i class="fas fa-sitemap"></i> Organización</a>
        <a class="cat-toc-link" onclick="scrollCat('sec-catalogos')"><i class="fas fa-tags"></i> Catálogos Maestros</a>
        <a class="cat-toc-link" onclick="scrollCat('sec-reportes')"><i class="fas fa-chart-bar"></i> Reportes y QR</a>
        <a class="cat-toc-link" onclick="scrollCat('sec-seguridad')"><i class="fas fa-shield-alt"></i> Usuarios y Acceso</a>
        <a class="cat-toc-link" onclick="scrollCat('sec-config')"><i class="fas fa-cog"></i> Configuración</a>
        <a class="cat-toc-link" onclick="scrollCat('sec-roles')"><i class="fas fa-user-tag"></i> Tabla de Roles</a>
        <a class="cat-toc-link" onclick="scrollCat('sec-tech')"><i class="fas fa-server"></i> Tecnología</a>
    </nav>

    {{-- CONTENIDO --}}
    <div class="cat-content">

        {{-- ========== SECCIÓN 1: BIENES ========== --}}
        <div class="cat-section" id="sec-bienes">
            <div class="cat-section-title">
                <div class="sec-icon sec-blue"><i class="fas fa-boxes"></i></div>
                <span>Gestión de Bienes Patrimoniales</span>
            </div>
            <p class="cat-section-sub">El corazón del sistema. Registro, consulta y seguimiento de todos los activos de la institución.</p>

            {{-- MÓDULO 01 --}}
            <div class="mod-card color-blue">
                <div class="mod-card-header">
                    <div class="mod-icon-wrap icon-blue"><i class="fas fa-box"></i></div>
                    <div class="mod-card-info">
                        <div class="mod-card-title">Inventario de Bienes</div>
                        <div class="mod-card-desc">Módulo central del sistema. Permite registrar, consultar y gestionar todos los bienes patrimoniales de la institución con su información completa, fotografía y código de identificación.</div>
                    </div>
                </div>
                <div class="mod-badges">
                    <span class="mod-badge badge-admin">Admin</span>
                    <span class="mod-badge badge-info-c">Informática</span>
                    <span class="mod-badge badge-user">Usuario</span>
                    <span class="mod-badge badge-inv">Invitado</span>
                </div>
                <div class="mod-features">
                    <div class="mod-feature"><i class="fas fa-check"></i>Registro con código patrimonial único</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Subida de fotografía del bien</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Búsqueda instantánea en tiempo real</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Vista detallada en modal (sin recargar)</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Edición rápida con doble clic</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Generación de QR individual</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Selección y operaciones masivas</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Filtros por estado, tipo, área y año</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Activar / desactivar bienes</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Paginación AJAX inteligente</div>
                </div>
            </div>

            {{-- MÓDULO DOCUMENTOS --}}
            <div class="mod-card color-teal">
                <div class="mod-card-header">
                    <div class="mod-icon-wrap icon-teal"><i class="fas fa-file-alt"></i></div>
                    <div class="mod-card-info">
                        <div class="mod-card-title">Documentos de Sustento</div>
                        <div class="mod-card-desc">Registro de los documentos oficiales que respaldan el ingreso de bienes: facturas, boletas, actas de entrega, NEA, entre otros. Cada bien puede vincularse a su documento de origen.</div>
                    </div>
                </div>
                <div class="mod-badges">
                    <span class="mod-badge badge-admin">Admin</span>
                    <span class="mod-badge badge-info-c">Informática</span>
                </div>
                <div class="mod-features">
                    <div class="mod-feature"><i class="fas fa-check"></i>Tipos libres: FACTURA, NEA, ACTA...</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Validación de número duplicado en tiempo real</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Muestra cuántos bienes tiene cada documento</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Protección: no elimina si tiene bienes asociados</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Eliminación masiva con advertencias</div>
                </div>
            </div>
        </div>

        {{-- ========== SECCIÓN 2: MOVIMIENTOS ========== --}}
        <div class="cat-section" id="sec-movimientos">
            <div class="cat-section-title">
                <div class="sec-icon sec-green"><i class="fas fa-exchange-alt"></i></div>
                <span>Control de Movimientos y Trazabilidad</span>
            </div>
            <p class="cat-section-sub">El "libro mayor" del inventario. Registra todo el ciclo de vida de cada activo: desde su ingreso hasta su baja definitiva.</p>

            <div class="mod-card color-green">
                <div class="mod-card-header">
                    <div class="mod-icon-wrap icon-green"><i class="fas fa-exchange-alt"></i></div>
                    <div class="mod-card-info">
                        <div class="mod-card-title">Movimientos de Bienes</div>
                        <div class="mod-card-desc">Permite saber dónde está cada bien en todo momento. Registra traslados, asignaciones a áreas y bajas definitivas con historial completo y trazabilidad auditada.</div>
                    </div>
                </div>
                <div class="mod-badges">
                    <span class="mod-badge badge-admin">Admin</span>
                    <span class="mod-badge badge-info-c">Informática</span>
                    <span class="mod-badge badge-user">Usuario (consulta)</span>
                </div>
                <div class="mod-features">
                    <div class="mod-feature"><i class="fas fa-check"></i>Asignación masiva a área/ambiente</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Baja masiva de múltiples bienes</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Reversión de baja (restaurar bien)</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Trazabilidad completa por bien</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>PDF de historial por bien</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Filtros por tipo, área, fechas, estado</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Leyenda visual por tipo de movimiento</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Visor de trazabilidad en modal</div>
                </div>

                <table class="mod-table">
                    <thead><tr><th>Tipo</th><th>Color</th><th>Significado</th></tr></thead>
                    <tbody>
                        <tr><td><strong>SIN ASIGNAR</strong></td><td><span class="badge" style="background:#dbeafe;color:#2563eb">Azul</span></td><td>Bien recién registrado, sin ubicación final</td></tr>
                        <tr><td><strong>ASIGNACIÓN</strong></td><td><span class="badge" style="background:#dcfce7;color:#16a34a">Verde</span></td><td>Bien activo asignado a un área y ambiente</td></tr>
                        <tr><td><strong>BAJA</strong></td><td><span class="badge" style="background:#fee2e2;color:#dc2626">Rojo</span></td><td>Bien retirado del inventario activo</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ========== SECCIÓN 3: ORGANIZACIÓN ========== --}}
        <div class="cat-section" id="sec-organizacion">
            <div class="cat-section-title">
                <div class="sec-icon sec-purple"><i class="fas fa-sitemap"></i></div>
                <span>Estructura Organizativa</span>
            </div>
            <p class="cat-section-sub">Módulos de catálogo que definen la organización espacial y humana de la institución.</p>

            <div class="mod-card color-purple">
                <div class="mod-card-header">
                    <div class="mod-icon-wrap icon-purple"><i class="fas fa-th-large"></i></div>
                    <div class="mod-card-info">
                        <div class="mod-card-title">Áreas Institucionales</div>
                        <div class="mod-card-desc">Gestiona los departamentos o áreas funcionales de la institución (Dirección, Secretaría, Laboratorios, etc.). Las áreas agrupan las ubicaciones físicas y definen el ámbito de acceso de los usuarios.</div>
                    </div>
                </div>
                <div class="mod-badges"><span class="mod-badge badge-admin">Admin (CRUD)</span><span class="mod-badge badge-inv">Otros (solo lectura)</span></div>
                <div class="mod-features">
                    <div class="mod-feature"><i class="fas fa-check"></i>Registro y edición de áreas</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Búsqueda en tiempo real</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Eliminación segura (sin dependencias)</div>
                </div>
            </div>

            <div class="mod-card color-indigo">
                <div class="mod-card-header">
                    <div class="mod-icon-wrap icon-indigo"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="mod-card-info">
                        <div class="mod-card-title">Ubicaciones y Ambientes</div>
                        <div class="mod-card-desc">Define los espacios físicos concretos donde se ubican los bienes: aulas, oficinas, laboratorios, almacenes. Cada ubicación tiene una sede, un <strong>ambiente</strong> (identificador visual en toda la plataforma), piso y área.</div>
                    </div>
                </div>
                <div class="mod-badges"><span class="mod-badge badge-admin">Admin (CRUD)</span><span class="mod-badge badge-inv">Otros (solo lectura)</span></div>
                <div class="mod-features">
                    <div class="mod-feature"><i class="fas fa-star" style="color:#f59e0b"></i>Marcar "Recepción Inicial" (punto de llegada de bienes nuevos)</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Filtro por área</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Búsqueda por sede, ambiente o piso</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Solo una ubicación activa como recepción</div>
                </div>
            </div>

            <div class="mod-card color-slate">
                <div class="mod-card-header">
                    <div class="mod-icon-wrap icon-slate"><i class="fas fa-user-tie"></i></div>
                    <div class="mod-card-info">
                        <div class="mod-card-title">Responsables de Bienes</div>
                        <div class="mod-card-desc">Registro del personal que puede ser designado como responsable de los bienes. Al vincular un responsable a un usuario, ese usuario hereda las áreas de acceso del responsable.</div>
                    </div>
                </div>
                <div class="mod-badges"><span class="mod-badge badge-admin">Admin (CRUD)</span><span class="mod-badge badge-info-c">Informática (lectura)</span></div>
                <div class="mod-features">
                    <div class="mod-feature"><i class="fas fa-check"></i>DNI, nombre, apellidos y cargo</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Búsqueda por DNI o nombre</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Vinculación con cuentas de usuario</div>
                </div>
            </div>
        </div>

        {{-- ========== SECCIÓN 3.5: CATÁLOGOS MAESTROS ========== --}}
        <div class="cat-section" id="sec-catalogos">
            <div class="cat-section-title">
                <div class="sec-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-tags"></i></div>
                <span>Catálogos Maestros</span>
            </div>
            <p class="cat-section-sub">Tablas de referencia que alimentan los formularios del sistema. Solo el Administrador puede modificarlas.</p>

            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px;">

                <div class="mod-card color-amber" style="margin-bottom:0;">
                    <div class="mod-card-header" style="margin-bottom:10px;">
                        <div class="mod-icon-wrap icon-amber" style="width:38px;height:38px;font-size:16px;"><i class="fas fa-tag"></i></div>
                        <div class="mod-card-info">
                            <div class="mod-card-title" style="font-size:0.95rem;">Tipos de Bien</div>
                            <div class="mod-card-desc">Categorías del inventario: Mueble, Equipo de Cómputo, Vehículo, Electrodoméstico, etc.</div>
                        </div>
                    </div>
                    <div class="mod-badges"><span class="mod-badge badge-admin">Solo Admin</span></div>
                    <div class="mod-features" style="grid-template-columns:1fr;">
                        <div class="mod-feature"><i class="fas fa-check"></i>Usado al registrar cada bien</div>
                        <div class="mod-feature"><i class="fas fa-check"></i>Agrupa bienes en reportes</div>
                    </div>
                </div>

                <div class="mod-card color-green" style="margin-bottom:0;">
                    <div class="mod-card-header" style="margin-bottom:10px;">
                        <div class="mod-icon-wrap icon-green" style="width:38px;height:38px;font-size:16px;"><i class="fas fa-heartbeat"></i></div>
                        <div class="mod-card-info">
                            <div class="mod-card-title" style="font-size:0.95rem;">Estados de Conservación</div>
                            <div class="mod-card-desc">Condición física del bien: BUENO, REGULAR, MALO, EN REPARACIÓN.</div>
                        </div>
                    </div>
                    <div class="mod-badges"><span class="mod-badge badge-admin">Solo Admin</span></div>
                    <div class="mod-features" style="grid-template-columns:1fr;">
                        <div class="mod-feature"><i class="fas fa-check"></i>Registrado en cada movimiento</div>
                        <div class="mod-feature"><i class="fas fa-check"></i>Aparece en reportes de estado</div>
                    </div>
                </div>

                <div class="mod-card color-indigo" style="margin-bottom:0;">
                    <div class="mod-card-header" style="margin-bottom:10px;">
                        <div class="mod-icon-wrap icon-indigo" style="width:38px;height:38px;font-size:16px;"><i class="fas fa-random"></i></div>
                        <div class="mod-card-info">
                            <div class="mod-card-title" style="font-size:0.95rem;">Tipos de Movimiento</div>
                            <div class="mod-card-desc">Categorías del ciclo de vida: SIN ASIGNAR, ASIGNACIÓN, BAJA.</div>
                        </div>
                    </div>
                    <div class="mod-badges"><span class="mod-badge badge-admin">Solo Admin</span></div>
                    <div class="mod-features" style="grid-template-columns:1fr;">
                        <div class="mod-feature"><i class="fas fa-check"></i>Define el estado operativo del bien</div>
                        <div class="mod-feature"><i class="fas fa-check"></i>Base de filtros en movimientos</div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ========== SECCIÓN 4: REPORTES Y QR ========== --}}
        <div class="cat-section" id="sec-reportes">
            <div class="cat-section-title">
                <div class="sec-icon sec-amber"><i class="fas fa-chart-bar"></i></div>
                <span>Reportes, Exportaciones y Códigos QR</span>
            </div>
            <p class="cat-section-sub">Herramientas para generar documentos oficiales, análisis del inventario y etiquetas QR para identificación física.</p>

            <div class="mod-card color-amber">
                <div class="mod-card-header">
                    <div class="mod-icon-wrap icon-amber"><i class="fas fa-file-pdf"></i></div>
                    <div class="mod-card-info">
                        <div class="mod-card-title">Reportes de Inventario</div>
                        <div class="mod-card-desc">Genera reportes oficiales del inventario en PDF y Excel. Los PDF llevan el membrete institucional con logo, datos de la sede y firma de conformidad. Los filtros permiten reportes altamente específicos.</div>
                    </div>
                </div>
                <div class="mod-badges"><span class="mod-badge badge-admin">Admin (todos)</span><span class="mod-badge badge-info-c">Informática (básicos)</span><span class="mod-badge badge-inv">Invitado (limitado)</span></div>
                <table class="mod-table">
                    <thead><tr><th>Tipo de Reporte</th><th>Descripción</th></tr></thead>
                    <tbody>
                        <tr><td><strong>Inventario General</strong></td><td>Todos los bienes por año seleccionado</td></tr>
                        <tr><td><strong>Por Área y Ubicación</strong></td><td>Bienes agrupados por área y ambiente</td></tr>
                        <tr><td><strong>Por Estado de Conservación</strong></td><td>Bienes filtrados por condición física</td></tr>
                        <tr><td><strong>Por Responsable</strong></td><td>Bienes asignados a una persona</td></tr>
                        <tr><td><strong>Exportar Excel</strong></td><td>Mismo inventario en formato .xlsx editable</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="mod-card color-rose">
                <div class="mod-card-header">
                    <div class="mod-icon-wrap icon-rose"><i class="fas fa-qrcode"></i></div>
                    <div class="mod-card-info">
                        <div class="mod-card-title">Generador de Códigos QR</div>
                        <div class="mod-card-desc">Permite generar códigos QR para identificar físicamente cualquier bien. Ofrece generación masiva en PDF (para imprimir hojas de etiquetas) y generación individual descargable.</div>
                    </div>
                </div>
                <div class="mod-badges"><span class="mod-badge badge-admin">Admin</span><span class="mod-badge badge-info-c">Informática</span></div>
                <div class="mod-features">
                    <div class="mod-feature"><i class="fas fa-check"></i>PDF masivo: 16 QRs por hoja A4</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>QR individual descargable como PNG</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Filtros combinables antes de generar</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Cada QR muestra código + nombre + tipo</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Vinculado a la API de la app móvil</div>
                </div>
            </div>
        </div>

        {{-- ========== SECCIÓN 5: SEGURIDAD ========== --}}
        <div class="cat-section" id="sec-seguridad">
            <div class="cat-section-title">
                <div class="sec-icon sec-rose"><i class="fas fa-shield-alt"></i></div>
                <span>Usuarios, Roles y Control de Acceso</span>
            </div>
            <p class="cat-section-sub">Gestión de cuentas del sistema web y de la aplicación móvil, con un sistema de permisos por rol y por área.</p>

            <div class="mod-card color-rose">
                <div class="mod-card-header">
                    <div class="mod-icon-wrap icon-rose"><i class="fas fa-users-cog"></i></div>
                    <div class="mod-card-info">
                        <div class="mod-card-title">Gestión de Usuarios</div>
                        <div class="mod-card-desc">Administra las cuentas de acceso al portal web y a la aplicación móvil Android. Cada usuario tiene dos contraseñas independientes: una para el portal y otra exclusiva para la app móvil.</div>
                    </div>
                </div>
                <div class="mod-badges"><span class="mod-badge badge-admin">Solo Admin</span></div>
                <div class="mod-features">
                    <div class="mod-feature"><i class="fas fa-check"></i>Contraseña web y contraseña móvil independientes</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Asignación de rol (Admin/Informática/Invitado)</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Vinculación a responsable para limitar áreas</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Filtros por rol, estado y último acceso</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Activar / desactivar usuarios en lote</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Registro de último ingreso por usuario</div>
                </div>
            </div>

            <div class="mod-card color-slate">
                <div class="mod-card-header">
                    <div class="mod-icon-wrap icon-slate"><i class="fas fa-mobile-alt"></i></div>
                    <div class="mod-card-info">
                        <div class="mod-card-title">Aplicación Móvil Android</div>
                        <div class="mod-card-desc">Extensión del sistema para trabajo en campo. Permite a los técnicos realizar inventarios físicos escaneando los códigos QR de los bienes, con soporte de modo sin conexión a internet.</div>
                    </div>
                </div>
                <div class="mod-badges"><span class="mod-badge badge-admin">Admin</span><span class="mod-badge badge-info-c">Informática</span></div>
                <div class="mod-features">
                    <div class="mod-feature"><i class="fas fa-check"></i>Escaneo de QR con cámara del celular</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Ficha completa del bien en pantalla</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Modo offline (sin internet)</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Sincronización automática al reconectar</div>
                    <div class="mod-feature"><i class="fas fa-check"></i>Contraseña móvil independiente del portal</div>
                </div>
            </div>
        </div>

        {{-- ========== SECCIÓN 6: CONFIGURACIÓN ========== --}}
        <div class="cat-section" id="sec-config">
            <div class="cat-section-title">
                <div class="sec-icon sec-teal"><i class="fas fa-cog"></i></div>
                <span>Configuración Institucional</span>
            </div>
            <p class="cat-section-sub">Personaliza el sistema con los datos, logos y preferencias de la institución.</p>

            <div class="mod-card color-teal">
                <div class="mod-card-header">
                    <div class="mod-icon-wrap icon-teal"><i class="fas fa-sliders-h"></i></div>
                    <div class="mod-card-info">
                        <div class="mod-card-title">Configuración del Sistema</div>
                        <div class="mod-card-desc">Permite personalizar toda la identidad visual e institucional del sistema. Los cambios se reflejan en el portal web y en todos los reportes PDF generados.</div>
                    </div>
                </div>
                <div class="mod-badges"><span class="mod-badge badge-admin">Solo Admin</span></div>
                <table class="mod-table">
                    <thead><tr><th>Pestaña</th><th>Configura</th></tr></thead>
                    <tbody>
                        <tr><td><strong>Identidad</strong></td><td>Nombre, slogan, RUC, logos, favicon, tema del sidebar</td></tr>
                        <tr><td><strong>Contacto</strong></td><td>Email, teléfono y dirección institucional</td></tr>
                        <tr><td><strong>Regional</strong></td><td>Moneda (PEN), zona horaria, locale y formato de fecha</td></tr>
                        <tr><td><strong>Reportes</strong></td><td>Texto del pie de página y leyenda legal en PDFs</td></tr>
                        <tr><td><strong>Soporte</strong></td><td>Correo y teléfono del área de soporte técnico</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ========== SECCIÓN 7: TABLA DE ROLES ========== --}}
        <div class="cat-section" id="sec-roles">
            <div class="cat-section-title">
                <div class="sec-icon sec-amber"><i class="fas fa-user-tag"></i></div>
                <span>Tabla de Acceso por Rol</span>
            </div>
            <p class="cat-section-sub">Resumen de permisos por módulo según el rol asignado a cada usuario del sistema.</p>

            <div class="mod-card" style="padding:0; overflow:hidden;">
                <table class="roles-table">
                    <thead>
                        <tr>
                            <th style="width:32%">Módulo / Función</th>
                            <th class="text-center">Admin</th>
                            <th class="text-center">Informática</th>
                            <th class="text-center">Usuario</th>
                            <th class="text-center">Invitado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><i class="fas fa-box text-blue mr-1"></i> Inventario de Bienes</td><td class="text-center check-yes">✔ Total</td><td class="text-center check-yes">✔ Total</td><td class="text-center check-partial">◑ Lectura</td><td class="text-center check-partial">◑ Lectura</td></tr>
                        <tr><td><i class="fas fa-exchange-alt text-green-500 mr-1"></i> Movimientos</td><td class="text-center check-yes">✔ Total</td><td class="text-center check-partial">◑ Sin baja</td><td class="text-center check-partial">◑ Consulta</td><td class="text-center check-no">✗</td></tr>
                        <tr><td><i class="fas fa-chart-bar text-amber-500 mr-1"></i> Reportes PDF/Excel</td><td class="text-center check-yes">✔ Total</td><td class="text-center check-partial">◑ Básicos</td><td class="text-center check-no">✗</td><td class="text-center check-partial">◑ Limitado</td></tr>
                        <tr><td><i class="fas fa-qrcode text-rose-500 mr-1"></i> Generador QR</td><td class="text-center check-yes">✔</td><td class="text-center check-yes">✔</td><td class="text-center check-no">✗</td><td class="text-center check-no">✗</td></tr>
                        <tr><td><i class="fas fa-sitemap mr-1"></i> Áreas y Ubicaciones</td><td class="text-center check-yes">✔ CRUD</td><td class="text-center check-partial">◑ Lectura</td><td class="text-center check-partial">◑ Lectura</td><td class="text-center check-no">✗</td></tr>
                        <tr><td><i class="fas fa-users-cog mr-1"></i> Gestión de Usuarios</td><td class="text-center check-yes">✔</td><td class="text-center check-no">✗</td><td class="text-center check-no">✗</td><td class="text-center check-no">✗</td></tr>
                        <tr><td><i class="fas fa-cog mr-1"></i> Configuración</td><td class="text-center check-yes">✔</td><td class="text-center check-no">✗</td><td class="text-center check-no">✗</td><td class="text-center check-no">✗</td></tr>
                        <tr><td><i class="fas fa-mobile-alt mr-1"></i> App Móvil Android</td><td class="text-center check-yes">✔</td><td class="text-center check-yes">✔</td><td class="text-center check-no">✗</td><td class="text-center check-no">✗</td></tr>
                        <tr><td><i class="fas fa-book-open mr-1"></i> Catálogo del Sistema</td><td class="text-center check-yes">✔</td><td class="text-center check-yes">✔</td><td class="text-center check-yes">✔</td><td class="text-center check-yes">✔</td></tr>
                    </tbody>
                </table>
            </div>
            <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:12px 18px; margin-top:12px; font-size:0.8rem; color:#6b7280;">
                <strong>Leyenda:</strong> &nbsp;
                <span class="check-yes">✔ Acceso total</span> &nbsp;|&nbsp;
                <span class="check-partial">◑ Acceso parcial o de solo lectura</span> &nbsp;|&nbsp;
                <span class="check-no">✗ Sin acceso</span>
            </div>
        </div>

        {{-- ========== SECCIÓN 8: TECNOLOGÍA ========== --}}
        <div class="cat-section" id="sec-tech">
            <div class="cat-section-title">
                <div class="sec-icon sec-slate"><i class="fas fa-server"></i></div>
                <span>Plataforma Tecnológica</span>
            </div>
            <p class="cat-section-sub">El sistema está construido con tecnologías modernas, robustas y de código abierto, garantizando estabilidad y seguridad a largo plazo.</p>

            <div class="tech-grid">
                <div class="tech-card">
                    <div class="tech-icon icon-rose"><i class="fab fa-laravel"></i></div>
                    <div><div class="tech-name">Laravel 10</div><div class="tech-desc">Framework backend PHP</div></div>
                </div>
                <div class="tech-card">
                    <div class="tech-icon" style="background:#f0fdf4;color:#16a34a;"><i class="fas fa-database"></i></div>
                    <div><div class="tech-name">PostgreSQL</div><div class="tech-desc">Base de datos relacional</div></div>
                </div>
                <div class="tech-card">
                    <div class="tech-icon icon-blue"><i class="fab fa-js"></i></div>
                    <div><div class="tech-name">Node.js API</div><div class="tech-desc">API para app móvil</div></div>
                </div>
                <div class="tech-card">
                    <div class="tech-icon icon-amber"><i class="fas fa-file-pdf"></i></div>
                    <div><div class="tech-name">DomPDF</div><div class="tech-desc">Generación de reportes PDF</div></div>
                </div>
                <div class="tech-card">
                    <div class="tech-icon icon-green"><i class="fas fa-file-excel"></i></div>
                    <div><div class="tech-name">Laravel Excel</div><div class="tech-desc">Exportación .xlsx</div></div>
                </div>
                <div class="tech-card">
                    <div class="tech-icon icon-slate"><i class="fas fa-qrcode"></i></div>
                    <div><div class="tech-name">Endroid QR</div><div class="tech-desc">Códigos QR de alta calidad</div></div>
                </div>
                <div class="tech-card">
                    <div class="tech-icon icon-purple"><i class="fab fa-android"></i></div>
                    <div><div class="tech-name">App Android</div><div class="tech-desc">Inventario de campo</div></div>
                </div>
                <div class="tech-card">
                    <div class="tech-icon" style="background:#fff7ed;color:#ea580c;"><i class="fas fa-paint-brush"></i></div>
                    <div><div class="tech-name">AdminLTE 3</div><div class="tech-desc">UI institucional responsive</div></div>
                </div>
            </div>

            {{-- NOTA FINAL --}}
            <div style="background: linear-gradient(135deg,#1e293b,#0f172a); border-radius:14px; padding:28px 32px; margin-top:32px; color:#fff; text-align:center;">
                <i class="fas fa-shield-alt" style="font-size:2rem; color:#f59e0b; margin-bottom:12px; display:block;"></i>
                <h4 style="color:#fff; font-weight:800; margin-bottom:8px;">Sistema Seguro y Auditado</h4>
                <p style="color:rgba(255,255,255,0.65); font-size:0.88rem; max-width:480px; margin: 0 auto;">
                    Todas las operaciones quedan registradas con usuario, fecha y hora. El sistema implementa protección CSRF, validación de permisos por rol y restricción de acceso por área institucional.
                </p>
                <div style="margin-top:20px; display:flex; justify-content:center; gap:20px; flex-wrap:wrap;">
                    <span style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:8px; padding:8px 18px; font-size:0.78rem; color:rgba(255,255,255,0.7);">
                        <i class="fas fa-lock mr-1" style="color:#f59e0b;"></i> Autenticación segura
                    </span>
                    <span style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:8px; padding:8px 18px; font-size:0.78rem; color:rgba(255,255,255,0.7);">
                        <i class="fas fa-user-check mr-1" style="color:#10b981;"></i> Control por roles
                    </span>
                    <span style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:8px; padding:8px 18px; font-size:0.78rem; color:rgba(255,255,255,0.7);">
                        <i class="fas fa-history mr-1" style="color:#3b82f6;"></i> Historial auditado
                    </span>
                </div>
            </div>
        </div>

    </div>{{-- /cat-content --}}
</div>{{-- /cat-body --}}
</div>{{-- /catalogo-wrap --}}
@endsection

@section('js')
<script>
// Scroll suave — nombre propio para no colisionar con window.scrollTo nativa
function scrollCat(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const top = el.getBoundingClientRect().top + window.pageYOffset - 75;
    window.scrollTo({ top: top, behavior: 'smooth' });
}

// Resaltar sección activa en el TOC
const catSections = ['sec-bienes','sec-movimientos','sec-organizacion','sec-catalogos','sec-reportes','sec-seguridad','sec-config','sec-roles','sec-tech'];
const catLinks = document.querySelectorAll('.cat-toc-link');

window.addEventListener('scroll', function() {
    let current = 'sec-bienes';
    catSections.forEach(id => {
        const el = document.getElementById(id);
        if (el && window.pageYOffset >= el.offsetTop - 130) current = id;
    });
    catLinks.forEach(link => {
        const fn = link.getAttribute('onclick') || '';
        link.classList.toggle('active', fn.includes(current));
    });
}, { passive: true });
</script>
@endsection
