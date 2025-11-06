<link rel="stylesheet" href="<?= base_url('assets/css/alumnos/materia_tareas.css') ?>">

<section class="tareas-teams"> <!-- 🔹 Cambiado de 'proyectos-teams' a 'tareas-teams' -->
    <!-- ===========================
       📋 LISTA DE PROYECTOS
    ============================ -->
    <aside class="panel-lista">
        <div class="titulo-lista">
            <i class="fas fa-diagram-project"></i>
            <h3>Proyectos</h3>
        </div>

        <!-- 🔍 Filtros -->
        <div class="filtros-proyectos">
            <button class="filtro-btn" data-filtro="todas">Todos</button>
            <button class="filtro-btn activo" data-filtro="pendiente">Pendientes</button>
            <button class="filtro-btn" data-filtro="entregado">Entregados</button>
            <button class="filtro-btn" data-filtro="vencido">Vencidos</button>
        </div>

        <ul id="listaProyectos">
            <li class="sin-proyectos">Cargando proyectos...</li>
        </ul>
    </aside>

    <!-- ===========================
       📘 DETALLE DE PROYECTO
    ============================ -->
    <main class="panel-detalle" id="panelDetalleProyecto">
        <div class="detalle-vacio">
            <i class="fas fa-folder-open"></i>
            <p>Selecciona un proyecto para ver los detalles.</p>
        </div>
    </main>
</section>