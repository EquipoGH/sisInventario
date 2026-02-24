<table style="width:100%; border-collapse:collapse; font-family:DejaVu Sans, sans-serif; font-size:8px;">
  {{-- Encabezado institucional --}}
  <tr>
    <td colspan="8" style="text-align:center; font-weight:bold; font-size:11px; text-transform:uppercase; padding:4px;">
      {{ strtoupper($settings['nombre_institucion'] ?? 'INSTITUCIÓN') }}
    </td>
  </tr>
  <tr>
    <td colspan="8" style="text-align:center; font-weight:bold; padding:2px;">
      REPORTE DE MOVIMIENTOS (POR BIEN)
    </td>
  </tr>
  <tr>
    <td colspan="8" style="text-align:center; font-size:7px; padding:2px 2px 6px;">
      @if(!empty($settings['ruc']))RUC: {{ $settings['ruc'] }} | @endif
      Fecha: {{ now()->format('d/m/Y H:i') }}
      @php
        // ✅ Filtros reales que envía el controlador
        $desdeRaw = $filtros['desde'] ?? null;
        $hastaRaw = $filtros['hasta'] ?? null;
        if (!empty($desdeRaw) && !empty($hastaRaw)) $periodoExcel = "{$desdeRaw} a {$hastaRaw}";
        elseif (!empty($desdeRaw)) $periodoExcel = "Desde {$desdeRaw}";
        elseif (!empty($hastaRaw)) $periodoExcel = "Hasta {$hastaRaw}";
        else $periodoExcel = 'Todas las fechas';

        $partesFiltros = [];
        if (!empty($filtros['tipo_mvto_nombre'])) $partesFiltros[] = "Tipo mov.: {$filtros['tipo_mvto_nombre']}";
        if (!empty($filtros['area_nombre']))      $partesFiltros[] = "Área: {$filtros['area_nombre']}";
        if (!empty($filtros['ubicacion_nombre'])) $partesFiltros[] = "Ubic.: {$filtros['ubicacion_nombre']}";
        if (!empty($filtros['q']))                $partesFiltros[] = "Búsqueda: \"{$filtros['q']}\"";
        
        $filtrosExtraStr = !empty($partesFiltros) ? ' | ' . implode(' | ', $partesFiltros) : '';
      @endphp
      | Período: {{ $periodoExcel }}{{ $filtrosExtraStr }} | Total: {{ $rows->count() }}
    </td>
  </tr>
  <tr><td colspan="8" style="height:6px;"></td></tr>

  {{-- Cabecera de columnas: ✅ 8 columnas, igual que baseQuery() --}}
  <tr>
    <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:18px;  text-align:center;">#</th>
    <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:85px;  text-align:center;">CÓDIGO</th>
    <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:180px; text-align:center;">DENOMINACIÓN</th>
    <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:80px;  text-align:center;">TIPO BIEN</th>
    <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:55px;  text-align:center;">FECHA</th>
    <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:90px;  text-align:center;">MOVIMIENTO</th>
    <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:80px;  text-align:center;">ÁREA</th>
    <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:120px; text-align:center;">UBICACIÓN</th>
  </tr>

  {{-- Datos: ✅ usa $rows (variable correcta del controlador) y aliases correctos del SELECT --}}
  @forelse($rows as $i => $r)
    @php
      // ✅ Aliases correctos según el SELECT en baseQuery()
      $ubicTxtE = trim(($r->nombre_sede ?? '') . ' - ' . ($r->ambiente ?? ''));
      if ($ubicTxtE === '' || $ubicTxtE === '-') $ubicTxtE = '-';

      $fechaTxtE = '-';
      if (!empty($r->fecha_mvto)) {
        try { $fechaTxtE = \Carbon\Carbon::parse($r->fecha_mvto)->format('d/m/Y'); }
        catch (\Throwable $e) { $fechaTxtE = (string)$r->fecha_mvto; }
      }
    @endphp
    <tr>
      <td style="border:1px solid #000; padding:2px 3px; text-align:center;">{{ $i + 1 }}</td>
      {{-- ✅ codigo_patrimonial (con underscore, alias del SELECT: 'b.codigo_patrimonial') --}}
      <td style="border:1px solid #000; padding:2px 3px;">{{ $r->codigo_patrimonial ?? '-' }}</td>
      {{-- ✅ denominacion_bien (con underscore, alias del SELECT: 'b.denominacion_bien') --}}
      <td style="border:1px solid #000; padding:2px 3px;">{{ mb_strtoupper($r->denominacion_bien ?? '') }}</td>
      {{-- ✅ tipo_bien (alias del SELECT: 'tb.nombre_tipo as tipo_bien') --}}
      <td style="border:1px solid #000; padding:2px 3px;">{{ $r->tipo_bien ?? '-' }}</td>
      <td style="border:1px solid #000; padding:2px 3px; text-align:center;">{{ $fechaTxtE }}</td>
      {{-- ✅ tipo_mov (alias del SELECT: 'tm.tipo_mvto as tipo_mov') --}}
      <td style="border:1px solid #000; padding:2px 3px;">{{ $r->tipo_mov ?? '-' }}</td>
      {{-- ✅ area (alias del SELECT: 'a.nombre_area as area') --}}
      <td style="border:1px solid #000; padding:2px 3px;">{{ $r->area ?? '-' }}</td>
      <td style="border:1px solid #000; padding:2px 3px;">{{ $ubicTxtE }}</td>
    </tr>
  @empty
    <tr>
      <td colspan="8" style="border:1px solid #000; padding:6px; text-align:center;">No hay registros</td>
    </tr>
  @endforelse
</table>
