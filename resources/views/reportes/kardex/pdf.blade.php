<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Movimientos</title>

    <style>
        @page { size: A4 landscape; margin: 10mm 10mm 14mm 10mm; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            color: #111;
        }

        /* Helpers */
        .b { font-weight: bold; }
        .c { text-align: center; }
        .r { text-align: right; }
        .muted { color: #555; }
        .upper { text-transform: uppercase; }
        .small { font-size: 8px; }

        /* Header frame like institutional forms */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-table td {
            border: 1px solid #000;
            padding: 6px 6px;
            vertical-align: middle;
        }

        .logo-box {
            width: 80px;
            height: 52px;
            border: 1px solid #000;
            display: table;
        }
        .logo-box span {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            font-size: 8px;
            color: #333;
        }

        .header-title {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            line-height: 1.25;
        }
        .header-subtitle {
            font-size: 9px;
            text-align: center;
            margin-top: 2px;
        }

        /* Secondary header line (like your sample) */
        .header-meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header-meta td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 8px;
        }

        /* Filters block */
        .filters {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 10px;
        }
        .filters td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 8px;
            vertical-align: top;
        }
        .filters .label {
            width: 14%;
            font-weight: bold;
        }
        .filters .value {
            width: 36%;
        }

        /* Data table */
        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.data th, table.data td {
            border: 1px solid #000;
            padding: 4px 5px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.data thead th {
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }
        table.data tbody tr:nth-child(even) {
            background: #f6f6f6; /* si lo quieres 100% blanco, borra esta regla */
        }

        /* Column widths: ajustadas para que no se desordene */
        .w-fecha { width: 7%; }
        .w-tipo  { width: 8%; }
        .w-cod   { width: 11%; }
        .w-bien  { width: 14%; }
        .w-tbien { width: 8%; }
        .w-area  { width: 10%; }
        .w-sede  { width: 10%; }
        .w-amb   { width: 9%; }
        .w-piso  { width: 5%; }
        .w-est   { width: 8%; }
        .w-doc   { width: 6%; }
        .w-ndoc  { width: 7%; }
        .w-det   { width: 7%; }

        /* Footer fixed */
        .footer {
            position: fixed;
            bottom: -10mm;
            left: 10mm;
            right: 10mm;
            font-size: 8px;
            color: #111;
        }
        .footer .left { float: left; }
        .footer .right { float: right; }
        .pagenum:before { content: counter(page); }
        .pagecount:before { content: counter(pages); }
        .clearfix { clear: both; }
    </style>
</head>

<body>
@php
    $tieneFiltros = !empty($filtros['desde']) || !empty($filtros['hasta']) ||
                    !empty($filtros['tipo_mvto']) || !empty($filtros['tipo_bien']) ||
                    !empty($filtros['area']) || !empty($filtros['estado']) ||
                    !empty($filtros['q']) || !empty($filtros['solo_operativos']);
@endphp

{{-- HEADER PRINCIPAL --}}
<table class="header-table">
    <tr>
        <td style="width: 12%;" class="c">
            {{-- Placeholder de logo (cuando tengas uno real lo cambiamos) --}}
            <div class="logo-box">
                <span>LOGO</span>
            </div>
        </td>

        <td style="width: 68%;">
            <div class="header-title upper">{{ config('app.name', 'Sistema de Gestión de Inventario') }}</div>
            <div class="header-subtitle upper">REPORTE DE MOVIMIENTOS</div>
        </td>

        <td style="width: 20%;">
            <div class="small"><span class="b">Fecha:</span> {{ $generado->format('d/m/Y H:i') }}</div>
            <div class="small"><span class="b">Registros:</span> {{ count($rows) }}</div>
            <div class="small"><span class="b">Página:</span> <span class="pagenum"></span> / <span class="pagecount"></span></div>
        </td>
    </tr>
</table>

{{-- META LINE (tipo “formato”) --}}
<table class="header-meta">
    <tr>
        <td style="width: 25%;"><span class="b">Módulo:</span> Reportes</td>
        <td style="width: 25%;"><span class="b">Reporte:</span> Movimientos</td>
        <td style="width: 25%;"><span class="b">Usuario imprime:</span> {{ auth()->user()->name ?? '-' }}</td>
        <td style="width: 25%;"><span class="b">Orden:</span> Fecha desc</td>
    </tr>
</table>

{{-- FILTROS --}}
@if($tieneFiltros)
<table class="filters">
    <tr>
        <td class="label upper">Desde</td>
        <td class="value">{{ !empty($filtros['desde']) ? \Carbon\Carbon::parse($filtros['desde'])->format('d/m/Y') : '-' }}</td>

        <td class="label upper">Hasta</td>
        <td class="value">{{ !empty($filtros['hasta']) ? \Carbon\Carbon::parse($filtros['hasta'])->format('d/m/Y') : '-' }}</td>
    </tr>

    <tr>
        <td class="label upper">Tipo movto</td>
        <td class="value">{{ $filtros['tipo_mvto'] ?? '-' }}</td>

        <td class="label upper">Tipo bien</td>
        <td class="value">{{ $filtros['tipo_bien'] ?? '-' }}</td>
    </tr>

    <tr>
        <td class="label upper">Área</td>
        <td class="value">{{ $filtros['area'] ?? '-' }}</td>

        <td class="label upper">Estado</td>
        <td class="value">{{ $filtros['estado'] ?? '-' }}</td>
    </tr>

    <tr>
        <td class="label upper">Solo operativos</td>
        <td class="value">{{ !empty($filtros['solo_operativos']) ? 'SI' : 'NO' }}</td>

        <td class="label upper">Búsqueda</td>
        <td class="value">{{ $filtros['q'] ?? '-' }}</td>
    </tr>
</table>
@endif

{{-- TABLA --}}
@if($rows && count($rows) > 0)
<table class="data">
    <thead>
        <tr>
            <th class="w-fecha c">Fecha</th>
            <th class="w-tipo c">Tipo movto</th>
            <th class="w-cod c">Código</th>
            <th class="w-bien">Bien</th>
            <th class="w-tbien c">Tipo bien</th>
            <th class="w-area">Área</th>
            <th class="w-sede">Sede</th>
            <th class="w-amb">Ambiente</th>
            <th class="w-piso c">Piso</th>
            <th class="w-est c">Estado</th>
            <th class="w-doc c">Doc</th>
            <th class="w-ndoc c">Nro doc</th>
            <th class="w-det">Detalle</th>
        </tr>
    </thead>

    <tbody>
        @foreach($rows as $row)
        <tr>
            <td class="c">{{ \Carbon\Carbon::parse($row->fecha_mvto)->format('d/m/Y') }}</td>
            <td class="c b">{{ $row->tipo_movimiento }}</td>
            <td class="c">{{ $row->codigo_patrimonial }}</td>
            <td>{{ $row->denominacion_bien }}</td>
            <td class="c">{{ $row->tipo_bien }}</td>
            <td>{{ $row->area }}</td>
            <td>{{ $row->sede }}</td>
            <td>{{ $row->ambiente }}</td>
            <td class="c">{{ $row->piso }}</td>
            <td class="c">{{ $row->estado_bien }}</td>
            <td class="c">{{ $row->tipo_documento }}</td>
            <td class="c">{{ $row->numero_documento }}</td>
            <td>{{ $row->detalle_tecnico ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
    <div style="padding: 10px; border:1px solid #000;">No hay registros para mostrar.</div>
@endif

<div class="footer">
    <div class="left">
        <span class="b">{{ config('app.name','Sistema') }}</span> · Reporte de Movimientos
    </div>
    <div class="right">
        Página <span class="pagenum"></span> de <span class="pagecount"></span>
    </div>
    <div class="clearfix"></div>
</div>

</body>
</html>
