<section class="entregas-section" data-proyecto="<?= esc($proyectoId) ?>"
    data-asignacion="<?= esc($proyecto['asignacion_id']) ?>">
    <header class="entregas-header">
        <button class="btn-sec" id="btnVolverProyectos">
            <i class="fas fa-arrow-left"></i> Volver a proyectos
        </button>
        <h3><i class="fas fa-folder-open"></i> Entregas — <?= esc($proyecto['titulo']) ?></h3>
    </header>

    <div id="listaEntregasProyecto" class="entregas-contenedor">
        <p class="placeholder">
            <i class="fas fa-spinner fa-spin"></i> Cargando entregas...
        </p>
    </div>
</section>