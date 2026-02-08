<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte de Bienes</title>
  <style>
    @page { margin: 12mm 8mm 22mm 8mm; }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 8px;
      color: #000;
      margin: 0;
      padding: 0;
    }

    /* Encabezado */
    .header-box {
      border: 2px solid #000;
      padding: 6px 8px;
      margin-bottom: 6px;
    }

    .header-grid { width: 100%; border-collapse: collapse; }
    .header-grid td { vertical-align: middle; padding: 2px 4px; }

    .logo-cell { width: 62px; text-align: center; }
    .logo { max-width: 52px; max-height: 52px; }

    .center-cell { text-align: center; }

    .info-cell {
      width: 105px;
      text-align: right;
      font-size: 7px;
      line-height: 1.25;
      white-space: nowrap;
    }

    .nombre-inst {
      font-weight: bold;
      font-size: 11px;
      margin-bottom: 2px;
      text-transform: uppercase;
    }

    .datos-inst {
      font-size: 7px;
      color: #333;
      line-height: 1.25;
    }

    /* Barra meta */
    .meta-box {
      border-top: 1px solid #000;
      margin-top: 4px;
      padding-top: 3px;
      width: 100%;
      font-size: 7px;
    }
    .meta-box table { width: 100%; border-collapse: collapse; }
    .meta-box td { padding: 1px 3px; }
    .meta-label { font-weight: bold; }

    /* Tabla principal */
    .table-datos {
      width: 100%;
      border-collapse: collapse;
      margin-top: 5px;
      font-size: 7px;
      table-layout: fixed;
    }

    .table-datos th {
      background-color: #e3e3e3;
      border: 1px solid #000;
      padding: 3px 2px;
      font-weight: bold;
      text-align: center;
      font-size: 7px;
      text-transform: uppercase;
      line-height: 1.1;
    }

    .table-datos td {
      border: 1px solid #000;
      padding: 2px 3px;
      vertical-align: middle;
      line-height: 1.1;
      overflow: hidden;
      word-break: break-word;
    }

    .c { text-align: center; }
    .r { text-align: right; }

    /* QR más pequeño => baja la altura de la fila */
    .qr-img {
      width: 34px;
      height: 34px;
      display: block;
      margin: 0 auto;
    }

    /* Evitar que el # se vea “gigante” en filas altas */
    .num {
      font-size: 7px;
      font-weight: bold;
      line-height: 1;
    }

    /* Para textos largos: máximo 2 líneas */
    .clip-2 {
      display: block;
      max-height: 2.2em;
      overflow: hidden;
    }

    /* Pie */
    .footer {
      position: fixed;
      left: 8mm;
      right: 8mm;
      bottom: 6mm;
      font-size: 7px;
      color: #555;
    }

    .firmas {
      margin-top: 18px;
      width: 100%;
      border-collapse: collapse;
    }

    .firmas td {
      width: 33.33%;
      text-align: center;
      padding: 0 6px;
      border-top: 1px solid #000;
      padding-top: 3px;
      font-weight: bold;
      font-size: 7px;
    }
  </style>
</head>
<body>

  {{-- ENCABEZADO --}}
  <div class="header-box">
    <table class="header-grid">
      <tr>
        <td class="logo-cell">
          @if(!empty($settings['logo_reportes_abs']))
            <img class="logo" src="{{ $settings['logo_reportes_abs'] }}" alt="Logo">
          @endif
        </td>
        <td class="center-cell">
          <div class="nombre-inst">{{ $settings['nombre_institucion'] ?: 'INSTITUCIÓN' }}</div>
          <div class="datos-inst">
            @if(!empty($settings['direccion'])){{ $settings['direccion'] }}<br>@endif
            @if(!empty($settings['ruc']))RUC: {{ $settings['ruc'] }}@endif
            @if(!empty($settings['telefono'])) | Tel: {{ $settings['telefono'] }}@endif
          </div>
        </td>
        <td class="info-cell">
          <strong>FECHA:</strong> {{ now()->format('d/m/Y') }}<br>
          <strong>HORA:</strong> {{ now()->format('H:i') }}<br>
        </td>
      </tr>
    </table>

    <div class="meta-box">
      <table>
        <tr>
          <td style="width:33%;"><span class="meta-label">REPORTE:</span> BIENES</td>
          <td style="width:45%;">
            <span class="meta-label">RANGO:</span>
            @if(!empty($filtros['desde']) && !empty($filtros['hasta']))
              {{ $filtros['desde'] }} a {{ $filtros['hasta'] }}
            @else
              Todos
            @endif
          </td>
          <td style="width:22%; text-align:right;">
            <span class="meta-label">TOTAL:</span> {{ $bienes->count() }}
          </td>
        </tr>
      </table>
    </div>
  </div>

  {{-- TABLA --}}
  <table class="table-datos">
    <thead>
      <tr>
        {{-- # más angosto --}}
        <th style="width:12px;">#</th>

        {{-- QR un poco más angosto --}}
        <th style="width:42px;">QR</th>

        {{-- Ajuste de anchos para aprovechar espacio --}}
        <th style="width:74px;">CÓDIGO</th>
        <th style="width:170px;">DENOMINACIÓN</th>
        <th style="width:70px;">TIPO</th>
        <th style="width:48px;">MARCA</th>
        <th style="width:48px;">MODELO</th>
        <th style="width:88px;">SERIE</th>
        <th style="width:70px;">N° DOC</th>
        <th style="width:46px;">FECHA</th>
      </tr>
    </thead>

    <tbody>
      @forelse($bienes as $i => $b)
        <tr>
          <td class="c"><span class="num">{{ $i + 1 }}</span></td>

          <td class="c">
            @if(!empty($b->qr_code))
              <img class="qr-img" src="data:image/png;base64,{{ $b->qr_code }}" alt="QR">
            @else
              -
            @endif
          </td>

          <td class="c">
            <span class="clip-2">{{ $b->codigo_patrimonial }}</span>
          </td>

          <td>
            <span class="clip-2">{{ mb_strtoupper($b->denominacion_bien ?? '') }}</span>
          </td>

          <td>
            <span class="clip-2">{{ optional($b->tipoBien)->nombre_tipo }}</span>
          </td>

          <td>
            <span class="clip-2">{{ $b->marca_bien }}</span>
          </td>

          <td>
            <span class="clip-2">{{ $b->modelo_bien }}</span>
          </td>

          <td>
            <span class="clip-2">{{ $b->nserie_bien }}</span>
          </td>

          <td class="c">
            <span class="clip-2">{{ $b->NumDoc }}</span>
          </td>

          <td class="c">{{ optional($b->fecha_registro)->format('d/m/Y') }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="10" class="c">No hay registros</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{-- PIE --}}
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

  {{-- Paginación (DomPDF) --}}
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
