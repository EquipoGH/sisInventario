<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Códigos QR - Inventario de Bienes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #1f2937;
            background: #ffffff;
        }

        /* ========== ENCABEZADO ========== */
        .header {
            text-align: center;
            background: #1e40af;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .header .info {
            font-size: 7px;
            margin-top: 3px;
        }

        /* ========== GRID 3x3 ========== */
        .qr-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .qr-grid td {
            width: 33.33%;
            height: 240px;
            border: 2px solid #cbd5e1;
            padding: 12px;
            text-align: center;
            vertical-align: middle;
            background: #ffffff;
        }

        .qr-grid td.empty {
            border: none;
            background: transparent;
        }

        /* ========== CÓDIGO QR ========== */
        .qr-code {
            width: 130px;
            height: 130px;
            display: block;
            margin: 0 auto 10px auto;
            border: 2px solid #e5e7eb;
            padding: 4px;
            background: #fafafa;
        }

        /* ========== CÓDIGO PATRIMONIAL ========== */
        .codigo-patrimonial {
            font-size: 11px;
            font-weight: bold;
            color: #1e40af;
            background: #dbeafe;
            padding: 4px 8px;
            border: 1px solid #93c5fd;
            display: inline-block;
            margin-bottom: 6px;
            max-width: 90%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ========== DENOMINACIÓN ========== */
        .denominacion {
            font-size: 8px;
            color: #374151;
            margin-top: 4px;
            line-height: 1.3;
            max-height: 26px;
            overflow: hidden;
        }

        /* ========== PIE DE PÁGINA ========== */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            color: #6b7280;
            padding: 8px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        /* ========== SALTOS DE PÁGINA ========== */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

@foreach($paginas as $indicePagina => $paginaBienes)
    {{-- ENCABEZADO --}}
    <div class="header">
        <h1>CÓDIGOS QR - INVENTARIO DE BIENES</h1>
        <div class="info">
            Total: {{ $total }} | Filtro: {{ $filtro }} | Generado: {{ $fecha }}
        </div>
    </div>

    {{-- GRID 3x3 --}}
    <table class="qr-grid">
        @foreach($paginaBienes->chunk(3) as $filaIndex => $fila)
            <tr>
                @foreach($fila as $bien)
                    <td>
                        {{-- QR --}}
                        <img src="{{ $bien['qr_base64'] }}"
                             alt="QR"
                             class="qr-code">

                        {{-- CÓDIGO --}}
                        <div class="codigo-patrimonial">
                            {{ $bien['codigo'] }}
                        </div>

                        {{-- NOMBRE --}}
                        <div class="denominacion">
                            {{ $bien['denominacion'] }}
                        </div>
                    </td>
                @endforeach

                {{-- CELDAS VACÍAS --}}
                @for($i = $fila->count(); $i < 3; $i++)
                    <td class="empty"></td>
                @endfor
            </tr>
        @endforeach
    </table>

    {{-- PIE --}}
    <div class="footer">
        Sistema de Gestión de Inventario | {{ $fecha }} | Página {{ $indicePagina + 1 }} de {{ $totalPaginas }}
    </div>

    {{-- SALTO --}}
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
