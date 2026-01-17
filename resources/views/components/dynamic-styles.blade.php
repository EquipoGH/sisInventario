{{-- 🎨 ESTILOS DINÁMICOS DEL SISTEMA --}}
<style>
    /* =====================================
       🎨 COLORES DINÁMICOS DEL SISTEMA
       ===================================== */

    /* 🔹 HEADERS DE MODALES */
    .modal-header.bg-primary {
        background-color: {{ $colores['color_header_crear'] ?? '#007bff' }} !important;
    }

    .modal-header.bg-info {
        background-color: {{ $colores['color_header_editar'] ?? '#17a2b8' }} !important;
    }

    .modal-header.bg-danger {
        background-color: {{ $colores['color_header_eliminar'] ?? '#dc3545' }} !important;
    }

    /* 📊 TABLA */
    .table thead.thead-dark {
        background-color: {{ $colores['color_tabla_header'] ?? '#343a40' }} !important;
        color: white !important;
    }

    .table tbody tr:hover,
    .editable-row:hover,
    .editable-cell:hover {
        background-color: {{ $colores['color_tabla_hover'] ?? '#e3f2fd' }} !important;
    }

    /* 🔘 BOTONES */
    .btn-primary {
        background-color: {{ $colores['color_btn_primario'] ?? '#007bff' }} !important;
        border-color: {{ $colores['color_btn_primario'] ?? '#007bff' }} !important;
    }

    .btn-success {
        background-color: {{ $colores['color_btn_success'] ?? '#28a745' }} !important;
        border-color: {{ $colores['color_btn_success'] ?? '#28a745' }} !important;
    }

    .btn-danger {
        background-color: {{ $colores['color_btn_danger'] ?? '#dc3545' }} !important;
        border-color: {{ $colores['color_btn_danger'] ?? '#dc3545' }} !important;
    }

    /* 🏷️ BADGES */
    .badge-info {
        background-color: {{ $colores['color_badge_info'] ?? '#17a2b8' }} !important;
    }

    .badge-warning {
        background-color: {{ $colores['color_badge_warning'] ?? '#ffc107' }} !important;
        color: #212529 !important;
    }

    /* ✨ ANIMACIÓN FADE-IN */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }

    /* 🔄 ICONOS DE ORDENAMIENTO */
    .sortable {
        cursor: pointer;
        user-select: none;
        transition: all 0.2s ease;
    }

    .sortable:hover {
        background-color: rgba(255, 255, 255, 0.1) !important;
    }

    .sort-icon {
        margin-left: 5px;
        opacity: 0.5;
        transition: opacity 0.2s;
    }

    .sortable:hover .sort-icon {
        opacity: 1;
    }

    /* 📝 FILAS EDITABLES */
    .editable-row, .editable-cell {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    /* 🎨 VISTA PREVIA CONFIGURACIÓN */
    .preview-hover:hover {
        background-color: {{ $colores['color_tabla_hover'] ?? '#e3f2fd' }} !important;
        cursor: pointer;
    }

    .form-control-color {
        width: 100%;
    }

    /* 🔍 INPUT GROUP SEARCH */
    .input-group-text.bg-primary {
        background-color: {{ $colores['color_btn_primario'] ?? '#007bff' }} !important;
        border-color: {{ $colores['color_btn_primario'] ?? '#007bff' }} !important;
    }
</style>
