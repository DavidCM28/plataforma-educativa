<link rel="stylesheet" href="<?= base_url('assets/css/profesores/tareas.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/profesores/proyectos.css') ?>">

<section class="tareas-section">

    <!-- 🚀 Encabezado igual al de proyectos -->
    <div class="tareas-header">
        <h3><i class="fas fa-file-alt"></i> Exámenes del grupo</h3>
        <button id="btnNuevoExamen" class="btn-main">
            <i class="fas fa-plus"></i> Nuevo examen
        </button>
    </div>

    <!-- 🔍 Filtro igual al de proyectos -->
    <div class="filtros-tareas">
        <label for="filtroEstadoExamen">
            <i class="fas fa-filter"></i> Filtrar por estado:
        </label>
        <select id="filtroEstadoExamen">
            <option value="">Todos</option>
            <option value="borrador">Borrador</option>
            <option value="publicado">Publicado</option>
            <option value="cerrado">Cerrado</option>
        </select>
    </div>

    <!-- 📋 Grid/tabla igual a proyectos/tareas -->
    <div id="listaExamenes" class="tareas-grid" data-asignacion="<?= esc($asignacionId) ?>">
        <p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando exámenes...</p>
    </div>
</section>