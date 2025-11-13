<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/alumnos/examen_resolver.css') ?>">
<script src="<?= base_url('assets/js/alert.js') ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">

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

<section class="examen-resolver">

    <header class="resolver-header">
        <h2><i class="fas fa-book-open"></i> <?= esc($examen['titulo']) ?></h2>
        <div class="info">
            <span><i class="far fa-clock"></i> Tiempo:
                <?= $examen['tiempo_minutos'] ? $examen['tiempo_minutos'] . ' min' : 'Sin límite' ?></span>
            <span><i class="fas fa-layer-group"></i> Parcial <?= esc($examen['parcial_num']) ?></span>

            <?php
            // Obtener el porcentaje del criterio asociado al examen (si existe)
            $criterioPorcentaje = $examen['criterio_porcentaje'] ?? null;
            ?>
            <span><i class="fas fa-chart-pie"></i>
                <?= $criterioPorcentaje
                    ? "Este examen equivale al {$criterioPorcentaje}% del parcial (100 pts)"
                    : "100 pts = porcentaje del criterio" ?>
            </span>
        </div>
    </header>

    <div class="instrucciones">
        <p><?= nl2br(esc($examen['instrucciones'])) ?></p>
    </div>

    <form id="formExamen" data-id="<?= $examen['id'] ?>" data-minutos="<?= esc($examen['tiempo_minutos']) ?>">
        <?php foreach ($examen['preguntas'] as $i => $p): ?>
    <div class="pregunta-card <?= $p['es_extra'] ? 'extra' : '' ?>">
        <div class="pregunta-header">
            <h4>Pregunta <?= $i + 1 ?> <?= $p['es_extra'] ? '<span class="extra-tag">💎 Puntos extra</span>' : '' ?></h4>
            <span class="puntaje"><?= esc($p['puntos']) ?> pts</span>
        </div>

        <p class="texto-pregunta"><?= esc($p['pregunta']) ?></p>

        <?php if (!empty($p['imagen'])): ?>
            <img src="<?= base_url('uploads/examenes/' . $p['imagen']) ?>" alt="Imagen de apoyo" class="img-pregunta">
        <?php endif; ?>

        <?php if ($p['tipo'] === 'opcion'): ?>
            <?php foreach ($p['opciones'] as $op): ?>
                <?php
                    $checked = isset($examen['respuestas_previas']["pregunta_{$p['id']}"]) &&
                               $examen['respuestas_previas']["pregunta_{$p['id']}"] == $op['id'];
                ?>
                <label class="opcion">
                    <input type="radio" name="pregunta_<?= $p['id'] ?>"
                           value="<?= esc($op['id']) ?>"
                           <?= $checked ? 'checked' : '' ?>>
                    <span><?= esc($op['texto']) ?></span>
                </label>
            <?php endforeach; ?>
        <?php else: ?>
            <?php
                $valorGuardado = $examen['respuestas_previas']["pregunta_{$p['id']}"] ?? '';
            ?>
            <textarea name="pregunta_<?= $p['id'] ?>" rows="4" placeholder="Escribe tu respuesta..."><?= esc($valorGuardado) ?></textarea>
        <?php endif; ?>
    </div>
<?php endforeach; ?>


        <div class="acciones-final">
            <button type="submit" class="btn-main">
                <i class="fas fa-check"></i> Enviar examen
            </button>
        </div>
        <button type="button" id="resetPrueba" class="btn-sec" style="margin-top:20px;">
            🔄 Reiniciar contador (modo prueba)
        </button>
    </form>
</section>

<script src="<?= base_url('assets/js/alumnos/examen_resolver.js') ?>"></script>

<?= $this->endSection() ?>