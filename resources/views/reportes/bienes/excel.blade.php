<table>
  <tr>
    <td>{{ strtoupper($settings['nombre_institucion'] ?? 'INSTITUCIÓN') }}</td>
  </tr>
  <tr>
    <td>REPORTE DE BIENES</td>
  </tr>
  <tr>
    <td>
      @if(!empty($settings['ruc']))RUC: {{ $settings['ruc'] }} | @endif
      Fecha: {{ now()->format('d/m/Y') }}
      @if(!empty($filtros['desde']) && !empty($filtros['hasta']))
        | Rango: {{ $filtros['desde'] }} a {{ $filtros['hasta'] }}
      @endif
    </td>
  </tr>
  <tr><td></td></tr>
  <tr><td></td></tr>

  {{-- Headers --}}
  <tr>
    <th>#</th>
    <th>CÓDIGO</th>
    <th>DENOMINACIÓN</th>
    <th>TIPO</th>
    <th>MARCA</th>
    <th>MODELO</th>
    <th>SERIE</th>
    <th>N° DOC</th>
    <th>FECHA</th>
  </tr>

  {{-- Datos --}}
  @foreach($bienes as $i => $b)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ $b->codigo_patrimonial }}</td>
      <td>{{ strtoupper($b->denominacion_bien) }}</td>
      <td>{{ optional($b->tipoBien)->nombre_tipo }}</td>
      <td>{{ $b->marca_bien }}</td>
      <td>{{ $b->modelo_bien }}</td>
      <td>{{ $b->nserie_bien }}</td>
      <td>{{ $b->NumDoc }}</td>
      <td>{{ optional($b->fecha_registro)->format('d/m/Y') }}</td>
    </tr>
  @endforeach
</table>
