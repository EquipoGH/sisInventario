<table style="width:100%; border-collapse:collapse; font-family:DejaVu Sans, sans-serif; font-size:8px;">
  {{-- Encabezado --}}
  <tr>
    <td style="text-align:center; font-weight:bold; font-size:11px; text-transform:uppercase;">
      {{ strtoupper($settings['nombre_institucion'] ?? 'INSTITUCIÓN') }}
    </td>
  </tr>
  <tr>
    <td style="text-align:center; font-weight:bold; padding-top:2px;">
      @php
        $tipoTxtExcel = match($reporte ?? 'inventario_general') {
          'inventario_area'         => 'INVENTARIO POR ÁREA Y UBICACIÓN',
          'inventario_estado_admin' => 'INVENTARIO POR ESTADO DE CONSERVACIÓN' . (!empty($filtros['estado_bien_nombre']) ? ' (' . mb_strtoupper($filtros['estado_bien_nombre']) . ')' : ' (TODOS)'),
          'bienes_responsable'      => 'BIENES POR RESPONSABLE' . (!empty($filtros['responsable_nombre']) ? ' (' . mb_strtoupper($filtros['responsable_nombre']) . ')' : ' (TODOS)'),
          default                   => 'INVENTARIO GENERAL',
        };
      @endphp
      REPORTE DE BIENES — {{ $tipoTxtExcel }}
    </td>
  </tr>
  <tr>
    <td style="text-align:center; font-size:7px; padding-top:2px; padding-bottom:6px;">
      @if(!empty($settings['ruc']))RUC: {{ $settings['ruc'] }} | @endif
      Fecha: {{ now()->format('d/m/Y H:i') }}
      @php
        // ✅ Filtros reales que envía el controlador (anio, area_id, etc.)
        $partesFiltros = [];
        if (!empty($filtros['anio']))                 $partesFiltros[] = "Año: {$filtros['anio']}";
        if (!empty($filtros['area_nombre']))          $partesFiltros[] = "Área: {$filtros['area_nombre']}";
        if (!empty($filtros['ubicacion_nombre']))     $partesFiltros[] = "Ubic.: {$filtros['ubicacion_nombre']}";
        if (!empty($filtros['tipo_bien_nombre']))     $partesFiltros[] = "Tipo: {$filtros['tipo_bien_nombre']}";
        $periodoExcel = !empty($partesFiltros) ? implode(' | ', $partesFiltros) : 'Todos los registros';
        $estadoExcel  = match($filtros['estado'] ?? 'activos') {
          'inactivos' => 'Inactivos',
          'todos'     => 'Todos',
          default     => 'Activos',
        };
      @endphp
      | Estado: {{ $estadoExcel }} | Filtros: {{ $periodoExcel }} | Total: {{ $bienes->count() }}
    </td>
  </tr>

  <tr><td style="height:6px;"></td></tr>

  <tr>
    <td>
      <table style="width:100%; border-collapse:collapse; table-layout:fixed; font-size:7px;">
        <thead>
          <tr>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:18px;  text-align:center;">#</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:78px;  text-align:center;">CÓDIGO</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:205px; text-align:center;">DENOMINACIÓN</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:72px;  text-align:center;">TIPO</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:52px;  text-align:center;">MARCA</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:52px;  text-align:center;">MODELO</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:78px;  text-align:center;">SERIE</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:65px;  text-align:center;">ÁREA</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:120px; text-align:center;">UBICACIÓN</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:65px;  text-align:center;">REGISTRADO POR</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:48px;  text-align:center;">FECHA REG.</th>
          </tr>
        </thead>

        <tbody>
          @forelse($bienes as $i => $b)
            @php
              $lm       = $b->latestMovimiento;
              $ubic     = $lm?->ubicacion;
              $area     = $ubic?->area;
              $estadoCons = $lm?->estadoConservacion?->nombre_estado;
              $usuarioMvto = $lm?->usuario?->name;
              $ubicTxtE = $ubic
                ? trim($ubic->ambiente ?? '')
                : null;
            @endphp
            <tr>
              <td style="border:1px solid #000; padding:2px 3px; text-align:center;">{{ $i + 1 }}</td>
              <td style="border:1px solid #000; padding:2px 3px;">{{ $b->codigo_patrimonial }}</td>
              <td style="border:1px solid #000; padding:2px 3px;">{{ mb_strtoupper($b->denominacion_bien ?? '') }}</td>
              <td style="border:1px solid #000; padding:2px 3px;">{{ optional($b->tipoBien)->nombre_tipo }}</td>
              <td style="border:1px solid #000; padding:2px 3px;">{{ $b->marca_bien }}</td>
              <td style="border:1px solid #000; padding:2px 3px;">{{ $b->modelo_bien }}</td>
              <td style="border:1px solid #000; padding:2px 3px;">{{ $b->nserie_bien }}</td>
              <td style="border:1px solid #000; padding:2px 3px;">{{ $area?->nombre_area }}</td>
              <td style="border:1px solid #000; padding:2px 3px;">{{ $ubicTxtE }}</td>
              <td style="border:1px solid #000; padding:2px 3px;">{{ $usuarioMvto }}</td>
              <td style="border:1px solid #000; padding:2px 3px; text-align:center;">{{ optional($b->fecha_registro)->format('d/m/Y') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="11" style="border:1px solid #000; padding:6px; text-align:center;">
                No hay registros
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </td>
  </tr>
</table>
