<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Trazabilidad - {{ $bien->codigo_patrimonial }}</title>
    <style>
        @page {
            margin: 8mm 8mm 12mm 8mm;
        }

        body {
            margin: 0;
            padding: 12px;
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 9px;
            color: #2c3e50;
            line-height: 1.4;
        }

        /* ==============================
           ENCABEZADO
        ============================== */
        .header {
            text-align: center;
            margin-bottom: 16px;
            padding: 12px 0;
            border-bottom: 3px solid #0277bd;
            background-color: #e3f2fd;
        }
        .header .institution {
            font-size: 8px;
            color: #37474f;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 3px;
            font-weight: 700;
        }
        .header h1 {
            font-size: 17px;
            color: #0277bd;
            margin: 5px 0;
            font-weight: 700;
            text-transform: uppercase;
        }
        .header .subtitle {
            font-size: 8px;
            color: #546e7a;
            font-style: italic;
        }

        /* ==============================
           SECCIONES
        ============================== */
        .section { margin-bottom: 14px; page-break-inside: avoid; }

        .section-title {
            background-color: #0277bd;
            color: white;
            padding: 7px 10px;
            font-size: 10px;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ==============================
           TABLA INFORMACION DEL BIEN
        ============================== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table td {
            border: 1px solid #90a4ae;
            padding: 7px 10px;
            font-size: 9px;
        }
        .info-table .label {
            width: 28%;
            font-weight: 700;
            background-color: #eceff1;
            color: #37474f;
        }
        .info-table .value {
            width: 72%;
            background-color: #ffffff;
            color: #263238;
        }
        .info-table .value strong { color: #0277bd; font-size: 10px; }

        /* ==============================
           TABLA DE MOVIMIENTOS
        ============================== */
        .movements-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .movements-table thead th {
            background-color: #37474f;
            color: white;
            border: 1px solid #263238;
            padding: 7px 4px;
            text-align: center;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .movements-table tbody td {
            border: 1px solid #b0bec5;
            padding: 5px 4px;
            font-size: 7px;
            vertical-align: middle;
        }
        .movements-table tbody tr.fila-normal:nth-child(even) { background-color: #f5f5f5; }
        .movements-table tbody tr.fila-normal:nth-child(odd)  { background-color: #ffffff; }

        /* FILA ANULADA */
        .fila-anulada {
            background-color: #fff3f3 !important;
            border-left: 3px solid #c62828;
        }
        .fila-anulada td { color: #78909c; }

        /* ANCHOS DE COLUMNA */
        .col-id       { width: 4%;  text-align: center; font-weight: 700; color: #0277bd; }
        .col-fecha    { width: 9%;  text-align: center; }
        .col-tipo     { width: 12%; text-align: center; }
        .col-usuario  { width: 9%;  }
        .col-area     { width: 12%; text-align: center; font-weight: 700; color: #f57c00; }
        .col-ubicacion{ width: 14%; }
        .col-estado   { width: 8%;  text-align: center; font-size: 6px; }
        .col-doc      { width: 12%; font-size: 6px; }
        .col-detalle  { width: 20%; font-size: 7px; color: #37474f; line-height: 1.3; }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 6px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .badge-alta      { background-color: #2196f3; color: white; }
        .badge-asignacion{ background-color: #4caf50; color: white; }
        .badge-baja      { background-color: #f44336; color: white; }
        .badge-reversion { background-color: #00bcd4; color: white; }
        .badge-otro      { background-color: #9e9e9e; color: white; }
        .badge-anulado   { background-color: #c62828; color: white; margin-top: 2px; display: block; }

        /* TEXTOS */
        .area-text   { color: #f57c00; font-weight: 700; font-size: 7px; }
        .area-none   { color: #9e9e9e; font-style: italic; }
        .doc-name    { color: #1b5e20; font-weight: 600; font-size: 6px; word-wrap: break-word; line-height: 1.3; }
        .doc-none    { color: #d32f2f; font-weight: 700; text-align: center; }
        .motivo-text { color: #263238; font-weight: 600; line-height: 1.3; word-wrap: break-word; }
        .motivo-none { color: #9e9e9e; font-style: italic; text-align: center; }
        .motivo-anulacion { color: #c62828; font-style: italic; font-size: 6.5px; display: block; margin-top: 2px; line-height: 1.3; }

        /* ==============================
           ESTADISTICAS
        ============================== */
        .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .stats-table td {
            border: 2px solid #0277bd;
            padding: 10px;
            text-align: center;
            background-color: #e3f2fd;
        }
        .stat-label  { font-size: 8px; color: #37474f; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 4px; font-weight: 700; }
        .stat-value  { font-size: 18px; font-weight: 700; color: #0277bd; }
        .stat-detail { font-size: 7px; color: #546e7a; margin-top: 3px; font-style: italic; }

        /* RESUMEN POR TIPO */
        .summary-box {
            padding: 10px;
            background-color: #fafafa;
            border: 2px solid #90a4ae;
            margin-top: 8px;
        }
        .summary-title { font-weight: 700; margin-bottom: 6px; color: #37474f; font-size: 9px; text-transform: uppercase; }
        .summary-box ul { margin: 0; padding-left: 16px; list-style: square; }
        .summary-box li { margin: 3px 0; font-size: 8px; color: #263238; }
        .li-anulado   { color: #c62828; }

        /* AVISO ANULADOS */
        .aviso-anulados {
            background-color: #fff3f3;
            border: 1px solid #c62828;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-size: 7.5px;
            color: #c62828;
            font-weight: 700;
        }

        /* SIN DATOS */
        .no-data {
            text-align: center; padding: 30px; color: #9e9e9e;
            font-style: italic; background-color: #fafafa;
            border: 2px dashed #bdbdbd;
        }

        /* PIE DE PAGINA */
        .footer {
            margin-top: 18px; padding-top: 8px;
            border-top: 2px solid #90a4ae;
            font-size: 7px; color: #546e7a;
        }
        .footer p { margin: 2px 0; line-height: 1.5; }
        .footer-disclaimer {
            margin-top: 8px; text-align: center; font-style: italic;
            color: #9e9e9e; font-size: 6px; padding: 5px;
            background-color: #fafafa;
        }
    </style>
</head>
<body>

    {{-- ENCABEZADO --}}
    <div class="header">
        <div class="institution">Instituto de Educación Superior Tecnológico Público</div>
        <h1>HISTORIAL DE TRAZABILIDAD</h1>
        <div class="subtitle">Sistema de Gestión de Inventario - {{ config('app.name', 'GesInventario') }}</div>
    </div>

    {{-- INFORMACION DEL BIEN --}}
    <div class="section">
        <div class="section-title">INFORMACION DEL BIEN</div>
        <table class="info-table">
            <tr>
                <td class="label">Codigo Patrimonial:</td>
                <td class="value"><strong>{{ $bien->codigo_patrimonial }}</strong></td>
            </tr>
            <tr>
                <td class="label">Denominacion:</td>
                <td class="value">{{ $bien->denominacion_bien }}</td>
            </tr>
            <tr>
                <td class="label">Tipo de Bien:</td>
                <td class="value">{{ $bien->tipoBien ? $bien->tipoBien->nombre_tipo : 'No especificado' }}</td>
            </tr>
            <tr>
                <td class="label">Periodo Consultado:</td>
                <td class="value"><strong>{{ $periodo }}</strong></td>
            </tr>
        </table>
    </div>

    {{-- HISTORIAL DE MOVIMIENTOS --}}
    <div class="section">
        <div class="section-title">HISTORIAL DE MOVIMIENTOS</div>

        @php
            $totalAnulados = $movimientos->where('anulado', true)->count();
        @endphp

        @if($totalAnulados > 0)
            <div class="aviso-anulados">
                AVISO: Este historial incluye {{ $totalAnulados }} movimiento(s) ANULADO(S) (marcados con fondo rosado).
                Los movimientos anulados son registros de errores corregidos, conservados para auditoria segun normativa SBN/MEF.
            </div>
        @endif

        @if($movimientos->count() > 0)
            <table class="movements-table">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-fecha">FECHA</th>
                        <th class="col-tipo">TIPO</th>
                        <th class="col-usuario">USUARIO</th>
                        <th class="col-area">AREA</th>
                        <th class="col-ubicacion">UBICACION</th>
                        <th class="col-estado">ESTADO CONSERV.</th>
                        <th class="col-doc">DOCUMENTO</th>
                        <th class="col-detalle">DETALLE / MOTIVO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movimientos as $mov)
                        @php
                            $esAnulado  = (bool) $mov->anulado;
                            $tipoStr    = strtoupper($mov->tipoMovimiento->tipo_mvto ?? '-');
                            $badgeClass = 'badge-otro';
                            if (str_contains($tipoStr, 'ALTA') || str_contains($tipoStr, 'REGISTRO'))
                                $badgeClass = 'badge-alta';
                            elseif (str_contains($tipoStr, 'ASIGNACI'))
                                $badgeClass = 'badge-asignacion';
                            elseif (str_contains($tipoStr, 'BAJA'))
                                $badgeClass = 'badge-baja';
                            elseif (str_contains($tipoStr, 'REVERS'))
                                $badgeClass = 'badge-reversion';

                            $estadoConserv = $mov->estadoConservacion->nombre_conservacion
                                             ?? ($mov->estadoConservacion->nombre_estado ?? '-');

                            $documento = $mov->documentoSustento;
                            $nombreDoc = null;
                            if ($documento) {
                                $td = $documento->tipo_documento ?? null;
                                $nd = $documento->numero_documento ?? null;
                                $nombreDoc = $td && $nd ? "{$td} - {$nd}" : ($nd ?? $td);
                            }
                        @endphp

                        <tr class="{{ $esAnulado ? 'fila-anulada' : 'fila-normal' }}">

                            {{-- ID --}}
                            <td class="col-id">{{ $mov->id_movimiento }}</td>

                            {{-- FECHA --}}
                            <td class="col-fecha">
                                <strong>{{ \Carbon\Carbon::parse($mov->fecha_mvto)->format('d/m/Y') }}</strong><br>
                                <small style="color:#78909c;">{{ \Carbon\Carbon::parse($mov->fecha_mvto)->format('H:i') }}</small>
                            </td>

                            {{-- TIPO --}}
                            <td class="col-tipo">
                                <span class="badge {{ $badgeClass }}">{{ $tipoStr }}</span>
                                @if($esAnulado)
                                    <span class="badge badge-anulado">ANULADO</span>
                                @endif
                            </td>

                            {{-- USUARIO --}}
                            <td class="col-usuario">{{ $mov->usuario->name ?? '-' }}</td>

                            {{-- AREA --}}
                            <td class="col-area">
                                @if($mov->ubicacion && $mov->ubicacion->area)
                                    <span class="{{ $esAnulado ? '' : 'area-text' }}" style="{{ $esAnulado ? 'color:#9e9e9e;text-decoration:line-through;' : '' }}">
                                        {{ $mov->ubicacion->area->nombre_area }}
                                    </span>
                                @else
                                    <span class="area-none">-</span>
                                @endif
                            </td>

                            {{-- UBICACION --}}
                            <td class="col-ubicacion" style="{{ $esAnulado ? 'color:#9e9e9e;text-decoration:line-through;' : '' }}">
                                {{ $mov->ubicacion ? $mov->ubicacion->ambiente : '-' }}
                            </td>

                            {{-- ESTADO CONSERVACION --}}
                            <td class="col-estado">
                                <strong style="color:#37474f;">{{ $estadoConserv }}</strong>
                            </td>

                            {{-- DOCUMENTO --}}
                            <td class="col-doc">
                                @if($nombreDoc)
                                    <span class="doc-name">{{ $nombreDoc }}</span>
                                @else
                                    <span class="doc-none">Sin doc</span>
                                @endif
                            </td>

                            {{-- DETALLE / MOTIVO --}}
                            <td class="col-detalle">
                                @if($esAnulado)
                                    {{-- Si está anulado: mostrar el motivo de anulación como prioritario --}}
                                    @if($mov->motivo_anulacion)
                                        <span class="motivo-anulacion">
                                            [ANULACION] {{ Str::limit($mov->motivo_anulacion, 100, '...') }}
                                        </span>
                                    @else
                                        <span class="motivo-none">Anulado sin motivo registrado</span>
                                    @endif
                                @elseif($mov->detalle_tecnico)
                                    <span class="motivo-text">{{ Str::limit($mov->detalle_tecnico, 100, '...') }}</span>
                                @else
                                    <span class="motivo-none">Sin detalle</span>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                <p>No hay movimientos registrados para este bien en el periodo seleccionado.</p>
            </div>
        @endif
    </div>

    {{-- ESTADISTICAS --}}
    @php
        $movimientosValidos  = $movimientos->where('anulado', false);
        $movimientosAnulados = $movimientos->where('anulado', true);
    @endphp
    @if($movimientos->count() > 0)
        <div class="section">
            <div class="section-title">ESTADISTICAS</div>

            <table class="stats-table">
                <tr>
                    <td style="width:33%;">
                        <div class="stat-label">Total de Movimientos</div>
                        <div class="stat-value">{{ $estadisticas['total_movimientos'] }}</div>
                        <div class="stat-detail">Incluyendo anulados</div>
                    </td>
                    <td style="width:33%;">
                        <div class="stat-label">Movimientos Validos</div>
                        <div class="stat-value" style="color:#2e7d32;">{{ $movimientosValidos->count() }}</div>
                        <div class="stat-detail">Sin contar anulados</div>
                    </td>
                    <td style="width:34%;">
                        <div class="stat-label">Ultimo Movimiento Valido</div>
                        <div class="stat-value" style="font-size:12px;">
                            @if($movimientosValidos->first())
                                {{ \Carbon\Carbon::parse($movimientosValidos->first()->fecha_mvto)->format('d/m/Y H:i') }}
                            @else
                                N/A
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

            <div class="summary-box">
                <div class="summary-title">Movimientos por Tipo</div>
                <ul>
                    @foreach($estadisticas['tipos'] as $tipo => $cantidad)
                        <li><strong>{{ $tipo }}:</strong> {{ $cantidad }} {{ $cantidad == 1 ? 'movimiento' : 'movimientos' }}</li>
                    @endforeach
                    @if($movimientosAnulados->count() > 0)
                        <li class="li-anulado">
                            <strong>ANULADOS:</strong> {{ $movimientosAnulados->count() }}
                            {{ $movimientosAnulados->count() == 1 ? 'movimiento (error corregido)' : 'movimientos (errores corregidos)' }}
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    @endif

    {{-- PIE DE PAGINA --}}
    <div class="footer">
        <p><strong>Generado el:</strong> {{ $fechaGeneracion }}</p>
        <p><strong>Por:</strong> {{ $usuario->name }} ({{ $usuario->email }})</p>
        <p><strong>Sistema:</strong> GesInventario v2.0</p>

        <div class="footer-disclaimer">
            Este documento ha sido generado automaticamente por el Sistema de Gestion de Inventario.<br>
            La informacion es confidencial y de uso exclusivo de la institucion.<br>
            Los movimientos ANULADOS se conservan por exigencia normativa (Directiva N 0006-2021-EF/54.01 - MEF/DGA).
        </div>
    </div>

</body>
</html>
