<!-- app/Views/profesor/grupos/tareas.php -->
<link rel="stylesheet" href="<?= base_url('assets/css/profesores/tareas.css') ?>">

<section class="tareas-section">
    <!-- ============================================================
         🧭 Encabezado del módulo
    ============================================================ -->
    <div class="tareas-header">
        <h3><i class="fas fa-tasks"></i> Tareas del grupo</h3>
        <button id="btnNuevaTarea" class="btn-main">
            <i class="fas fa-plus"></i> Nueva tarea
        </button>
    </div>

    <!-- ============================================================
         Filtros
    ============================================================ -->

    <div class="filtros-tareas">
        <label for="filtroParcial"><i class="fas fa-filter"></i> Filtrar por parcial:</label>
        <select id="filtroParcial">
            <option value="">Todos</option>
            <option value="1">1° Parcial</option>
            <option value="2">2° Parcial</option>
            <option value="3">3° Parcial</option>
        </select>
    </div>


    <!-- ============================================================
         📋 Contenedor dinámico de tareas
    ============================================================ -->
    <div id="listaTareas" class="tareas-grid" data-asignacion="<?= esc($asignacionId) ?>">
        <p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando tareas...</p>
    </div>

    <!-- ============================================================
         🧩 Modal Crear / Editar Tarea
    ============================================================ -->
    <div id="modalTarea" class="modal hidden">
        <div class="modal-card tarea-modal">
            <span class="close">&times;</span>
            <h2 id="tituloModalTarea"><i class="fas fa-file-alt"></i> Nueva tarea</h2>

            <form id="formTarea" enctype="multipart/form-data" data-ajax="false">

                <!-- Campos ocultos -->
                <input type="hidden" name="id" id="tareaId">
                <input type="hidden" name="asignacion_id" value="<?= esc($asignacionId) ?>">

                <!-- Título -->
                <label for="tituloTarea">Título</label>
                <input type="text" id="tituloTarea" name="titulo" required>

                <!-- Descripción -->
                <label for="descripcionTarea">Descripción</label>
                <textarea id="descripcionTarea" name="descripcion" rows="3"
                    placeholder="Instrucciones o detalles..."></textarea>

                <!-- Fecha -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="fechaEntrega">Fecha de entrega</label>
                        <input type="datetime-local" id="fechaEntrega" name="fecha_entrega">
                    </div>
                </div>

                <!-- Parcial -->
                <div class="form-group">
                    <label for="parcialNumero">Parcial</label>
                    <select id="parcialNumero" name="parcial_numero" required>
                        <option value="1">1° Parcial</option>
                        <option value="2">2° Parcial</option>
                        <option value="3">3° Parcial</option>
                    </select>
                </div>

                <!-- Criterio -->
                <div class="form-group">
                    <label for="criterioId">Criterio de evaluación</label>
                    <select id="criterioId" name="criterio_id" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($criterios as $c): ?>
                            <option value="<?= esc($c['id']) ?>">
                                <?= esc($c['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Info de ponderación -->
                <div id="infoCriterio" class="info-criterio" style="display:none; margin-top:10px;">
                    <p><strong>Porcentaje total del criterio:</strong>
                        <span id="porcentajeCriterio">0%</span>
                    </p>
                    <p><strong>Porcentaje restante:</strong>
                        <span id="porcentajeRestante">--%</span>
                    </p>
                    <label for="porcentajeTarea">Porcentaje que representará esta tarea:</label>
                    <input type="number" id="porcentajeTarea" name="porcentaje_tarea" placeholder="Ejemplo: 25" min="1"
                        max="100">
                </div>



                <!-- Archivos -->
                <div class="form-group archivo-group">
                    <label for="archivoTarea">Archivos adjuntos</label>
                    <input type="file" id="archivoTarea" name="archivos[]" multiple>
                    <div id="previewArchivos" class="archivo-preview"></div>
                </div>

                <!-- Botones -->
                <div class="modal-footer">
                    <button type="button" class="btn-sec cerrar-modal">Cancelar</button>
                    <button type="submit" class="btn-main">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>