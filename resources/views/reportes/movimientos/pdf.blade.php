<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte Movimientos por Fecha (por Bien)</title>
  <style>
    @page { margin: 12mm 8mm 18mm 8mm; }
    * { box-sizing: border-box; }
    body{ font-family: DejaVu Sans, sans-serif; font-size: 8px; color:#000; margin:0; padding:0; }

    .header-box{ border: 2px solid #000; padding: 7px 9px; margin-bottom: 8px; }
    .top-grid{ width:100%; border-collapse:collapse; }
    .top-grid td{ vertical-align: middle; padding: 2px 4px; }
    .logo-cell{ width:62px; text-align:center; }
    .logo{ max-width:52px; max-height:52px; }

    .inst-cell{ text-align:center; }
    .inst-name{ font-weight:900; font-size:11px; text-transform:uppercase; margin-bottom:2px; letter-spacing:.3px; }
    .inst-info{ font-size:7px; line-height:1.25; color:#333; }

    .stamp-cell{ width:120px; text-align:right; font-size:7px; line-height:1.25; white-space:nowrap; }

    .rule{ border-top: 1px solid #000; margin-top: 5px; padding-top: 5px; }
    .report-title{ font-weight:900; font-size:10px; text-transform:uppercase; letter-spacing:.4px; margin-bottom:2px; }

    .meta-line{ width:100%; border-collapse:collapse; font-size:7px; }
    .meta-line td{ padding: 1px 2px; }
    .meta-label{ font-weight:900; }
    .meta-value{ color:#111; }

    .table-datos{ width:100%; border-collapse: collapse; font-size:7px; table-layout:auto; }
    .table-datos th{
      background:#efefef; border:1px solid #000; padding:3px 2px; text-align:center;
      font-weight:900; text-transform:uppercase; line-height:1.1; white-space:nowrap;
    }
    .table-datos td{
      border:1px solid #000; padding:2px 3px; vertical-align:top; line-height:1.15; word-break:break-word;
    }
    .table-datos tbody tr:nth-child(even) td{ background:#fafafa; }

    .c{ text-align:center; vertical-align: middle; }
    .num{ font-weight:900; font-size:7px; }
    .nowrap{ white-space: nowrap; word-break: normal; }
    .clip-2{ display:block; max-height: 2.3em; overflow:hidden; }
    .clip-3{ display:block; max-height: 3.45em; overflow:hidden; }

    .footer{
      position: fixed; left: 8mm; right: 8mm; bottom: 6mm;
      font-size: 7px; color:#555;
    }
  </style>
</head>
<body>

@php
  $desdeRaw = $filtros['desde'] ?? null;
  $hastaRaw = $filtros['hasta'] ?? null;

  $fmt = function($d){
    try { return \Carbon\Carbon::parse($d)->format('d/m/Y'); }
    catch (\Throwable $e) { return $d; }
  };

  if (!empty($desdeRaw) && !empty($hastaRaw)) $periodoTxt = $fmt($desdeRaw).' - '.$fmt($hastaRaw);
  elseif (!empty($desdeRaw)) $periodoTxt = 'Desde '.$fmt($desdeRaw);
  elseif (!empty($hastaRaw)) $periodoTxt = 'Hasta '.$fmt($hastaRaw);
  else $periodoTxt = 'Todas las fechas';

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
    <div class="report-title">Reporte de movimientos por fecha (por bien)</div>

    <table class="meta-line">
      <tr>
        <td style="width:40%;">
          <span class="meta-label">Período:</span>
          <span class="meta-value">{{ $periodoTxt }}</span>
        </td>
        <td style="width:40%;">
          <span class="meta-label">Generado por:</span>
          <span class="meta-value">{{ $usuarioTxt }}</span>
        </td>
        <td style="width:20%; text-align:right;">
          <span class="meta-label">Total:</span>
          <span class="meta-value">{{ $rows->count() }}</span>
        </td>
      </tr>
    </table>
  </div>
</div>

<table class="table-datos">
  <thead>
    <tr>
      <th width="3%">#</th>
<th width="12%">Código</th>
<th width="28%">Denominación</th>
<th width="12%">Tipo bien</th>
<th width="10%">Fecha</th>
<th width="12%">Movimiento</th>
<th width="11%">Área</th>
<th width="12%">Ubicación</th>

    </tr>
  </thead>

  <tbody>
    @forelse($rows as $i => $r)
      @php
        $ubicTxt = trim(($r->nombre_sede ?? '') . ' - ' . ($r->ambiente ?? ''));
        if ($ubicTxt === '' || $ubicTxt === '-') $ubicTxt = '-';

        $docTxt = trim(($r->tipodocumento ?? '') . ' ' . ($r->numerodocumento ?? ''));
        if ($docTxt === '') $docTxt = '-';

        $fechaTxt = '-';
        if (!empty($r->fecha_mvto)) {
          try { $fechaTxt = \Carbon\Carbon::parse($r->fecha_mvto)->format('d/m/Y'); }
          catch (\Throwable $e) { $fechaTxt = (string) $r->fecha_mvto; }
        }
      @endphp

      <tr>
        <td class="c nowrap"><span class="num">{{ $i + 1 }}</span></td>
        <td class="c nowrap"><span class="clip-2">{{ $r->codigopatrimonial ?? '-' }}</span></td>
        <td><span class="clip-3">{{ mb_strtoupper($r->denominacionbien ?? '') }}</span></td>
        <td><span class="clip-2">{{ $r->tipo_bien ?? '-' }}</span></td>
        <td class="c nowrap">{{ $fechaTxt }}</td>
        <td><span class="clip-2">{{ $r->tipo_mov ?? '-' }}</span></td>
        <td><span class="clip-2">{{ $r->area ?? '-' }}</span></td>
        <td><span class="clip-2">{{ $ubicTxt }}</span></td>
        <td><span class="clip-2">{{ $docTxt }}</span></td>
      </tr>
    @empty
      <tr><td colspan="9" class="c">No hay registros</td></tr>
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
