<link rel="stylesheet" href="<?= base_url('assets/css/alumnos/materia_tareas.css') ?>">

<section class="tareas-teams">
    <!-- ===========================
       📋 LISTA DE TAREAS
  ============================ -->
    <aside class="panel-lista">
        <div class="titulo-lista">
            <i class="fas fa-tasks"></i>
            <h3>Tareas</h3>
        </div>

        <!-- 🔍 Filtros -->
        <div class="filtros-tareas">
            <button class="filtro-btn" data-filtro="todas">Todas</button>
            <button class="filtro-btn activo" data-filtro="pendiente">Pendientes</button>
            <button class="filtro-btn" data-filtro="entregada">Entregadas</button>
            <button class="filtro-btn" data-filtro="vencida">Vencidas</button>
        </div>

        <ul id="listaTareas">
            <li class="sin-tareas">Cargando tareas...</li>
        </ul>
    </aside>

    <!-- ===========================
       📘 DETALLE DE TAREA
  ============================ -->
    <main class="panel-detalle" id="panelDetalle">
        <div class="detalle-vacio">
            <i class="fas fa-book-open"></i>
            <p>Selecciona una tarea para ver los detalles.</p>
        </div>
    </main>
</section>