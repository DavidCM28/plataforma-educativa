<link rel="stylesheet" href="<?= base_url('assets/css/profesores/tareas.css') ?>">
<section class="tareas-section">
    <div class="tareas-header">
        <h3><i class="fas fa-file-alt"></i> Exámenes del grupo</h3>
        <button id="btnNuevoExamen" class="btn-main"><i class="fas fa-plus"></i> Nuevo examen</button>
    </div>

    <div id="listaExamenes" class="tareas-grid" data-asignacion="<?= esc($asignacionId) ?>">
        <p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando exámenes...</p>
    </div>

    <!-- Modal Crear/Editar Examen -->
    <div id="modalExamen" class="modal hidden">
        <div class="modal-card tarea-modal">
            <span class="close">&times;</span>
            <h2 id="tituloModalExamen"><i class="fas fa-file-alt"></i> Nuevo examen</h2>

            <form id="formExamen" enctype="multipart/form-data" data-ajax="false">
                <input type="hidden" name="id" id="examenId">
                <input type="hidden" name="asignacion_id" value="<?= esc($asignacionId) ?>">

                <label>Título</label>
                <input type="text" name="titulo" id="tituloExamen" required>

                <label>Descripción</label>
                <textarea name="descripcion" id="descripcionExamen" rows="2"></textarea>

                <label>Instrucciones</label>
                <textarea name="instrucciones" id="instruccionesExamen" rows="2"></textarea>

                <div class="grid" style="display:grid;grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label>Tiempo (min)</label>
                        <input type="number" name="tiempo_minutos" id="tiempoExamen" min="1">
                    </div>
                    <div>
                        <label>Intentos máximos</label>
                        <input type="number" name="intentos_maximos" id="intentosExamen" min="1">
                    </div>
                    <div>
                        <label>Publicación</label>
                        <input type="datetime-local" name="fecha_publicacion" id="pubExamen">
                    </div>
                    <div>
                        <label>Cierre</label>
                        <input type="datetime-local" name="fecha_cierre" id="cierreExamen">
                    </div>
                </div>

                <hr style="opacity:.2;margin:12px 0">

                <!-- Constructor de preguntas -->
                <div class="tarea-subheader"
                    style="display:flex;align-items:center;gap:8px;justify-content:space-between;">
                    <h4 style="margin:0"><i class="fas fa-list-ol"></i> Preguntas</h4>
                    <button type="button" id="btnAgregarPregunta" class="btn-sec"><i class="fas fa-plus"></i> Agregar
                        pregunta</button>
                </div>

                <div id="contenedorPreguntas" class="tareas-grid" style="padding:0;gap:10px;"></div>

                <div class="modal-footer">
                    <button type="button" class="btn-sec cerrar-modal">Cancelar</button>
                    <button type="submit" class="btn-main"><i class="fas fa-save"></i> Guardar examen</button>
                </div>
            </form>
        </div>
    </div>
</section>