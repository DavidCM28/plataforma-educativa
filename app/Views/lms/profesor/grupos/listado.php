<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/profesores/grupos.css') ?>">

<section class="grupo-panel">
    <h2><i class="fas fa-users"></i> Mis Grupos Asignados</h2>

    <?php if (!empty($asignaciones)): ?>
        <div class="grupos-grid">
            <?php foreach ($asignaciones as $asignacion): ?>
                <div class="grupo-card" onclick="window.location='<?= base_url('profesor/grupos/ver/' . $asignacion['id']) ?>'">
                    <div class="grupo-header">
                        <h3><?= esc($asignacion['materia'] ?? 'Materia') ?></h3>
                        <span class="grupo-codigo"><?= esc($asignacion['grupo'] ?? 'Grupo') ?></span>
                    </div>
                    <div class="grupo-info">
                        <p><i class="fas fa-door-open"></i> Aula: <?= esc($asignacion['aula'] ?? '-') ?></p>
                        <p><i class="fas fa-calendar-day"></i> Días: <?= esc($asignacion['dias'] ?? '-') ?></p>
                        <p><i class="fas fa-clock"></i> Hora: <?= esc($asignacion['hora'] ?? '-') ?></p>
                    </div>
                    <div class="grupo-actions">
                        <button class="btn btn-primary">Ver Detalles</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center; padding: 2rem;">No tienes grupos asignados.</p>
    <?php endif; ?>
</section>

<?= $this->endSection();
?>
