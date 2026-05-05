<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Inventario {{ $inventario->codigoinventario }}</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        /* --- CABECERA PROFESIONAL --- */
        .header-container {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 10px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-cell {
            width: 20%;
            text-align: left;
        }
        .info-cell {
            width: 60%;
            text-align: center;
        }
        .doc-cell {
            width: 20%;
            text-align: right;
            vertical-align: top;
        }
        .inst-name {
            font-size: 14px;
            font-weight: bold;
            color: #1a237e;
            text-transform: uppercase;
            margin: 0;
        }
        .inst-ruc {
            font-size: 11px;
            color: #555;
            margin: 2px 0;
        }
        .acta-title {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 10px;
            color: #000;
        }

        /* --- SECCIONES --- */
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            background-color: #f0f2f5;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 11px;
            border-left: 4px solid #1a237e;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        /* --- TABLAS --- */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.data-table th {
            background-color: #1a237e;
            color: #ffffff;
            padding: 6px 4px;
            text-align: left;
            font-size: 9px;
            border: 1px solid #1a237e;
        }
        table.data-table td {
            padding: 5px 4px;
            border: 1px solid #e0e0e0;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* --- RESUMEN --- */
        .stats-grid {
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-box {
            border: 1px solid #ccc;
            text-align: center;
            padding: 8px;
            width: 19%;
        }
        .stat-val {
            font-size: 16px;
            font-weight: bold;
            display: block;
        }
        .stat-lbl {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }

        /* --- TEXTOS --- */
        .legal-text {
            font-size: 9px;
            text-align: justify;
            margin-top: 10px;
            font-style: italic;
        }

        /* --- FIRMAS --- */
        .signature-section {
            margin-top: 50px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
        }
        .signature-box {
            width: 33%;
            text-align: center;
            padding: 0 15px;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin-bottom: 5px;
        }
        .sig-name {
            font-weight: bold;
            font-size: 9px;
        }
        .sig-role {
            font-size: 8px;
            color: #444;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #888;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <!-- CABECERA -->
    <div class="header-container">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if(isset($settings['logo_reportes_path']))
                        {{-- Nota: Para DomPDF se recomienda usar rutas absolutas o base64 si es posible --}}
                        {{-- <img src="{{ public_path('storage/'.$settings['logo_reportes_path']) }}" height="50"> --}}
                    @endif
                </td>
                <td class="info-cell">
                    <div class="inst-name">{{ $settings['nombre_institucion'] ?? 'Sistema de Gestión de Inventarios' }}</div>
                    <div class="inst-ruc">RUC: {{ $settings['ruc'] ?? '----------' }}</div>
                    <div class="acta-title">ACTA DE TOMA FÍSICA DE INVENTARIO</div>
                </td>
                <td class="doc-cell">
                    <div style="border: 1px solid #000; padding: 5px; text-align: center;">
                        <div style="font-size: 8px;">CÓDIGO ACTA</div>
                        <div style="font-size: 11px; font-weight: bold;">{{ $inventario->codigoinventario }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- DATOS GENERALES -->
    <div class="section">
        <div class="section-title">1. Datos del Inventario</div>
        <table class="data-table" style="border: none;">
            <tr>
                <td style="border: none; width: 50%;">
                    <strong>Tipo de Inventario:</strong> {{ $inventario->tipoinventario }}<br>
                    <strong>Responsable del Área:</strong> 
                    {{ $inventario->responsablePersona ? $inventario->responsablePersona->nombre_responsable . ' ' . $inventario->responsablePersona->apellidos_responsable : 'No asignado' }}<br>
                    <strong>DNI Responsable:</strong> {{ $inventario->responsable ?? '---' }}
                </td>
                <td style="border: none; width: 50%;">
                    <strong>Fecha de Inicio:</strong> {{ $inventario->fecha_inicio ? $inventario->fecha_inicio->format('d/m/Y') : '---' }}<br>
                    <strong>Fecha de Cierre:</strong> {{ $inventario->fechacierre ? $inventario->fechacierre->format('d/m/Y') : '---' }}<br>
                    <strong>Usuario Auditor:</strong> {{ $inventario->usuarioCierre->name ?? '---' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- METODOLOGÍA -->
    <div class="section">
        <div class="section-title">2. Introducción y Metodología</div>
        <div style="font-size: 9px; text-align: justify;">
            El presente inventario se realiza con el propósito de verificar la existencia física, estado de conservación y ubicación de los bienes muebles patrimoniales. 
            La metodología aplicada consistió en la verificación "ítem por ítem" mediante el escaneo de códigos patrimoniales y la inspección ocular directa, contrastando los hallazgos con los registros del sistema institucional al corte del día {{ $inventario->fecha_inicio ? $inventario->fecha_inicio->format('d/m/Y') : 'inaugural' }}.
        </div>
    </div>

    <!-- RESUMEN EJECUTIVO -->
    <div class="section">
        <div class="section-title">3. Resumen de Hallazgos</div>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td class="stat-box">
                    <span class="stat-val">{{ $estadisticas['total_esperados'] ?? 0 }}</span>
                    <span class="stat-lbl">Esperados</span>
                </td>
                <td style="width: 1%;"></td>
                <td class="stat-box" style="border-color: #2e7d32;">
                    <span class="stat-val" style="color: #2e7d32;">{{ $estadisticas['total_verificados'] ?? 0 }}</span>
                    <span class="stat-lbl">Verificados</span>
                </td>
                <td style="width: 1%;"></td>
                <td class="stat-box" style="border-color: #1565c0;">
                    <span class="stat-val" style="color: #1565c0;">{{ $estadisticas['verificados_conformes'] ?? 0 }}</span>
                    <span class="stat-lbl">Conformes</span>
                </td>
                <td style="width: 1%;"></td>
                <td class="stat-box" style="border-color: #c62828;">
                    <span class="stat-val" style="color: #c62828;">{{ $estadisticas['total_faltantes'] ?? 0 }}</span>
                    <span class="stat-lbl">Faltantes</span>
                </td>
                <td style="width: 1%;"></td>
                <td class="stat-box" style="border-color: #ef6c00;">
                    <span class="stat-val" style="color: #ef6c00;">{{ $estadisticas['total_sobrantes'] ?? 0 }}</span>
                    <span class="stat-lbl">Ubic. Diferente</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- BIENES CON DIFERENCIA DE UBICACIÓN -->
    @php
        $sobrantes = $inventario->detalles->filter(function($d) {
            $originalUbicId = $d->movimiento ? $d->movimiento->idubicacion : null;
            $detectadaUbicId = $d->ubicaciondetectada;
            return ($detectadaUbicId && $originalUbicId != $detectadaUbicId);
        });
    @endphp

    @if($sobrantes->count() > 0)
    <div class="section">
        <div class="section-title">4. Bienes Detectados en Ubicación Distinta</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Código</th>
                    <th style="width: 33%;">Denominación</th>
                    <th style="width: 25%;">Ubicación Sistema</th>
                    <th style="width: 25%;">Ubicación Física Hallada</th>
                    <th style="width: 5%;">Est.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sobrantes as $d)
                <tr>
                    <td><strong>{{ $d->movimiento->bien->codigo_patrimonial }}</strong></td>
                    <td>{{ $d->movimiento->bien->denominacion_bien }}</td>
                    <td style="color: #666;">{{ $d->movimiento->ubicacion->area->nombre_area ?? '---' }} ({{ $d->movimiento->ubicacion->ambiente ?? '---' }})</td>
                    <td style="font-weight: bold; color: #1a237e;">{{ $d->ubicacionDetectada->area->nombre_area ?? '---' }} ({{ $d->ubicacionDetectada->ambiente ?? '---' }})</td>
                    <td>{{ substr($d->estadoConservacion->nombre_conservacion ?? '-', 0, 1) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- BIENES FALTANTES -->
    @if(count($estadisticas['bienes_faltantes'] ?? []) > 0)
    <div class="section">
        <div class="section-title">5. Bienes Faltantes (No hallados en la toma física)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Código</th>
                    <th style="width: 55%;">Denominación / Descripción</th>
                    <th style="width: 30%;">Tipo de Bien</th>
                </tr>
            </thead>
            <tbody>
                @foreach($estadisticas['bienes_faltantes'] as $b)
                <tr>
                    <td><strong>{{ $b['codigo_patrimonial'] }}</strong></td>
                    <td>{{ $b['denominacion_bien'] }}</td>
                    <td>{{ $b['tipo_bien'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- CONCLUSIONES Y FIRMAS -->
    <div class="section">
        <div class="section-title">6. Certificación y Cierre</div>
        <div class="legal-text">
            Siendo las {{ now()->format('H:i') }} horas del día {{ now()->format('d/m/Y') }}, se da por concluida la presente acta, manifestando los firmantes su plena conformidad con los resultados físicos aquí detallados. Se deja constancia que la información presentada refleja fielmente la realidad física encontrada a la fecha del corte. Cualquier discrepancia posterior será responsabilidad de los custodios asignados.
        </div>
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td class="signature-box">
                    <div class="sig-line"></div>
                    <div class="sig-name">
                        {{ $inventario->responsablePersona ? strtoupper($inventario->responsablePersona->nombre_responsable . ' ' . $inventario->responsablePersona->apellidos_responsable) : '___________________________' }}
                    </div>
                    <div class="sig-role">RESPONSABLE DEL ÁREA / CUSTODIO</div>
                </td>
                <td class="signature-box">
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ strtoupper($inventario->usuarioCierre->name ?? '___________________________') }}</div>
                    <div class="sig-role">ADMINISTRADOR DE INVENTARIO / AUDITOR</div>
                </td>
                <td class="signature-box">
                    <div class="sig-line"></div>
                    <div class="sig-name">___________________________</div>
                    <div class="sig-role">TESTIGO / JEFE DE ALMACÉN</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ $settings['nombre_institucion'] ?? 'Sistema de Gestión de Inventarios' }} - Generado el {{ $fecha_actual }} - Página 1 de 1
    </div>

</body>
</html>
