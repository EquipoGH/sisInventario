<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Bajas de Bienes</title>
    <style>
        @page { margin: 6mm 6mm 10mm 6mm; }
        body {
            margin: 0; padding: 12px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8px; color: #2c3e50; line-height: 1.4;
        }
        /* ENCABEZADO */
        .header {
            text-align: center; margin-bottom: 12px; padding: 10px 0;
            border-bottom: 3px solid #c62828; background-color: #ffebee;
        }
        .header .institution { font-size: 7px; color: #37474f; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; }
        .header h1 { font-size: 15px; color: #c62828; margin: 5px 0; font-weight: 700; text-transform: uppercase; }
        .header .subtitle { font-size: 7px; color: #546e7a; font-style: italic; }

        /* FILTROS APLICADOS */
        .filtros-box {
            background-color: #f5f5f5; border: 1px solid #bdbdbd;
            padding: 5px 10px; margin-bottom: 10px; font-size: 7px;
        }
        .filtros-box strong { color: #37474f; }

        /* ESTADISTICAS */
        .stats-row { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .stats-row td {
            border: 2px solid #c62828; padding: 8px; text-align: center;
            background-color: #ffebee;
        }
        .stat-label { font-size: 7px; color: #37474f; text-transform: uppercase; font-weight: 700; }
        .stat-value { font-size: 16px; font-weight: 700; color: #c62828; }
        .stat-detail { font-size: 6px; color: #546e7a; font-style: italic; margin-top: 2px; }

        /* TABLA PRINCIPAL */
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .main-table thead th {
            background-color: #37474f; color: white; border: 1px solid #263238;
            padding: 6px 4px; text-align: center; font-size: 6.5px;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;
        }
        .main-table tbody td {
            border: 1px solid #b0bec5; padding: 5px 4px;
            font-size: 7px; vertical-align: middle;
        }
        .main-table tbody tr:nth-child(even) { background-color: #fafafa; }

        /* ANCHOS */
        .col-n    { width: 3%;  text-align: center; }
        .col-cod  { width: 12%; text-align: center; }
        .col-den  { width: 20%; }
        .col-tipo { width: 9%;  text-align: center; }
        .col-fecha{ width: 8%;  text-align: center; }
        .col-mot  { width: 22%; }
        .col-res  { width: 12%; }
        .col-obs  { width: 14%; }

        /* BADGES */
        .badge {
            display: inline-block; padding: 2px 5px; border-radius: 2px;
            font-size: 5.5px; font-weight: 700; text-transform: uppercase;
        }
        .badge-danger    { background-color: #c62828; color: white; }
        .badge-secondary { background-color: #78909c; color: white; }

        .cod-text   { color: #c62828; font-weight: 700; font-size: 7px; }
        .den-text   { font-weight: 700; font-size: 7px; color: #263238; }
        .marca-text { color: #78909c; font-size: 6px; display: block; }
        .res-text   { color: #1b5e20; font-size: 6.5px; font-weight: 600; word-wrap: break-word; }
        .sin-res    { color: #9e9e9e; font-style: italic; }
        .mot-text   { color: #2c3e50; font-size: 6.5px; line-height: 1.3; word-wrap: break-word; }
        .obs-text   { color: #546e7a; font-size: 6px; font-style: italic; line-height: 1.3; word-wrap: break-word; }
        .no-data    { text-align: center; padding: 25px; color: #9e9e9e; font-style: italic; border: 2px dashed #bdbdbd; }

        /* PIE */
        .footer {
            margin-top: 14px; padding-top: 7px; border-top: 2px solid #90a4ae;
            font-size: 6.5px; color: #546e7a;
        }
        .footer p { margin: 2px 0; }
        .footer-d {
            margin-top: 6px; text-align: center; font-style: italic;
            color: #9e9e9e; font-size: 5.5px; padding: 4px;
            background-color: #fafafa;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="institution">Instituto de Educacion Superior Tecnologico Publico</div>
        <h1>Registro de Bajas de Bienes Patrimoniales</h1>
        <div class="subtitle">Sistema de Gestion de Inventario — GesInventario v2.0</div>
    </div>

    {{-- FILTROS --}}
    <div class="filtros-box">
        <strong>Filtros aplicados:</strong> {{ $filtros }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Total registros:</strong> {{ $total }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Generado:</strong> {{ $fechaGeneracion }}
    </div>

    {{-- ESTADISTICAS --}}
    @php
        $porTipo = $bajas->groupBy(fn($b) => $b->bien?->tipoBien?->nombre_tipo ?? 'Sin tipo');
    @endphp
    <table class="stats-row">
        <tr>
            <td style="width:25%;">
                <div class="stat-label">Total Bajas</div>
                <div class="stat-value">{{ $total }}</div>
                <div class="stat-detail">En este reporte</div>
            </td>
            <td style="width:25%;">
                <div class="stat-label">Tipos de Bien</div>
                <div class="stat-value" style="font-size:14px;">{{ $porTipo->count() }}</div>
                <div class="stat-detail">Categorias distintas</div>
            </td>
            <td style="width:25%;">
                <div class="stat-label">Con Resolucion</div>
                <div class="stat-value" style="font-size:14px;">{{ $bajas->whereNotNull('resolucion')->filter(fn($b) => $b->resolucion !== '')->count() }}</div>
                <div class="stat-detail">Documentadas formalmente</div>
            </td>
            <td style="width:25%;">
                <div class="stat-label">Generado por</div>
                <div class="stat-value" style="font-size:10px; color:#37474f;">{{ $usuario->name }}</div>
                <div class="stat-detail">{{ $usuario->email }}</div>
            </td>
        </tr>
    </table>

    {{-- TABLA --}}
    @if($bajas->count() > 0)
        <table class="main-table">
            <thead>
                <tr>
                    <th class="col-n">#</th>
                    <th class="col-cod">CODIGO PATRIMONIAL</th>
                    <th class="col-den">DENOMINACION</th>
                    <th class="col-tipo">TIPO BIEN</th>
                    <th class="col-fecha">FECHA BAJA</th>
                    <th class="col-mot">MOTIVO DE BAJA</th>
                    <th class="col-res">RESOLUCION</th>
                    <th class="col-obs">OBSERVACION</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bajas as $i => $b)
                    <tr>
                        <td class="col-n">{{ $i + 1 }}</td>
                        <td class="col-cod">
                            <span class="cod-text">{{ $b->bien->codigo_patrimonial ?? '—' }}</span>
                        </td>
                        <td class="col-den">
                            <span class="den-text">{{ $b->bien->denominacion_bien ?? '—' }}</span>
                            @if($b->bien?->marca_bien)
                                <span class="marca-text">{{ $b->bien->marca_bien }}</span>
                            @endif
                        </td>
                        <td class="col-tipo">
                            <span class="badge badge-secondary">
                                {{ $b->bien?->tipoBien?->nombre_tipo ?? '—' }}
                            </span>
                        </td>
                        <td class="col-fecha">
                            {{ $b->fecha_baja ? $b->fecha_baja->format('d/m/Y') : '—' }}
                        </td>
                        <td class="col-mot">
                            <span class="mot-text">{{ Str::limit($b->motivo_baja, 120, '...') }}</span>
                        </td>
                        <td class="col-res">
                            @if($b->resolucion)
                                <span class="res-text">{{ $b->resolucion }}</span>
                            @else
                                <span class="sin-res">No registrada</span>
                            @endif
                        </td>
                        <td class="col-obs">
                            @if($b->observacion)
                                <span class="obs-text">{{ Str::limit($b->observacion, 80, '...') }}</span>
                            @else
                                <span class="sin-res">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- RESUMEN POR TIPO --}}
        @if($porTipo->count() > 1)
        <div style="margin-top:8px; padding:8px; background-color:#fafafa; border:1px solid #b0bec5; font-size:7px;">
            <strong style="color:#37474f;">Bajas por tipo de bien:</strong>
            @foreach($porTipo as $tipo => $items)
                &nbsp;&nbsp;{{ $tipo }}: <strong style="color:#c62828;">{{ $items->count() }}</strong>
            @endforeach
        </div>
        @endif
    @else
        <div class="no-data">No hay registros de baja para los filtros seleccionados.</div>
    @endif

    {{-- PIE --}}
    <div class="footer">
        <p><strong>Generado:</strong> {{ $fechaGeneracion }}</p>
        <p><strong>Por:</strong> {{ $usuario->name }} ({{ $usuario->email }})</p>
        <p><strong>Normativa:</strong> Directiva N 001-2015/SBN — Sistema Nacional de Bienes Estatales</p>
        <div class="footer-d">
            Documento generado automaticamente por GesInventario v2.0.
            La informacion es confidencial y de uso exclusivo de la institucion.
        </div>
    </div>

</body>
</html>
