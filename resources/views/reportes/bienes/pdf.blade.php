<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte de Bienes</title>
  <style>
    @page { margin: 12mm 8mm 35mm 8mm; }
    * { box-sizing: border-box; }

    body{
      font-family: DejaVu Sans, sans-serif;
      font-size: 8px;
      color:#000;
      margin:0;
      padding:0;
    }

    /* ===== Encabezado ===== */
    .header-box{
      border: 2px solid #000;
      padding: 7px 9px;
      margin-bottom: 8px;
    }
    .top-grid{ width:100%; border-collapse:collapse; }
    .top-grid td{ vertical-align: middle; padding: 2px 4px; }

    .logo-cell{ width:62px; text-align:center; }
    .logo{ max-width:52px; max-height:52px; }

    .inst-cell{ text-align:center; }
    .inst-name{
      font-weight: 900;
      font-size: 11px;
      text-transform: uppercase;
      margin-bottom: 2px;
      letter-spacing: .3px;
    }
    .inst-info{
      font-size: 7px;
      line-height: 1.25;
      color: #333;
    }

    .stamp-cell{
      width:120px;
      text-align:right;
      font-size:7px;
      line-height:1.25;
      white-space:nowrap;
    }

    .rule{ border-top: 1px solid #000; margin-top: 5px; padding-top: 5px; }

    .report-title{
      font-weight: 900;
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: .4px;
      margin-bottom: 2px;
    }

    .meta-line{
      width:100%;
      border-collapse:collapse;
      font-size:7px;
    }
    .meta-line td{ padding: 1px 2px; }
    .meta-label{ font-weight: 900; }
    .meta-value{ color:#111; }

    /* ===== Tabla ===== */
    .table-datos{
      width:100%;
      border-collapse: collapse;
      font-size:7px;
      table-layout: auto;
    }

    .table-datos th{
      background:#efefef;
      border: 1px solid #000;
      padding: 3px 2px;
      text-align:center;
      font-weight: 900;
      text-transform: uppercase;
      line-height: 1.1;
      white-space: nowrap;
    }

    .table-datos td{
      border: 1px solid #000;
      padding: 2px 3px;
      vertical-align: top;
      line-height: 1.15;
      word-break: break-word;
    }

    .table-datos tbody tr:nth-child(even) td{ background:#fafafa; }

    .c{ text-align:center; vertical-align: middle; }
    .num{ font-weight: 900; font-size: 7px; }

    .clip-3{ display:block; max-height: 3.45em; overflow:hidden; }
    .clip-2{ display:block; max-height: 2.3em; overflow:hidden; }

    .col-n{ padding-left:1px !important; padding-right:1px !important; }
    .nowrap{ white-space: nowrap; word-break: normal; }

    /* ===== Pie ===== */
    .footer{
      position: fixed;
      left: 0;
      right: 0;
      bottom: -28mm;
      height: 25mm;
      font-size: 7px;
      color:#555;
    }

    .firmas{
      margin-top: 15px;
      width:100%;
      border-collapse: collapse;
    }
    .firmas td{
      width:33.33%;
      text-align:center;
      border-top:1px solid #000;
      padding-top: 3px;
      font-weight: 900;
      font-size: 7px;
    }
  </style>
</head>
<body>

  @php
    // ✅ Tipos de reporte correctos (igual que el controlador)
    $tipoTxt = match($reporte ?? 'inventario_general') {
      'inventario_general'      => 'Inventario General ' . now()->format('Y'),
      'inventario_area'         => 'Inventario por Área y Ubicación',
      'inventario_estado_admin' => 'Inventario por Estado de Conservación' . (!empty($filtros['estado_bien_nombre']) ? ' (' . $filtros['estado_bien_nombre'] . ')' : ' (Todos)'),
      'bienes_responsable'      => 'Bienes por Responsable' . (!empty($filtros['responsable_nombre']) ? ' (' . $filtros['responsable_nombre'] . ')' : ' (Todos)'),
      default                   => 'Inventario General',
    };

    $estadoRaw = $estado ?? ($filtros['estado'] ?? 'activos');
    $estadoTxt = match($estadoRaw) {
      'bajas' => 'Bajas',
      'todos'     => 'Todos',
      default     => 'Activos',
    };

    // ✅ Filtros reales que envía el controlador
    $anio     = $filtros['anio']      ?? null;
    $areaId   = $filtros['area_id']   ?? null;
    $ubicId   = $filtros['ubicacion_id'] ?? null;
    $tipoBien = $filtros['tipo_bien'] ?? null;

    // Construir descripción de período/filtros
    $filtroPartes = [];
    if ($anio)                                    $filtroPartes[] = "Año: {$anio}";
    if (!empty($filtros['area_nombre']))          $filtroPartes[] = "Área: {$filtros['area_nombre']}";
    if (!empty($filtros['ubicacion_nombre']))     $filtroPartes[] = "Ubic.: {$filtros['ubicacion_nombre']}";
    if (!empty($filtros['tipo_bien_nombre']))     $filtroPartes[] = "Tipo: {$filtros['tipo_bien_nombre']}";
    $periodoTxt = !empty($filtroPartes) ? implode(' | ', $filtroPartes) : '';

    // Usuario que genera el reporte
    $u = $usuario ?? auth()->user();
    $usuarioTxt = $u ? ($u->name ?? $u->email ?? 'Usuario') : 'Usuario';
  @endphp

  <div class="header-box">
    <table class="top-grid">
      <tr>
        <td class="logo-cell">
          @if(!empty($settings['logo_reportes_abs']))
            <img class="logo" src="{{ $settings['logo_reportes_abs'] }}" alt="Logo">
          @endif
        </td>

        <td class="inst-cell">
          <div class="inst-name">{{ $settings['nombre_institucion'] ?: 'INSTITUCIÓN' }}</div>
          <div class="inst-info">
            @if(!empty($settings['direccion'])){{ $settings['direccion'] }}<br>@endif
            @if(!empty($settings['ruc']))RUC: {{ $settings['ruc'] }}@endif
            @if(!empty($settings['telefono'])) | Tel: {{ $settings['telefono'] }}@endif
          </div>
        </td>

        <td class="stamp-cell">
          <strong>Fecha:</strong> {{ now()->format('d/m/Y') }}<br>
          <strong>Hora:</strong> {{ now()->format('H:i') }}
        </td>
      </tr>
    </table>

    <div class="rule">
      <div class="report-title">Reporte de Bienes — {{ $tipoTxt }}</div>

      <table class="meta-line">
        <tr>
          <td style="width:30%;">
            <span class="meta-label">Tipo:</span>
            <span class="meta-value">{{ $tipoTxt }}</span>
          </td>

          <td style="width:20%;">
            <span class="meta-label">Estado:</span>
            <span class="meta-value">{{ $estadoTxt }}</span>
          </td>

          <td style="width:34%;">
            @if($periodoTxt)
              <span class="meta-label">Filtros:</span>
              <span class="meta-value">{{ $periodoTxt }}</span>
            @endif
          </td>

          <td style="width:16%; text-align:right;">
            <span class="meta-label">Total:</span>
            <span class="meta-value">{{ $bienes->count() }}</span>
          </td>
        </tr>

        <tr>
          <td colspan="4">
            <span class="meta-label">Generado por:</span>
            <span class="meta-value">{{ $usuarioTxt }}</span>
          </td>
        </tr>
      </table>
    </div>
  </div>

  @php
    $bienesOrdenados = $bienes->sortBy(function($b) {
      $area = $b->latestMovimiento?->ubicacion?->area;
      $areaName = $area ? strtoupper($area->nombre_area) : 'ZZZ_SIN_AREA';
      $registrador = $b->registradoPor ? strtoupper($b->registradoPor->name) : 'ZZZ_SISTEMA';
      return $areaName . '|' . $registrador;
    });

    $bienesAgrupados = $bienesOrdenados->groupBy(function($b) {
      $area = $b->latestMovimiento?->ubicacion?->area;
      return $area ? strtoupper($area->nombre_area) : 'SIN ÁREA';
    });
  @endphp

  @forelse($bienesAgrupados as $grupoLlave => $listaBienes)
    <div style="font-size: 9px; font-weight: bold; margin-bottom: 4px; margin-top: 15px; text-transform: uppercase;">
      {{ $grupoLlave }}
    </div>

    <table class="table-datos">
      <thead>
        <tr>
          <th width="4%"   style="width:4%;">#</th>
          <th width="11%"  style="width:11%;">CÓDIGO</th>
          <th width="28%"  style="width:28%;">DENOMINACIÓN</th>
          <th width="10%"  style="width:10%;">TIPO</th>
          <th width="8%"   style="width:8%;">MARCA</th>
          <th width="7%"   style="width:7%;">MODELO</th>
          <th width="10%"  style="width:10%;">SERIE</th>
          <th width="22%"  style="width:22%;">UBICACIÓN</th>
        </tr>
      </thead>
      <tbody>
        @foreach($listaBienes as $i => $b)
          @php
            $lm = $b->latestMovimiento;
            $ubic = $lm?->ubicacion;
            $area = $ubic?->area;

            $ubicTxtRow = null;
            if ($ubic) {
              $ubicTxtRow = trim($ubic->ambiente ?? '');
            }
          @endphp

          <tr>
            <td class="c num" width="4%" style="width:4%;">{{ $loop->iteration }}</td>
            <td class="c nowrap" width="11%" style="width:11%;"><span class="clip-2">{{ $b->codigo_patrimonial }}</span></td>

            <td width="28%" style="width:28%;"><span class="clip-3">{{ mb_strtoupper($b->denominacion_bien ?? '') }}</span></td>
            <td width="10%" style="width:10%;"><span class="clip-2">{{ optional($b->tipoBien)->nombre_tipo }}</span></td>

            <td width="8%"  style="width:8%;"><span class="clip-2">{{ $b->marca_bien }}</span></td>
            <td width="7%"  style="width:7%;"><span class="clip-2">{{ $b->modelo_bien }}</span></td>
            <td width="10%" style="width:10%;"><span class="clip-2">{{ $b->nserie_bien }}</span></td>

            <td width="22%" style="width:22%;"><span class="clip-2">{{ $ubicTxtRow }}</span></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @empty
    <table class="table-datos">
      <tbody>
        <tr>
          <td colspan="7" class="c">No hay registros</td>
        </tr>
      </tbody>
    </table>
  @endforelse

  <div class="footer">
    @if(!empty($settings['pie_reportes']))
      <div style="margin-bottom:2px;">{{ $settings['pie_reportes'] }}</div>
    @endif
    @if(!empty($settings['texto_legal']))
      <div style="color:#666;">{{ $settings['texto_legal'] }}</div>
    @endif

    <table class="firmas">
      <tr>
        <td>ENTREGUÉ CONFORME</td>
        <td>RECIBÍ CONFORME</td>
        <td>RESPONSABLE</td>
      </tr>
    </table>
  </div>

  <script type="text/php">
    if (isset($pdf)) {
      $text = "Página {PAGE_NUM} / {PAGE_COUNT}";
      $font = $fontMetrics->get_font("DejaVu Sans", "normal");
      $size = 7;
      $x = 470;
      $y = 18;
      $pdf->page_text($x, $y, $text, $font, $size, array(0,0,0));
    }
  </script>

</body>
</html>
