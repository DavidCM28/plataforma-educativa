<!-- app/Views/profesor/grupos/proyectos.php -->
<link rel="stylesheet" href="<?= base_url('assets/css/profesores/proyectos.css') ?>">

<section class="tareas-section">
    <!-- ============================================================
         🚀 Encabezado del módulo de proyectos
    ============================================================ -->
    <div class="tareas-header">
        <h3><i class="fas fa-rocket"></i> Proyectos del grupo</h3>
        <button id="btnNuevoProyecto" class="btn-main">
            <i class="fas fa-plus"></i> Nuevo proyecto
        </button>
    </div>

    <!-- ============================================================
         🔍 Filtros (por parcial)
    ============================================================ -->
    <div class="filtros-tareas">
        <label for="filtroParcialProyecto">
            <i class="fas fa-filter"></i> Filtrar por parcial:
        </label>
        <select id="filtroParcialProyecto">
            <option value="">Todos</option>
            <option value="1">1° Parcial</option>
            <option value="2">2° Parcial</option>
            <option value="3">3° Parcial</option>
        </select>
    </div>

    <!-- ============================================================
         📋 Contenedor dinámico de proyectos
    ============================================================ -->
    <div id="listaProyectos" class="tareas-grid" data-asignacion="<?= esc($asignacionId) ?>">
        <p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando proyectos...</p>
    </div>

    <!-- ============================================================
         🧩 Modal Crear / Editar Proyecto
    ============================================================ -->
    <div id="modalProyecto" class="modal hidden">
        <div class="modal-card tarea-modal">
            <span class="close">&times;</span>
            <h2 id="tituloModalProyecto"><i class="fas fa-file-alt"></i> Nuevo proyecto</h2>

            <form id="formProyecto" enctype="multipart/form-data" data-ajax="false">
                <!-- Campos ocultos -->
                <input type="hidden" name="id" id="proyectoId">
                <input type="hidden" name="asignacion_id" value="<?= esc($asignacionId) ?>">

                <!-- Título -->
                <label for="tituloProyecto">Título</label>
                <input type="text" id="tituloProyecto" name="titulo" required>

                <!-- Descripción -->
                <label for="descripcionProyecto">Descripción</label>
                <textarea id="descripcionProyecto" name="descripcion" rows="3"
                    placeholder="Instrucciones o detalles..."></textarea>

                <!-- Fecha -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="fechaEntregaProyecto">Fecha de entrega</label>
                        <input type="datetime-local" id="fechaEntregaProyecto" name="fecha_entrega">
                    </div>
                </div>

                <!-- Parcial -->
                <div class="form-group">
                    <label for="parcialNumeroProyecto">Parcial</label>
                    <select id="parcialNumeroProyecto" name="parcial_numero" required>
                        <option value="1">1° Parcial</option>
                        <option value="2">2° Parcial</option>
                        <option value="3">3° Parcial</option>
                    </select>
                </div>

                <!-- Criterio -->
                <div class="form-group">
                    <label for="criterioIdProyecto">Criterio de evaluación</label>
                    <select id="criterioIdProyecto" name="criterio_id" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($criterios as $c): ?>
                            <option value="<?= esc($c['id']) ?>"><?= esc($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Info de ponderación -->
                <div id="infoCriterioProyecto" class="info-criterio" style="display:none; margin-top:10px;">
                    <p><strong>Porcentaje total del criterio:</strong>
                        <span id="porcentajeCriterioProyecto">0%</span>
                    </p>
                    <p><strong>Porcentaje restante:</strong>
                        <span id="porcentajeRestanteProyecto">--%</span>
                    </p>
                    <label for="porcentajeProyecto">Porcentaje que representará este proyecto:</label>
                    <input type="number" id="porcentajeProyecto" name="porcentaje_proyecto" placeholder="Ejemplo: 30"
                        min="1" max="100">
                </div>

                <!-- Archivos -->
                <div class="form-group archivo-group">
                    <label for="archivoProyecto">Archivos adjuntos</label>
                    <input type="file" id="archivoProyecto" name="archivos[]" multiple>
                    <div id="previewArchivosProyecto" class="archivo-preview"></div>
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