<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Códigos QR - Inventario Patrimonial</title>
    <style>
        /* ============================================================
           RESET & PAGE CONFIG
           ============================================================ */
        @page {
            size: 210mm 297mm;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            font-family: Arial, Helvetica, sans-serif;
            background: #ffffff;
            width: 210mm;
        }

        /* ============================================================
           PAGE WRAPPER — height controls everything that fits per page
           ============================================================ */
        .page-wrapper {
            width: 210mm;
            height: 297mm;
            page-break-after: always;
            overflow: hidden;
            position: relative;
        }

        .page-wrapper:last-child {
            page-break-after: auto;
        }

        /* ============================================================
           HEADER  (height: 24mm)
           ============================================================ */
        .page-header {
            width: 100%;
            height: 24mm;
            background: #0f172a;
            padding: 0 6mm;
            display: table;
        }

        .header-inner {
            display: table-cell;
            vertical-align: middle;
        }

        .header-logo-row {
            display: table;
            width: 100%;
        }

        .header-title-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .header-badge-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 35mm;
        }

        .header-badge {
            background: #1e40af;
            border-radius: 3mm;
            padding: 2mm 4mm;
            display: inline-block;
        }

        .header-badge span {
            color: #93c5fd;
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .page-header h1 {
            font-size: 13pt;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 1px;
            text-transform: uppercase;
            line-height: 1;
        }

        .page-header .subtitle {
            font-size: 7.5pt;
            color: #94a3b8;
            margin-top: 2px;
            letter-spacing: 0.3px;
        }

        /* accent line below header */
        .header-accent {
            height: 3px;
            background: linear-gradient(to right, #2563eb, #7c3aed, #0891b2);
            width: 100%;
        }

        /* ============================================================
           QR GRID TABLE  (height: 259mm = 297 - 24 header - 14 footer)
           ============================================================ */
        .qr-grid-table {
            width: 198mm;
            margin: 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
        }

        /* Filas: 3 filas × 86mm = 258mm → cabe dentro de 259mm */
        .qr-grid-table tr {
            height: 85mm;
        }

        /* Celdas: 3 columnas × 66mm = 198mm */
        .qr-grid-table td {
            width: 66mm;
            height: 85mm;
            padding: 2mm;
            vertical-align: top;
        }

        /* ============================================================
           QR CARD
           ============================================================ */
        .qr-card {
            width: 62mm;
            height: 81mm;
            border: 1px solid #e2e8f0;
            border-radius: 3.5mm;
            background: #ffffff;
            text-align: center;
            overflow: hidden;
            position: relative;
        }

        /* Thin colored top bar on card */
        .qr-card-accent {
            height: 2.5mm;
            background: #2563eb;
            border-radius: 3.5mm 3.5mm 0 0;
        }

        .qr-card-body {
            padding: 3mm 3mm 2mm 3mm;
        }

        /* ---- QR Image ---- */
        .qr-image-wrap {
            width: 44mm;
            height: 44mm;
            margin: 0 auto;
            border: 0.5pt solid #cbd5e1;
            border-radius: 2mm;
            padding: 1.5mm;
            background: #f8fafc;
        }

        .qr-image {
            width: 41mm;
            height: 41mm;
            display: block;
        }

        /* ---- Badge row ---- */
        .qr-badge-row {
            margin-top: 2.5mm;
            background: #eff6ff;
            border: 0.5pt dashed #93c5fd;
            border-radius: 2mm;
            padding: 1.5mm 2mm;
        }

        .qr-codigo {
            font-family: "Courier New", Courier, monospace;
            font-size: 8pt;
            font-weight: bold;
            color: #1d4ed8;
            word-break: break-all;
            line-height: 1.1;
        }

        /* ---- Denominacion ---- */
        .qr-denominacion {
            margin-top: 2mm;
            font-size: 6.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            line-height: 1.2;
            height: 8mm;
            overflow: hidden;
        }

        /* ---- Tipo bien pill ---- */
        .qr-tipo {
            margin-top: 1.5mm;
            display: inline-block;
            background: #f1f5f9;
            border-radius: 1.5mm;
            padding: 0.5mm 2mm;
            font-size: 5.5pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ---- Empty card (placeholder when row has < 3 items) ---- */
        .qr-card-empty {
            width: 62mm;
            height: 81mm;
            border: 1pt dashed #e2e8f0;
            border-radius: 3.5mm;
            background: #fafafa;
        }

        /* ============================================================
           FOOTER  (height: 14mm, pinned at bottom using table layout)
           ============================================================ */
        .page-footer {
            height: 14mm;
            background: #f8fafc;
            border-top: 0.5pt solid #e2e8f0;
            display: table;
            width: 100%;
        }

        .footer-inner {
            display: table-cell;
            vertical-align: middle;
            padding: 0 7mm;
        }

        .footer-left {
            display: table-cell;
            vertical-align: middle;
            font-size: 6.5pt;
            color: #64748b;
        }

        .footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-size: 6.5pt;
            color: #94a3b8;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-dot {
            display: inline-block;
            width: 2mm;
            height: 2mm;
            background: #2563eb;
            border-radius: 50%;
            vertical-align: middle;
            margin-right: 1.5mm;
        }
    </style>
</head>
<body>

@foreach($paginas as $indicePagina => $paginaBienes)
@php
    /* Split into rows of 3 for the table */
    $filas = $paginaBienes->values()->chunk(3);
@endphp

<div class="page-wrapper">

    {{-- ============ HEADER ============ --}}
    <div class="page-header">
        <div class="header-inner">
            <div class="header-logo-row">
                <div class="header-title-cell">
                    <h1> Inventario Patrimonial &mdash; QRs</h1>
                    <div class="subtitle">
                        {{ $total }} registros &nbsp;|&nbsp; {{ $filtro }} &nbsp;|&nbsp; P&aacute;gina {{ $indicePagina + 1 }} de {{ count($paginas) }}
                    </div>
                </div>
                <div class="header-badge-cell">
                    <div class="header-badge">
                        <span>{{ now()->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="header-accent"></div>

    {{-- ============ QR GRID ============ --}}
    <table class="qr-grid-table">
        <tbody>
        @foreach($filas as $fila)
            <tr>
                @foreach($fila as $bien)
                <td>
                    <div class="qr-card">
                        <div class="qr-card-accent"></div>
                        <div class="qr-card-body">

                            {{-- QR Image --}}
                            <div class="qr-image-wrap">
                                <img src="{{ $bien['qr_base64'] }}" class="qr-image" alt="QR">
                            </div>

                            {{-- Código patrimonial --}}
                            <div class="qr-badge-row">
                                <div class="qr-codigo">{{ $bien['codigo'] }}</div>
                            </div>

                            {{-- Denominación --}}
                            <div class="qr-denominacion">
                                {{ mb_strtoupper($bien['denominacion']) }}
                            </div>

                            {{-- Tipo bien --}}
                            <span class="qr-tipo">{{ $bien['tipo'] ?? '' }}</span>

                        </div>
                    </div>
                </td>
                @endforeach

                {{-- Fill empty cells to complete the row --}}
                @for($i = $fila->count(); $i < 3; $i++)
                <td>
                    <div class="qr-card-empty"></div>
                </td>
                @endfor
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- ============ FOOTER ============ --}}
    <div class="page-footer">
        <div class="footer-inner">
            <table class="footer-table">
                <tr>
                    <td class="footer-left">
                        <span class="footer-dot"></span>
                        Sistema de Gesti&oacute;n Patrimonial &mdash; Generado el {{ $fecha }}
                    </td>
                    <td class="footer-right">
                        P&aacute;gina {{ $indicePagina + 1 }} / {{ count($paginas) }}
                        &nbsp;&mdash;&nbsp;
                        Total QRs: {{ $total }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

</div>{{-- end page-wrapper --}}
@endforeach

</body>
</html>
