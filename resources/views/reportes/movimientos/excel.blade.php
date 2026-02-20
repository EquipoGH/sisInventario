<table>
  <thead>
    <!-- ENCABEZADO INSTITUCIONAL -->
    <tr>
      <th colspan="13" style="font-size: 16px; font-weight: bold; text-align: center; background-color: #34495e; color: white;">
        {{ $settings['nombre_institucion'] ?? 'INSTITUCIÓN' }}
      </th>
    </tr>
    <tr>
      <th colspan="13" style="font-size: 12px; text-align: center; background-color: #ecf0f1;">
        REPORTE: MOVIMIENTOS POR FECHA
      </th>
    </tr>
    <tr>
      <th colspan="13" style="font-size: 10px; text-align: center;">
        Generado por: {{ $usuario->name ?? 'Sistema' }} | Fecha: {{ $fechaGeneracion }} | Total: {{ $movimientos->count() }} registros
      </th>
    </tr>
    <tr><th colspan="13"></th></tr>

    <!-- ENCABEZADOS DE COLUMNA -->
    <tr style="background-color: #4472C4; color: white; font-weight: bold;">
      <th>ID</th>
      <th>Fecha/Hora</th>
      <th>Tipo Movimiento</th>
      <th>Código Patrimonial</th>
      <th>Denominación Bien</th>
      <th>Tipo Bien</th>
      <th>Área</th>
      <th>Ubicación</th>
      <th>Estado Conservación</th>
      <th>Usuario Registró</th>
      <th>Tipo Documento</th>
      <th>N° Documento</th>
      <th>Fecha Documento</th>
      <th>Detalle Técnico</th>
      <th>Estado</th>
      <th>Anulado Por</th>
      <th>Motivo Anulación</th>
    </tr>
  </thead>
  <tbody>
    @foreach($movimientos as $m)
      <tr>
        <td>{{ $m->idmovimiento }}</td>
        <td>{{ $m->fechamvto ? \Carbon\Carbon::parse($m->fechamvto)->format('d/m/Y H:i') : '-' }}</td>
        <td>{{ $m->tipoMovimiento->tipomvto ?? '-' }}</td>
        <td>{{ $m->bien->codigopatrimonial ?? '-' }}</td>
        <td>{{ $m->bien->denominacionbien ?? '-' }}</td>
        <td>{{ $m->bien->tipoBien->nombretipo ?? '-' }}</td>
        <td>{{ $m->ubicacion->area->nombre_area ?? '-' }}</td>
        <td>{{ ($m->ubicacion->nombre_sede ?? '') . ' - ' . ($m->ubicacion->ambiente ?? '') }}</td>
        <td>{{ $m->estadoConservacion->nombre_estado ?? '-' }}</td>
        <td>{{ $m->usuario->name ?? '-' }}</td>
        <td>{{ $m->documentoSustento->tipodocumento ?? '-' }}</td>
        <td>{{ $m->documentoSustento->numerodocumento ?? '-' }}</td>
        <td>{{ $m->documentoSustento->fecha_documento ? \Carbon\Carbon::parse($m->documentoSustento->fecha_documento)->format('d/m/Y') : '-' }}</td>
        <td>{{ $m->detalletecnico ?? '-' }}</td>
        <td>{{ ($m->anulado ?? false) ? 'ANULADO' : 'ACTIVO' }}</td>
        <td>{{ $m->usuarioAnulo->name ?? '-' }}</td>
        <td>{{ $m->motivoanulacion ?? '-' }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
