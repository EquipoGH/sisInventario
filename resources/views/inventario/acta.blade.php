<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Conciliación Física - {{ $inventario->codigoinventario }}</title>
    <style>
        @page {
            margin: 2cm 1.5cm;
            @bottom-right {
                content: "Página " counter(page) " de " counter(pages);
                font-size: 9px;
                font-family: Arial, sans-serif;
                color: #555;
            }
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            color: #111;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .text-justify { text-align: justify; }
        .uppercase { text-transform: uppercase; }

        /* --- CABECERA --- */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .inst-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .inst-ruc {
            font-size: 10px;
            color: #444;
        }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: underline;
            margin-bottom: 20px;
        }
        .doc-subtitle {
            font-size: 12px;
            text-align: center;
            margin-top: -15px;
            margin-bottom: 25px;
            font-weight: bold;
        }

        /* --- PÁRRAFOS LEGALES --- */
        .legal-text {
            font-size: 11.5px;
            line-height: 1.6;
            text-align: justify;
            margin-bottom: 15px;
        }

        /* --- TABLAS DE DATOS --- */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table th, .info-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }
        .info-table th {
            background-color: #f0f0f0;
            font-size: 10px;
            text-transform: uppercase;
            width: 30%;
            text-align: left;
        }
        .info-table td {
            font-size: 11px;
        }

        /* --- CUADRO ESTADÍSTICO --- */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .stats-table th, .stats-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        .stats-table th {
            background-color: #e0e0e0;
            font-size: 10px;
            text-transform: uppercase;
        }
        .stats-table td {
            font-size: 13px;
            font-weight: bold;
        }

        /* --- TABLAS ANEXOS --- */
        .anexo-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
            text-decoration: underline;
            page-break-after: avoid;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 9px;
            text-align: left;
        }
        .data-table th {
            background-color: #f0f0f0;
            text-transform: uppercase;
        }

        /* --- FIRMAS --- */
        .signatures {
            width: 100%;
            margin-top: 60px;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 10px;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin: 0 auto 5px auto;
            width: 85%;
        }
        .sig-name {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .sig-role {
            font-size: 9px;
            color: #333;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    @php
        // Preparación de datos consolidados
        $incidenciasMap = $inventario->incidencias->groupBy(fn($i) => $i->id_bien ?? 'sc_'.$i->id_incidencia);
        
        // Bienes Sobrantes (Hallados físicamente pero no pertenecían al alcance original)
        $sobrantesIds = $estadisticas['sobrantes_ids'] ?? [];
        $sobrantesFinal = collect();
        if(!empty($sobrantesIds)) {
            $sobrantesFinal = \App\Models\Bien::with('tipoBien')->whereIn('id_bien', $sobrantesIds)->get();
        }

        // Bienes Faltantes
        $faltantesIds = $estadisticas['faltantes_ids'] ?? [];
        $faltantesFinal = collect();
        if (!empty($faltantesIds)) {
            $faltantesFinal = \App\Models\Bien::with('tipoBien')->whereIn('id_bien', $faltantesIds)->get();
        }

        // Bienes con Alertas (Deteriorados o Ubicaciones Diferentes que sí estaban en el alcance)
        $alertasFinal = collect();
        foreach($inventario->detalles as $det) {
            if (!$det->movimiento || !$det->movimiento->bien) continue;
            // Omitir si es sobrante o faltante (ya están en otras listas)
            if (in_array($det->movimiento->idbien, $sobrantesIds)) continue;
            if (in_array($det->movimiento->idbien, $faltantesIds)) continue;

            $esUbicDiferente = ($det->ubicaciondetectada && $det->movimiento->idubicacion != $det->ubicaciondetectada);
            $esDeteriorado = (strtolower($det->estadoConservacion->nombre_conservacion ?? '') != 'bueno');
            $inc = $incidenciasMap->get($det->movimiento->idbien)?->first();

            if ($inc || $esUbicDiferente || $esDeteriorado) {
                $alertasFinal->push([
                    'codigo'       => $det->movimiento->bien->codigo_patrimonial,
                    'denominacion' => $det->movimiento->bien->denominacion_bien,
                    'observacion'  => $inc ? $inc->observacion : ($esUbicDiferente ? 'Ubicación física distinta a la del sistema.' : 'Estado de conservación: ' . ($det->estadoConservacion->nombre_conservacion ?? '---')),
                ]);
            }
        }

        setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'esp');
        $fechaCierre = $inventario->fechacierre ? $inventario->fechacierre : now();
        $dia = $fechaCierre->format('d');
        $mes = strftime('%B', $fechaCierre->timestamp);
        $anio = $fechaCierre->format('Y');
    @endphp

    <!-- CABECERA INSTITUCIONAL -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="inst-name">{{ $settings['nombre_institucion'] ?? 'ENTIDAD PÚBLICA / EMPRESA' }}</div>
                <div class="inst-ruc">RUC: {{ $settings['ruc'] ?? '----------' }}</div>
            </td>
            <td style="width: 30%; text-align: right;">
                <div style="font-size: 10px;">Fecha Emisión: {{ $fecha_actual }}</div>
                <div style="font-size: 10px;">Expediente: <b>{{ $inventario->codigoinventario }}</b></div>
            </td>
        </tr>
    </table>

    <div class="doc-title">ACTA DE CONCILIACIÓN FÍSICA DE INVENTARIO</div>
    <div class="doc-subtitle">N° {{ $inventario->codigoinventario }} - {{ $anio }}</div>

    <div class="legal-text">
        En las instalaciones de la entidad <strong>{{ strtoupper($settings['nombre_institucion'] ?? 'Institución') }}</strong>, siendo el día <strong>{{ $dia }} de {{ $mes }} de {{ $anio }}</strong>, se reunieron los miembros de la Comisión de Inventario y el servidor responsable de la custodia de los bienes, con la finalidad de suscribir el Acta de Conciliación Física del Inventario Patrimonial, conforme a las directivas vigentes de gestión de bienes muebles estatales y/o normatividad interna.
    </div>

    <!-- DATOS DEL INVENTARIO -->
    <table class="info-table">
        <tr>
            <th>TIPO DE INVENTARIO</th>
            <td class="uppercase">{{ $inventario->tipoinventario }}</td>
        </tr>
        <tr>
            <th>CUSTODIO / RESPONSABLE (ÁREA)</th>
            <td class="uppercase">{{ $inventario->responsablePersona ? $inventario->responsablePersona->nombre_responsable . ' ' . $inventario->responsablePersona->apellidos_responsable : 'NO APLICA / GENERAL' }} - DNI: {{ $inventario->responsable ?? '---' }}</td>
        </tr>
        <tr>
            <th>FECHA DE INICIO DE VERIFICACIÓN</th>
            <td>{{ $inventario->fecha_inicio ? $inventario->fecha_inicio->format('d/m/Y') : '---' }}</td>
        </tr>
        <tr>
            <th>AUDITOR / ENCARGADO PATRIMONIO</th>
            <td class="uppercase">{{ $inventario->usuarioCierre->name ?? '---' }}</td>
        </tr>
    </table>

    <div class="legal-text">
        Habiéndose concluido el trabajo de verificación física "in situ" en las dependencias señaladas, se procedió a efectuar el cruce de información entre los resultados de la toma física y el registro patrimonial del sistema, determinándose el siguiente resumen cuantitativo:
    </div>

    <!-- RESUMEN ESTADÍSTICO -->
    <table class="stats-table">
        <tr>
            <th>Total Esperado<br>(Según Sistema)</th>
            <th>Bienes Verificados<br>(Físicamente Conformes)</th>
            <th>Bienes Faltantes<br>(No Ubicados)</th>
            <th>Bienes Sobrantes<br>(No pertenecían al alcance)</th>
        </tr>
        <tr>
            <td>{{ $estadisticas['total_esperados'] ?? 0 }}</td>
            <td>{{ $estadisticas['total_verificados'] ?? 0 }}</td>
            <td style="color: #c62828;">{{ count($faltantesFinal) }}</td>
            <td style="color: #1565c0;">{{ count($sobrantesFinal) }}</td>
        </tr>
    </table>

    <div class="legal-text">
        Se adjuntan a la presente acta, en calidad de <strong>ANEXOS</strong>, los listados detallados correspondientes a las diferencias (faltantes, sobrantes y observaciones) halladas durante la inspección.
    </div>
    
    <div class="legal-text" style="margin-top: 15px;">
        En señal de conformidad y aceptación plena de los resultados obtenidos y detallados en los anexos, firman los presentes:
    </div>

    <!-- FIRMAS DE CONFORMIDAD -->
    <table class="signatures">
        <tr>
            <td class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-name">{{ $inventario->responsablePersona ? $inventario->responsablePersona->nombre_responsable . ' ' . $inventario->responsablePersona->apellidos_responsable : '___________________________' }}</div>
                <div class="sig-role">SERVIDOR CUSTODIO DE LOS BIENES<br>DNI: {{ $inventario->responsable ?? '____________' }}</div>
            </td>
            <td class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-name">{{ $inventario->usuarioCierre->name ?? '___________________________' }}</div>
                <div class="sig-role">RESPONSABLE DE CONTROL PATRIMONIAL<br>(COMISIÓN DE INVENTARIO)</div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="height: 60px;"></td>
        </tr>
        <tr>
            <td colspan="2" class="sig-box" style="width: 100%;">
                <div class="sig-line" style="width: 40%;"></div>
                <div class="sig-name">______________________________________</div>
                <div class="sig-role">JEFE DE ADMINISTRACIÓN / TESTIGO<br>(COMISIÓN DE INVENTARIO)</div>
            </td>
        </tr>
    </table>


    <!-- ==================== ANEXOS ==================== -->
    
    @if(count($faltantesFinal) > 0)
    <div class="page-break"></div>
    <div class="header-table" style="border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 15px;">
        <span class="inst-name" style="font-size: 11px;">{{ $settings['nombre_institucion'] ?? 'INSTITUCIÓN' }}</span>
        <span style="float: right; font-size: 10px;">Expediente: {{ $inventario->codigoinventario }}</span>
    </div>
    
    <div class="anexo-title">ANEXO 01: RELACIÓN DE BIENES FALTANTES (NO UBICADOS)</div>
    <div style="font-size: 10px; margin-bottom: 10px; text-align: justify;">
        Bienes que figuran asignados al área/responsable en el sistema patrimonial, pero que <strong>NO fueron ubicados</strong> físicamente durante el proceso de verificación. Estos bienes quedan sujetos a investigación para deslinde de responsabilidades.
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">N°</th>
                <th style="width: 15%;">Cód. Patrimonial</th>
                <th style="width: 45%;">Denominación del Bien</th>
                <th style="width: 35%;">Observación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($faltantesFinal as $index => $f)
            @php $incFaltante = $incidenciasMap->get($f->id_bien)?->first(); @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td class="text-bold">{{ $f->codigo_patrimonial }}</td>
                <td>{{ $f->denominacion_bien }}<br><small style="color: #666;">{{ $f->tipoBien->nombre_tipo ?? '' }}</small></td>
                <td>{{ $incFaltante ? $incFaltante->observacion : 'No presentado durante la toma física.' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif


    @if(count($sobrantesFinal) > 0)
    @if(count($faltantesFinal) == 0) <div class="page-break"></div> @endif
    <div class="anexo-title" style="{{ count($faltantesFinal) > 0 ? 'margin-top: 40px;' : '' }}">ANEXO 02: RELACIÓN DE BIENES SOBRANTES (HALLAZGOS FÍSICOS)</div>
    <div style="font-size: 10px; margin-bottom: 10px; text-align: justify;">
        Bienes que fueron verificados físicamente en el área auditada, pero que según el sistema <strong>NO estaban asignados</strong> a este custodio/área (pertenecían a otro lugar o no estaban registrados). Requieren regularización de desplazamiento o alta patrimonial.
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">N°</th>
                <th style="width: 15%;">Cód. Patrimonial</th>
                <th style="width: 45%;">Denominación del Bien</th>
                <th style="width: 35%;">Tipo de Bien</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sobrantesFinal as $index => $s)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td class="text-bold">{{ $s->codigo_patrimonial }}</td>
                <td>{{ $s->denominacion_bien }}</td>
                <td>{{ $s->tipoBien->nombre_tipo ?? '---' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif


    @if(count($alertasFinal) > 0)
    @if(count($faltantesFinal) == 0 && count($sobrantesFinal) == 0) <div class="page-break"></div> @endif
    <div class="anexo-title" style="{{ (count($faltantesFinal) > 0 || count($sobrantesFinal) > 0) ? 'margin-top: 40px;' : '' }}">ANEXO 03: BIENES VERIFICADOS CON OBSERVACIONES E INCIDENCIAS</div>
    <div style="font-size: 10px; margin-bottom: 10px; text-align: justify;">
        Bienes que fueron ubicados en posesión del custodio, pero que presentan un cambio de estado de conservación, ubicación física exacta divergente u otra incidencia reportada durante la toma física.
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">N°</th>
                <th style="width: 15%;">Cód. Patrimonial</th>
                <th style="width: 40%;">Denominación del Bien</th>
                <th style="width: 40%;">Detalle del Hallazgo / Observación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alertasFinal as $index => $a)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td class="text-bold">{{ $a['codigo'] }}</td>
                <td>{{ $a['denominacion'] }}</td>
                <td style="color: #d32f2f;">{{ $a['observacion'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

</body>
</html>
