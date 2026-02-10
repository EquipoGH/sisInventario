<table style="width:100%; border-collapse:collapse; font-family:DejaVu Sans, sans-serif; font-size:8px;">
  {{-- Título --}}
  <tr>
    <td style="text-align:center; font-weight:bold; font-size:11px; text-transform:uppercase;">
      {{ strtoupper($settings['nombre_institucion'] ?? 'INSTITUCIÓN') }}
    </td>
  </tr>
  <tr>
    <td style="text-align:center; font-weight:bold; padding-top:2px;">
      REPORTE DE BIENES
    </td>
  </tr>
  <tr>
    <td style="text-align:center; font-size:7px; padding-top:2px; padding-bottom:6px;">
      @if(!empty($settings['ruc']))RUC: {{ $settings['ruc'] }} | @endif
      Fecha: {{ now()->format('d/m/Y') }}
      @if(!empty($filtros['desde']) || !empty($filtros['hasta']))
        | Período:
        @if(!empty($filtros['desde']) && !empty($filtros['hasta']))
          {{ $filtros['desde'] }} a {{ $filtros['hasta'] }}
        @elseif(!empty($filtros['desde']))
          Desde {{ $filtros['desde'] }}
        @else
          Hasta {{ $filtros['hasta'] }}
        @endif
      @else
        | Período: Todas las fechas
      @endif
      | Total: {{ $bienes->count() }}
    </td>
  </tr>

  <tr><td style="height:6px;"></td></tr>

  <tr>
    <td>
      <table style="width:100%; border-collapse:collapse; table-layout:fixed; font-size:7px;">
        <thead>
          <tr>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:18px; text-align:center;">#</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:78px; text-align:center;">CÓDIGO</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:170px; text-align:center;">DENOMINACIÓN</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:72px; text-align:center;">TIPO</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:52px; text-align:center;">MARCA</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:52px; text-align:center;">MODELO</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:78px; text-align:center;">SERIE</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:65px; text-align:center;">N° DOC</th>
            <th style="border:1px solid #000; background:#ddd; padding:3px 2px; width:48px; text-align:center;">FECHA</th>
          </tr>
        </thead>

        <tbody>
          @forelse($bienes as $i => $b)
            <tr>
              <td style="border:1px solid #000; padding:2px 3px; text-align:center;">
                {{ $i + 1 }}
              </td>

              <td style="border:1px solid #000; padding:2px 3px;">
                {{ $b->codigo_patrimonial }}
              </td>

              <td style="border:1px solid #000; padding:2px 3px;">
                {{ mb_strtoupper($b->denominacion_bien ?? '') }}
              </td>

              <td style="border:1px solid #000; padding:2px 3px;">
                {{ optional($b->tipoBien)->nombre_tipo }}
              </td>

              <td style="border:1px solid #000; padding:2px 3px;">
                {{ $b->marca_bien }}
              </td>

              <td style="border:1px solid #000; padding:2px 3px;">
                {{ $b->modelo_bien }}
              </td>

              <td style="border:1px solid #000; padding:2px 3px;">
                {{ $b->nserie_bien }}
              </td>

              <td style="border:1px solid #000; padding:2px 3px;">
                {{ $b->NumDoc }}
              </td>

              <td style="border:1px solid #000; padding:2px 3px; text-align:center;">
                {{ optional($b->fecha_registro)->format('d/m/Y') }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" style="border:1px solid #000; padding:6px; text-align:center;">
                No hay registros
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </td>
  </tr>
</table>
