<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>
<script>
    window.examenId = <?= $examen['id'] ?>;
    window.base = "<?= base_url('/') ?>";
</script>


<link rel="stylesheet" href="<?= base_url('assets/css/profesores/examen_respuesta_detalle.css') ?>">
<script src="<?= base_url('assets/js/profesores/examen_respuesta_detalle.js') ?>" defer></script>
<link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">
<script src="<?= base_url('assets/js/alert.js') ?>"></script>
<!-- 🔔 Contenedor global de alertas -->
<div id="alertContainer" class="alert-container"></div>

<!-- ⚠️ Modal de confirmación -->
<div id="confirmModal" class="confirm-modal hidden">
    <div class="confirm-box">
        <h3 id="confirmTitle">Confirmar acción</h3>
        <p id="confirmMessage">¿Estás seguro de continuar?</p>
        <div class="confirm-buttons">
            <button id="confirmCancelar">Cancelar</button>
            <button id="confirmAceptar">Aceptar</button>
        </div>
    </div>
</div>
<section class="detalle-respuesta">
    <?php $examenId = $examen['id']; ?>


    <h2 class="titulo">
        <i class="fas fa-user-check"></i>
        Revisión de <?= esc($respuesta['alumno_nombre']) ?>
        <span class="mat"> (<?= esc($respuesta['matricula']) ?>) </span>
    </h2>

    <div class="preguntas-lista">
        <?php foreach ($detalles as $d): ?>
            <div class="pregunta-card">

                <div class="pregunta-header">
                    <h4 class="pregunta-titulo"><?= esc($d['pregunta']) ?></h4>
                    <span class="pts"><?= number_format($d['puntos'], 2) ?> pts</span>
                </div>

                <?php if (!empty($d['imagen'])): ?>
                    <div class="pregunta-imagen">
                        <img src="<?= base_url('uploads/examenes/' . $d['imagen']) ?>" alt="Imagen de la pregunta">
                    </div>
                <?php endif; ?>


                <?php if ($d['tipo'] === 'opcion'): ?>

                    <div class="respuesta-opcion">
                        <p><strong>Opción elegida:</strong> <?= esc($d['opcion_texto'] ?? '-') ?></p>
                        <p class="puntaje-obtenido"><strong>Puntos:</strong> <?= esc($d['puntos_obtenidos']) ?></p>
                    </div>

                <?php else: ?>

                    <label class="subtitulo">Respuesta del alumno:</label>
                    <textarea class="respuesta-abierta" disabled><?= esc($d['respuesta_texto'] ?? '') ?></textarea>

                    <div class="cal-buttons">
                        <button class="btn-grade btn-ok" data-id="<?= $d['id'] ?>"
                            data-pts="<?= $d['puntos'] ?>">Correcta</button>
                        <button class="btn-grade btn-mid" data-id="<?= $d['id'] ?>"
                            data-pts="<?= $d['puntos'] / 2 ?>">Parcial</button>
                        <button class="btn-grade btn-bad" data-id="<?= $d['id'] ?>" data-pts="0">Incorrecta</button>
                    </div>

                    <label class="obs-label">Observación:</label>
                    <textarea class="obs-input obs" data-id="<?= $d['id'] ?>"><?= esc($d['observacion'] ?? '') ?></textarea>

                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    </div>

    <div class="panel-flotante">

        <button id="btnVolverRespuestas" class="btn-volver">
            <i class="fa-solid fa-backward"></i> Volver
        </button>

        <div class="total-label">
            Total: <span id="totalPts"><?= number_format($respuesta['calificacion'] ?? 0, 2) ?></span> pts
        </div>

        <button id="btnGuardarTodo" class="btn-guardar-todo">
            <i class="fa-solid fa-floppy-disk"></i>
        </button>

    </div>




</section>

<?= $this->endSection() ?>