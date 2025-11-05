<section class="entregas-section" data-tarea="<?= esc($tareaId) ?>">
    <header class="entregas-header">
        <button class="btn-sec" id="btnVolverTareas">
            <i class="fas fa-arrow-left"></i> Volver a tareas
        </button>
        <h3><i class="fas fa-folder-open"></i> Entregas — <?= esc($tarea['titulo']) ?></h3>
    </header>

    <div id="listaEntregas" class="entregas-contenedor">
        <p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando entregas...</p>
    </div>
</section>