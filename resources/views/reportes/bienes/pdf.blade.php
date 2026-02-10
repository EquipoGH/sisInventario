<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte de Bienes</title>
  <style>
    @page { margin: 12mm 8mm 18mm 8mm; }
    * { box-sizing: border-box; }

    body{
      font-family: DejaVu Sans, sans-serif;
      font-size: 8px;
      color:#000;
      margin:0;
      padding:0;
    }

    /* ===== Encabezado simple ===== */
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
      table-layout: auto; /* CLAVE: NO fixed */
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

    /* # más pequeño */
    .col-n{ padding-left:1px !important; padding-right:1px !important; }

    /* Código sin salto raro */
    .nowrap{ white-space: nowrap; word-break: normal; }

    /* ===== Pie ===== */
    .footer{
      position: fixed;
      left: 8mm;
      right: 8mm;
      bottom: 6mm;
      font-size: 7px;
      color:#555;
    }

    .firmas{
      margin-top: 12px;
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
    $tipoTxt = match($reporte ?? 'general') {
      'registrados' => 'Registrados',
      'asignados' => 'Asignados',
      'bajas' => 'Bajas',
      default => 'General',
    };

    $estadoRaw = $estado ?? ($filtros['estado'] ?? 'activos');
    $estadoTxt = match($estadoRaw) {
      'inactivos' => 'Inactivos',
      'todos' => 'Todos',
      default => 'Activos',
    };

    $desdeRaw = $filtros['desde'] ?? null;
    $hastaRaw = $filtros['hasta'] ?? null;

    $fmt = function($d){
      try { return \Carbon\Carbon::parse($d)->format('d/m/Y'); }
      catch (\Throwable $e) { return $d; }
    };

    if (!empty($desdeRaw) && !empty($hastaRaw)) {
      $periodoTxt = $fmt($desdeRaw).' - '.$fmt($hastaRaw);
    } elseif (!empty($desdeRaw)) {
      $periodoTxt = 'Desde '.$fmt($desdeRaw);
    } elseif (!empty($hastaRaw)) {
      $periodoTxt = 'Hasta '.$fmt($hastaRaw);
    } else {
      $periodoTxt = 'Todas las fechas';
    }

    // ✅ Usuario que genera el reporte (si no lo pasas desde controller, usa auth())
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
      <div class="report-title">Reporte de Bienes</div>

      <table class="meta-line">
        <tr>
          <td style="width:30%;">
            <span class="meta-label">Tipo:</span>
            <span class="meta-value">{{ $tipoTxt }}</span>
          </td>

          <td style="width:24%;">
            <span class="meta-label">Estado:</span>
            <span class="meta-value">{{ $estadoTxt }}</span>
          </td>

          <td style="width:30%;">
            <span class="meta-label">Período:</span>
            <span class="meta-value">{{ $periodoTxt }}</span>
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

  <table class="table-datos">
    <thead>
      <tr>
        {{-- ✅ SIN FECHA --}}
        <th class="col-n" width="3%"  style="width:3%;">#</th>
        <th width="11%" style="width:11%;">CÓDIGO</th>
        <th width="26%" style="width:26%;">DENOMINACIÓN</th>
        <th width="10%" style="width:10%;">TIPO</th>
        <th width="7%"  style="width:7%;">MARCA</th>
        <th width="7%"  style="width:7%;">MODELO</th>
        <th width="10%" style="width:10%;">SERIE</th>
        <th width="10%" style="width:10%;">ÁREA</th>
        <th width="16%" style="width:16%;">UBICACIÓN</th>
      </tr>
    </thead>

    <tbody>
      @forelse($bienes as $i => $b)
        @php
          $lm = $b->latestMovimiento;
          $ubic = $lm?->ubicacion;
          $area = $ubic?->area;

          $ubicTxtRow = null;
          if ($ubic) {
            $partsU = array_filter([
              $ubic->nombre_sede ?? null,
              $ubic->ambiente ?? null,
            ]);
            $ubicTxtRow = implode(' - ', $partsU);
          }
        @endphp

        <tr>
          <td class="c col-n nowrap" width="3%"  style="width:3%;"><span class="num">{{ $i + 1 }}</span></td>
          <td class="c nowrap"      width="11%" style="width:11%;"><span class="clip-2">{{ $b->codigo_patrimonial }}</span></td>

          <td width="26%" style="width:26%;"><span class="clip-3">{{ mb_strtoupper($b->denominacion_bien ?? '') }}</span></td>
          <td width="10%" style="width:10%;"><span class="clip-2">{{ optional($b->tipoBien)->nombre_tipo }}</span></td>

          <td width="7%"  style="width:7%;"><span class="clip-2">{{ $b->marca_bien }}</span></td>
          <td width="7%"  style="width:7%;"><span class="clip-2">{{ $b->modelo_bien }}</span></td>
          <td width="10%" style="width:10%;"><span class="clip-2">{{ $b->nserie_bien }}</span></td>

          <td width="10%" style="width:10%;"><span class="clip-2">{{ $area?->nombre_area }}</span></td>
          <td width="16%" style="width:16%;"><span class="clip-2">{{ $ubicTxtRow }}</span></td>
        </tr>
      @empty
        <tr>
          <td colspan="9" class="c">No hay registros</td>
        </tr>
      @endforelse
    </tbody>
  </table>

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
//Hola
