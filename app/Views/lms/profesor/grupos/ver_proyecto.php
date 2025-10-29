<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<section class="ver-proyecto">
    <div class="card-proyecto">
        <h2><i class="fas fa-rocket"></i> <?= esc($proyecto['titulo']) ?></h2>
        <p><?= esc($proyecto['descripcion'] ?? 'Sin descripción.') ?></p>

        <?php if (!empty($proyecto['fecha_entrega'])): ?>
            <p><strong>📅 Entrega:</strong> <?= date('d/m/Y H:i', strtotime($proyecto['fecha_entrega'])) ?></p>
        <?php endif; ?>

        <?php if (!empty($proyecto['archivos'])): ?>
            <div class="archivos-lista">
                <h4>Archivos adjuntos</h4>
                <ul>
                    <?php foreach ($proyecto['archivos'] as $a): ?>
                        <li>
                            <a href="<?= base_url('uploads/proyectos/' . $a['archivo']) ?>" target="_blank">
                                <i class="fas fa-paperclip"></i> <?= esc($a['archivo']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <a href="<?= base_url('profesor/grupos/ver-proyecto/' . $proyecto['asignacion_id']) ?>" class="btn-main"
            style="margin-top:1rem;">
            <i class="fas fa-arrow-left"></i> Regresar al grupo
        </a>
    </div>
</section>

<?= $this->endSection() ?>