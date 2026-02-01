<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Trazabilidad - {{ $bien->codigo_patrimonial }}</title>
    <style>
        /* ========================================
           ⭐ CONFIGURACIÓN DE PÁGINA
        ======================================== */
        @page {
            margin: 5mm 5mm 5mm 5mm; /* ⭐ Superior, Derecha, Inferior, Izquierda */
        }

        /* ⭐ Márgenes adicionales en el body para mayor seguridad */
        body {
            margin: 0;
            padding: 15px; /* ⭐ Padding interno para separar del borde */
        }


        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 9px;
            color: #2c3e50;
            line-height: 1.4;
        }

        /* ========================================
           📋 ENCABEZADO
        ======================================== */
        .header {
            text-align: center;
            margin-bottom: 18px;
            padding: 14px 0;
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
            font-size: 18px;
            color: #0277bd;
            margin: 6px 0;
            font-weight: 700;
        }

        .header .subtitle {
            font-size: 8px;
            color: #546e7a;
            font-style: italic;
        }

        /* ========================================
           📦 SECCIONES
        ======================================== */
        .section {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #0277bd;
            color: white;
            padding: 8px 10px;
            font-size: 10px;
            font-weight: 700;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ========================================
           📊 INFORMACIÓN DEL BIEN (TABLA CON BORDES)
        ======================================== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .info-table td {
            border: 1px solid #90a4ae;
            padding: 8px 10px;
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

        .info-table .value strong {
            color: #0277bd;
            font-size: 10px;
        }

        /* ========================================
           📋 TABLA DE MOVIMIENTOS (CON BORDES COMPLETOS)
        ======================================== */
        .movements-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        /* ⭐ ENCABEZADO DE LA TABLA */
        .movements-table thead th {
            background-color: #37474f;
            color: white;
            border: 1px solid #263238;
            padding: 8px 5px;
            text-align: center;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ⭐ CELDAS DE LA TABLA (CON BORDES) */
        .movements-table tbody td {
            border: 1px solid #b0bec5; /* ⭐ BORDE EN TODAS LAS CELDAS */
            padding: 7px 5px;
            font-size: 8px;
            vertical-align: middle;
        }

        /* Filas alternadas */
        .movements-table tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        .movements-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        /* ⭐ ANCHOS DE COLUMNA OPTIMIZADOS */
        .col-id {
            width: 5%;
            text-align: center;
            font-weight: 700;
            color: #0277bd;
        }
        .col-fecha {
            width: 11%;
            text-align: center;
        }
        .col-tipo {
            width: 14%;
            text-align: center;
        }
        .col-usuario {
            width: 12%;
        }
        .col-ubicacion {
            width: 26%;
        }
        .col-estado {
            width: 10%;
            text-align: center;
        }
        .col-doc {
            width: 22%; /* ⭐ Ancho para nombre completo del documento */
            font-size: 7px;
        }

        /* ========================================
           🏷️ BADGES
        ======================================== */
        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .badge-primary { background-color: #2196f3; color: white; }
        .badge-success { background-color: #4caf50; color: white; }
        .badge-danger { background-color: #f44336; color: white; }
        .badge-info { background-color: #00bcd4; color: white; }
        .badge-secondary { background-color: #9e9e9e; color: white; }

        /* ⭐⭐⭐ ESTILO PARA NOMBRE DE DOCUMENTO ⭐⭐⭐ */
        .doc-name {
            color: #1b5e20;
            font-weight: 600;
            font-size: 7px;
            display: block;
            word-wrap: break-word;
            line-height: 1.3;
        }

        .doc-none {
            color: #d32f2f;
            font-weight: 700;
            text-align: center;
        }

        /* ========================================
           📊 ESTADÍSTICAS (TABLA CON BORDES)
        ======================================== */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .stats-table td {
            border: 2px solid #0277bd;
            padding: 12px;
            text-align: center;
            background-color: #e3f2fd;
        }

        .stat-label {
            font-size: 8px;
            color: #37474f;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .stat-value {
            font-size: 18px;
            font-weight: 700;
            color: #0277bd;
        }

        .stat-detail {
            font-size: 7px;
            color: #546e7a;
            margin-top: 3px;
            font-style: italic;
        }

        /* ========================================
           📋 RESUMEN POR TIPO (CON BORDES)
        ======================================== */
        .summary-box {
            padding: 10px;
            background-color: #fafafa;
            border: 2px solid #90a4ae;
            margin-top: 10px;
        }

        .summary-title {
            font-weight: 700;
            margin-bottom: 7px;
            color: #37474f;
            font-size: 9px;
            text-transform: uppercase;
        }

        .summary-box ul {
            margin: 0;
            padding-left: 16px;
            list-style: square;
        }

        .summary-box li {
            margin: 3px 0;
            font-size: 8px;
            color: #263238;
        }

        /* ========================================
           🚫 SIN DATOS
        ======================================== */
        .no-data {
            text-align: center;
            padding: 30px;
            color: #9e9e9e;
            font-style: italic;
            background-color: #fafafa;
            border: 2px dashed #bdbdbd;
        }

        /* ========================================
           📄 PIE DE PÁGINA
        ======================================== */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #90a4ae;
            font-size: 7px;
            color: #546e7a;
        }

        .footer p {
            margin: 2px 0;
            line-height: 1.5;
        }

        .footer-disclaimer {
            margin-top: 8px;
            text-align: center;
            font-style: italic;
            color: #9e9e9e;
            font-size: 6px;
            padding: 5px;
            background-color: #fafafa;
        }
    </style>
</head>
<body>
    {{-- ========================================
         📋 ENCABEZADO
    ======================================== --}}
    <div class="header">
        <div class="institution">Instituto de Educación Superior Tecnológico Público</div>
        <h1>HISTORIAL DE TRAZABILIDAD</h1>
        <div class="subtitle">Sistema de Gestión de Inventario - {{ config('app.name', 'GesInventario') }}</div>
    </div>

    {{-- ========================================
         📦 INFORMACIÓN DEL BIEN (CON BORDES)
    ======================================== --}}
    <div class="section">
        <div class="section-title">🔍 Información del Bien</div>
        <table class="info-table">
            <tr>
                <td class="label">Código Patrimonial:</td>
                <td class="value"><strong>{{ $bien->codigo_patrimonial }}</strong></td>
            </tr>
            <tr>
                <td class="label">Denominación:</td>
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

    {{-- ========================================
         📋 HISTORIAL DE MOVIMIENTOS (CON BORDES)
    ======================================== --}}
    <div class="section">
        <div class="section-title">Historial de Movimientos</div>

        @if($movimientos->count() > 0)
            <table class="movements-table">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-fecha">FECHA/HORA</th>
                        <th class="col-tipo">TIPO</th>
                        <th class="col-usuario">USUARIO</th>
                        <th class="col-ubicacion">UBICACIÓN</th>
                        <th class="col-estado">ESTADO</th>
                        <th class="col-doc">DOCUMENTO SUSTENTO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movimientos as $mov)
                        <tr>
                            {{-- ID --}}
                            <td class="col-id">{{ $mov->id_movimiento }}</td>

                            {{-- FECHA/HORA --}}
                            <td class="col-fecha">
                                <strong>{{ \Carbon\Carbon::parse($mov->fecha_mvto)->format('d/m/Y') }}</strong><br>
                                <small style="color: #78909c;">{{ \Carbon\Carbon::parse($mov->fecha_mvto)->format('H:i') }}</small>
                            </td>

                            {{-- TIPO MOVIMIENTO --}}
                            <td class="col-tipo">
                                @php
                                    $tipo = strtoupper($mov->tipoMovimiento->tipo_mvto ?? '-');
                                    $badgeClass = 'badge-secondary';
                                    if (str_contains($tipo, 'REGISTRO')) $badgeClass = 'badge-primary';
                                    elseif (str_contains($tipo, 'ASIGNACI')) $badgeClass = 'badge-success';
                                    elseif (str_contains($tipo, 'BAJA')) $badgeClass = 'badge-danger';
                                    elseif (str_contains($tipo, 'REVERS')) $badgeClass = 'badge-info';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $tipo }}</span>
                            </td>

                            {{-- USUARIO --}}
                            <td class="col-usuario">{{ $mov->usuario->name ?? '-' }}</td>

                            {{-- UBICACIÓN --}}
                            <td class="col-ubicacion">{{ $mov->ubicacion ? $mov->ubicacion->nombre_sede : '-' }}</td>

                            {{-- ESTADO --}}
                            <td class="col-estado">
                                <strong style="color: #37474f;">
                                    {{ $mov->estadoConservacion ? $mov->estadoConservacion->nombre_estado : '-' }}
                                </strong>
                            </td>

                            {{-- ⭐⭐⭐ DOCUMENTO SUSTENTO - VERSIÓN CORREGIDA FINAL ⭐⭐⭐ --}}
                            <td class="col-doc">
                                @php
                                    // Obtener el documento
                                    $documento = $mov->documentoSustento;
                                    $nombreDocumento = null;

                                    if ($documento) {
                                        // ⭐ CONSTRUIR NOMBRE DESDE LOS CAMPOS DISPONIBLES
                                        // Tu tabla solo tiene: tipo_documento, numero_documento, fecha_documento
                                        // Formato: "TIPO - NUMERO" (Ej: "GUIA REMISION - DOC1", "OTRO - ASDSD")

                                        $tipo = $documento->tipo_documento ?? null;
                                        $numero = $documento->numero_documento ?? null;

                                        if ($tipo && $numero) {
                                            // Ambos campos disponibles
                                            $nombreDocumento = "{$tipo} - {$numero}";
                                        } elseif ($numero) {
                                            // Solo número
                                            $nombreDocumento = $numero;
                                        } elseif ($tipo) {
                                            // Solo tipo
                                            $nombreDocumento = $tipo;
                                        }
                                    }

                                    // Verificar si hay FK aunque no se cargó la relación
                                    $tieneFk = !empty($mov->documento_sustentatorio);
                                @endphp

                                @if($nombreDocumento)
                                    {{-- ✅ CASO 1: Hay nombre de documento (ÉXITO) --}}
                                    <span class="doc-name">
                                        {{ $nombreDocumento }}
                                    </span>

                                @elseif($documento)
                                    {{-- ⚠️ CASO 2: Hay documento pero sin datos (mostrar ID) --}}
                                    <span style="color: #e65100; font-size: 7px; font-weight: 600; line-height: 1.2;">
                                        📎 Doc. ID: {{ $documento->id_documento }}<br>
                                        <small style="font-size: 6px; color: #666;">(sin información)</small>
                                    </span>

                                @elseif($tieneFk)
                                    {{-- ⚠️ CASO 3: Hay FK pero relación no cargó (error de carga) --}}
                                    <span style="color: #f57c00; font-size: 7px; font-weight: 600; line-height: 1.2;">
                                        📋 Ref: {{ $mov->documento_sustentatorio }}<br>
                                        <small style="font-size: 6px; color: #666;">(no cargado)</small>
                                    </span>

                                @else
                                    {{-- ❌ CASO 4: No hay documento asignado --}}
                                    <span class="doc-none">✗ Sin documento</span>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                <p>📭 No hay movimientos registrados para este bien en el periodo seleccionado</p>
            </div>
        @endif
    </div>

    {{-- ========================================
         📊 ESTADÍSTICAS (CON BORDES)
    ======================================== --}}
    @if($movimientos->count() > 0)
        <div class="section">
            <div class="section-title">Estadísticas</div>

            <table class="stats-table">
                <tr>
                    <td style="width: 50%;">
                        <div class="stat-label">Total de Movimientos</div>
                        <div class="stat-value">{{ $estadisticas['total_movimientos'] }}</div>
                        <div class="stat-detail">Registrados en el sistema</div>
                    </td>
                    <td style="width: 50%;">
                        <div class="stat-label">Último Movimiento</div>
                        <div class="stat-value" style="font-size: 13px;">
                            {{ $estadisticas['ultimo_movimiento'] ? \Carbon\Carbon::parse($estadisticas['ultimo_movimiento'])->format('d/m/Y H:i') : 'N/A' }}
                        </div>
                        <div class="stat-detail">
                            {{ $estadisticas['ultimo_movimiento'] ? \Carbon\Carbon::parse($estadisticas['ultimo_movimiento'])->diffForHumans() : '' }}
                        </div>
                    </td>
                </tr>
            </table>

            @if($estadisticas['tipos']->count() > 0)
                <div class="summary-box">
                    <div class="summary-title">Movimientos por Tipo</div>
                    <ul>
                        @foreach($estadisticas['tipos'] as $tipo => $cantidad)
                            <li>
                                <strong>{{ $tipo }}:</strong> {{ $cantidad }}
                                {{ $cantidad == 1 ? 'movimiento' : 'movimientos' }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    {{-- ========================================
         📄 PIE DE PÁGINA
    ======================================== --}}
    <div class="footer">
        <p><strong>Generado el:</strong> {{ $fechaGeneracion }}</p>
        <p><strong>Por:</strong> {{ $usuario->name }} ({{ $usuario->email }})</p>
        <p><strong>Sistema:</strong> GesInventario v2.0</p>

        <div class="footer-disclaimer">
            Este documento ha sido generado automáticamente por el Sistema de Gestión de Inventario.<br>
            La información es confidencial y de uso exclusivo de la institución.
        </div>
    </div>
</body>
</html>
