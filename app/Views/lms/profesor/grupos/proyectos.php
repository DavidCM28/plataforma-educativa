<!-- app/Views/profesor/grupos/proyectos.php -->
<link rel="stylesheet" href="<?= base_url('assets/css/profesores/proyectos.css') ?>">

<section class="tareas-section">
    <div class="tareas-header">
        <h3><i class="fas fa-rocket"></i> Proyectos del grupo</h3>
        <button id="btnNuevoProyecto" class="btn-main"><i class="fas fa-plus"></i> Nuevo proyecto</button>
    </div>

    <div id="listaProyectos" class="tareas-grid" data-asignacion="<?= esc($asignacionId) ?>">
        <p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando proyectos...</p>
    </div>

    <div id="modalProyecto" class="modal hidden">
        <div class="modal-card tarea-modal">
            <span class="close">&times;</span>
            <h2 id="tituloModalProyecto"><i class="fas fa-file-alt"></i> Nuevo proyecto</h2>

            <form id="formProyecto" enctype="multipart/form-data" data-ajax="false">

                <input type="hidden" name="id" id="proyectoId">
                <input type="hidden" name="asignacion_id" value="<?= esc($asignacionId) ?>">

                <label for="tituloProyecto">Título</label>
                <input type="text" id="tituloProyecto" name="titulo" required>

                <label for="descripcionProyecto">Descripción</label>
                <textarea id="descripcionProyecto" name="descripcion" rows="3"></textarea>

                <label for="fechaEntregaProyecto">Fecha de entrega</label>
                <input type="datetime-local" id="fechaEntregaProyecto" name="fecha_entrega">

                <label for="archivoProyecto">Archivos adjuntos</label>
                <input type="file" id="archivoProyecto" name="archivos[]" multiple>
                <div id="previewArchivosProyecto" class="archivo-preview"></div>

                <div class="modal-footer">
                    <button type="button" class="btn-sec cerrar-modal">Cancelar</button>
                    <button type="submit" class="btn-main"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</section>