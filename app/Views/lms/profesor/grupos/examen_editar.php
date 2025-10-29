<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/profesores/examenes.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/profesores/tareas.css') ?>">

<section class="editor-examen">
    <div class="editor-header">
        <button class="btn-sec" onclick="history.back()"><i class="fas fa-arrow-left"></i> Regresar</button>
        <h2 id="tituloEditor"><i class="fas fa-file-alt"></i> <?= isset($examen) ? 'Editar examen' : 'Nuevo examen' ?>
        </h2>
    </div>

    <form id="formExamen" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= esc($examen['id'] ?? '') ?>">
        <input type="hidden" name="asignacion_id" value="<?= esc($asignacionId) ?>">

        <div class="editor-form">
            <div class="campo">
                <label>Título</label>
                <input type="text" name="titulo" value="<?= esc($examen['titulo'] ?? '') ?>" required>
            </div>

            <div class="campo">
                <label>Descripción</label>
                <textarea name="descripcion" rows="2"><?= esc($examen['descripcion'] ?? '') ?></textarea>
            </div>

            <div class="campo">
                <label>Instrucciones</label>
                <textarea name="instrucciones" rows="2"><?= esc($examen['instrucciones'] ?? '') ?></textarea>
            </div>

            <div class="grid">
                <div>
                    <label>Tiempo (min)</label>
                    <input type="number" name="tiempo_minutos" min="1"
                        value="<?= esc($examen['tiempo_minutos'] ?? '') ?>">
                </div>
                <div>
                    <label>Intentos máximos</label>
                    <input type="number" name="intentos_maximos" min="1"
                        value="<?= esc($examen['intentos_maximos'] ?? '') ?>">
                </div>
                <div>
                    <label>Publicación</label>
                    <input type="datetime-local" name="fecha_publicacion"
                        value="<?= !empty($examen['fecha_publicacion']) ? date('Y-m-d\TH:i', strtotime($examen['fecha_publicacion'])) : '' ?>">
                </div>
                <div>
                    <label>Cierre</label>
                    <input type="datetime-local" name="fecha_cierre"
                        value="<?= !empty($examen['fecha_cierre']) ? date('Y-m-d\TH:i', strtotime($examen['fecha_cierre'])) : '' ?>">
                </div>
            </div>
        </div>

        <hr>

        <div class="editor-preguntas">
            <div class="preguntas-header">
                <h3><i class="fas fa-list-ol"></i> Preguntas</h3>
            </div>

            <div id="contenedorPreguntas" class="preguntas-lista">
                <?php if (!empty($examen['preguntas'])): ?>
                    <?php foreach ($examen['preguntas'] as $idx => $p): ?>
                        <div class="tarea-card">
                            <strong>Pregunta <?= $idx + 1 ?></strong>
                            <p><?= esc($p['pregunta']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </form>
</section>
<div class="floating-actions">
    <button id="btnAgregarPregunta" title="Agregar pregunta">
        <i class="fas fa-plus"></i>
    </button>
    <button id="btnGuardarExamen" title="Guardar examen">
        <i class="fas fa-save"></i>
    </button>
</div>

<script>
    window.base_url = "<?= rtrim(site_url(), '/') ?>/";
</script>
<script src="<?= base_url('assets/js/alert.js') ?>"></script>
<script src="<?= base_url('assets/js/profesores/examen_editor.js') ?>"></script>


<?= $this->endSection() ?>