<?= $this->extend('lms/dashboard-plataforma') ?>

<?= $this->section('contenidoDashboard') ?>

<section class="dashboard-profesor">
    <link rel="stylesheet" href="<?= base_url('assets/css/profesores.css') ?>">

    <!-- ASIGNACIONES -->
    <section class="asignaciones-modern">
        <h2 class="seccion-titulo">
            <i class="fas fa-chalkboard-teacher"></i> Mis Asignaturas
        </h2>

        <?php if (!empty($asignaciones)): ?>
            <div class="materias-grid">
                <?php foreach ($asignaciones as $a): ?>
                    <div class="materia-card">
                        <div onclick="window.location='<?= base_url('profesor/grupos/ver/' . $a['id']) ?>'">
                            <div class="materia-header">
                                <h3><?= esc($a['materia']) ?> (<?= esc($a['clave_materia']) ?>)</h3>

                                <span class="materia-status active">Activa</span>
                            </div>
                        </div>


                        <div class="materia-info">
                            <p><i class="fas fa-users"></i> Grupo: <?= esc($a['grupo']) ?></p>
                            <p><i class="fas fa-door-open"></i> Aula: <?= esc($a['aula'] ?? '-') ?></p>
                            <p><i class="fas fa-calendar-day"></i> Días: <?= esc($a['dias'] ?? '-') ?></p>
                            <p><i class="fas fa-clock"></i> Hora: <?= esc($a['hora'] ?? '-') ?></p>
                        </div>

                        <div class="materia-actions">
                            <button class="btn-materiales">
                                <i class="fas fa-folder-open"></i> Materiales
                            </button>
                            <button class="btn-tareas">
                                <i class="fas fa-tasks"></i> Tareas
                            </button>
                            <button class="btn-calificaciones">
                                <i class="fas fa-chart-line"></i> Calificaciones
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align:center;">No tienes asignaciones registradas.</p>
        <?php endif; ?>
    </section>

    <!-- HORARIO SEMANAL -->
    <section class="horario-semanal">
        <h2 class="seccion-titulo">
            <i class="fas fa-calendar-alt"></i> Horario Semanal
        </h2>

        <div class="horario-grid">
            <?php
            $diasSemana = ['L' => 'Lunes', 'M' => 'Martes', 'X' => 'Miércoles', 'J' => 'Jueves', 'V' => 'Viernes', 'S' => 'Sábado'];
            foreach ($diasSemana as $clave => $nombreDia):
                ?>
                <div class="horario-dia">
                    <h4><?= $nombreDia ?></h4>
                    <?php
                    $hayClase = false;
                    foreach ($asignaciones as $a):
                        if (str_contains($a['dias'], $clave)):
                            $hayClase = true; ?>
                            <div class="horario-item">
                                <strong><?= esc($a['materia']) ?></strong>
                                <span><?= esc($a['hora']) ?></span>
                                <span class="aula"><?= esc($a['aula'] ?? '-') ?></span>
                            </div>
                        <?php endif; endforeach;
                    if (!$hayClase): ?>
                        <p class="sin-clase">—</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</section>

<?= $this->endSection() ?>