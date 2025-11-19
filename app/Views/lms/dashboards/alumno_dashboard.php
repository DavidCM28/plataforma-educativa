<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/alumnos.css') ?>">

<section class="dashboard-alumno">

    <!-- =======================================================
       📚 MATERIAS INSCRITAS
  ======================================================= -->
    <section class="materias-alumno">
        <h2 class="seccion-titulo">
            <i class="fas fa-chalkboard-teacher"></i> Mis Materias
        </h2>

        <?php if (!empty($materias)): ?>
            <div class="materias-grid">
                <?php foreach ($materias as $m): ?>
                    <div class="materia-card" data-link="<?= base_url('alumno/materias/ver/' . $m['asignacion_id']) ?>">
                        <div class="materia-header">
                            <div class="materia-iniciales" style="background-color: <?= esc($m['color'] ?? '#3a3a3a') ?>;">
                                <?= strtoupper(substr($m['materia'], 0, 2)) ?>
                            </div>
                            <h3 class="materia-nombre"><?= esc($m['materia']) ?></h3>
                            <p class="materia-profesor"><i class="fas fa-user"></i> <?= esc($m['profesor']) ?></p>
                            <p class="materia-grupo"><i class="fas fa-users"></i> Grupo <?= esc($m['grupo']) ?></p>
                        </div>

                        <div class="materia-shortcuts">
                            <button title="Tareas"><i class="fas fa-tasks"></i></button>
                            <button title="Proyectos"><i class="fas fa-rocket"></i></button>
                            <button title="Exámenes"><i class="fas fa-book"></i></button>
                            <button title="Asistencias"><i class="fas fa-calendar-check"></i></button>
                            <button title="Calificaciones"><i class="fas fa-chart-line"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align:center;">Aún no estás inscrito en materias.</p>
        <?php endif; ?>
    </section>

    <!-- =======================================================
       📅 HORARIO SEMANAL
  ======================================================= -->
    <section class="horario-semanal">
        <h2 class="seccion-titulo">
            <i class="fas fa-calendar-alt"></i> Mi Horario
        </h2>

        <?php
        $diasSemana = ['L' => 'Lunes', 'M' => 'Martes', 'X' => 'Miércoles', 'J' => 'Jueves', 'V' => 'Viernes'];
        ?>

        <div class="horario-grid">
            <?php foreach ($diasSemana as $clave => $nombreDia): ?>
                <div class="horario-dia">
                    <h4><i class="fas fa-calendar-day"></i> <?= $nombreDia ?></h4>

                    <?php if (!empty($horario[$clave])): ?>
                        <?php foreach ($horario[$clave] as $clase): ?>
                            <div class="horario-item"
                                data-link="<?= base_url('alumno/materias/ver/' . ($clase['materia_id'] ?? '')) ?>">
                                <strong><i class="fas fa-book"></i> <?= esc($clase['materia']) ?></strong>
                                <span><i class="fas fa-clock"></i> <?= esc($clase['hora']) ?></span>
                                <?php if (!empty($clase['aula'])): ?>
                                    <span class="aula"><i class="fas fa-door-open"></i> <?= esc($clase['aula']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="sin-clase">—</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</section>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Hacer clic en tarjetas de materia
        document.querySelectorAll(".materia-card").forEach(card => {
            card.addEventListener("click", e => {
                if (!e.target.closest("button")) {
                    const link = card.dataset.link;
                    if (link) window.location.href = link;
                }
            });
        });

        // Atajos hacia secciones específicas de la materia
        document.querySelectorAll(".materia-shortcuts button").forEach(btn => {
            btn.addEventListener("click", e => {
                e.stopPropagation();
                const title = btn.getAttribute("title");
                const card = btn.closest(".materia-card");
                const base = card.dataset.link;
                const map = {
                    "Tareas": "tareas",
                    "Proyectos": "proyectos",
                    "Exámenes": "examenes",
                    "Asistencias": "asistencias",
                    "Calificaciones": "calificaciones"
                };
                const tab = map[title] || "inicio";
                window.location.href = `${base}?tab=${tab}`;
            });
        });

        // Horario clickeable
        document.querySelectorAll(".horario-item").forEach(i => {
            i.style.cursor = "pointer";
            i.addEventListener("click", () => {
                const link = i.dataset.link;
                if (link) window.location.href = link;
            });
        });
    });
</script>

<?= $this->endSection() ?>