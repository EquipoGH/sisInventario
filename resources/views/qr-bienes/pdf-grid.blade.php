<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Códigos QR - Inventario Patrimonial</title>
    <style>
        /* CONFIGURACIÓN DE PÁGINA A4 RIGIDA */
        @page {
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            background: #ffffff;
            width: 210mm;
            height: 297mm;
        }

        /* HEADER MUCHO MÁS COMPACTO */
        .page-header {
            width: 100%;
            background: #0f172a;
            color: white;
            padding: 5mm;
            text-align: center;
            height: 20mm;
            overflow: hidden;
        }

        .page-header h1 {
            font-size: 14pt;
            letter-spacing: 1px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .page-header .info {
            font-size: 8pt;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* CONTENEDOR DE GRILLA CON ALTURA CONTROLADA */
        .grid-container {
            width: 210mm;
            height: 260mm; /* Espacio para los QRs */
            padding: 5mm;
            overflow: hidden;
        }

        /* CADA ITEM TENDRA ALTO FIJO */
        .qr-item {
            width: 63mm;
            height: 85mm; /* 3 filas de 85mm = 255mm. Cabe en 260mm */
            float: left;
            padding: 2mm;
            text-align: center;
        }

        .qr-card {
            border: 0.5pt solid #cbd5e1;
            border-radius: 4mm;
            padding: 5mm 3mm;
            height: 81mm; /* qr-item height - padding */
            width: 100%;
            background-color: #ffffff;
            overflow: hidden;
        }

        .qr-image {
            width: 42mm;
            height: 42mm;
            display: block;
            margin: 0 auto;
            border: 0.1pt solid #e2e8f0;
            padding: 1mm;
        }

        .qr-code-text {
            margin-top: 4mm;
            font-size: 11pt;
            font-weight: bold;
            color: #1d4ed8;
            font-family: monospace;
            background-color: #eff6ff;
            padding: 2mm 0;
            border-radius: 2mm;
            border: 0.5pt dashed #bfdbfe;
        }

        .qr-label {
            margin-top: 3mm;
            font-size: 8pt;
            color: #475569;
            line-height: 1.1;
            height: 10mm;
            overflow: hidden;
            font-weight: 600;
            text-transform: uppercase;
        }

        .page-footer {
            position: absolute;
            bottom: 5mm;
            width: 100%;
            text-align: center;
            font-size: 7pt;
            color: #64748b;
            border-top: 0.1pt solid #e2e8f0;
            padding-top: 2mm;
        }

        .page-break {
            page-break-after: always;
            clear: both;
        }
    </style>
</head>
<body>

@foreach($paginas as $indicePagina => $paginaBienes)
    <div class="page-header">
        <h1>INVENTARIO PATRIMONIAL - QRs</h1>
        <div class="info">
            Registros: {{ $total }} | Filtro: {{ $filtro }} | Página {{ $indicePagina + 1 }} de {{ count($paginas) }}
        </div>
    </div>

    <div class="grid-container">
        @foreach($paginaBienes as $bien)
            <div class="qr-item">
                <div class="qr-card">
                    <img src="{{ $bien['qr_base64'] }}" class="qr-image">
                    
                    <div class="qr-code-text">
                        {{ $bien['codigo'] }}
                    </div>
                    
                    <div class="qr-label">
                        {{ mb_strtoupper($bien['denominacion']) }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="page-footer">
        Generado por Sistema de Gestión Patrimonial | {{ $fecha }}
    </div>

    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
