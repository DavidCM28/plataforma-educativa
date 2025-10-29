<?= $this->extend('lms/dashboard-plataforma') ?>

<?= $this->section('contenidoDashboard') ?>

<section class="dashboard-profesor">
    <link rel="stylesheet" href="<?= base_url('assets/css/profesores.css') ?>">

    <!-- ===============================
       🗂️ GRUPOS RESUMIDOS
       =============================== -->
    <section class="grupos-resumen">
        <h2 class="seccion-titulo">
            <i class="fas fa-layer-group"></i> Mis Grupos
        </h2>

        <div class="grupos-grid">
            <?php
            // Agrupar materias por grupo
            $grupos = [];
            foreach ($asignaciones as $a) {
                $grupos[$a['grupo']][] = $a;
            }
            ?>

            <?php foreach ($grupos as $nombreGrupo => $materias): ?>
                <div class="grupo-card">
                    <h3><i class="fas fa-users"></i> <?= esc($nombreGrupo) ?></h3>
                    <ul>
                        <?php foreach ($materias as $m): ?>
                            <li><i class="fas fa-book"></i> <?= esc($m['materia']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="grupo-footer">
                        <button><i class="fas fa-chart-bar"></i> Ver Reporte</button>
                        <button><i class="fas fa-folder-open"></i> Ver Detalles</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ===============================
       🧑‍🏫 MIS ASIGNATURAS
       =============================== -->
    <section class="asignaciones-modern">
        <h2 class="seccion-titulo">
            <i class="fas fa-chalkboard-teacher"></i> Mis Asignaturas
        </h2>

        <?php if (!empty($asignaciones)): ?>
            <div class="materias-grid">
                <?php foreach ($asignaciones as $a): ?>
                    <div class="materia-card" data-link="<?= base_url('profesor/grupos/ver/' . $a['id']) ?>">
                        <div class="materia-header">
                            <div class="materia-iniciales" style="background-color: <?= esc($a['color'] ?? '#3a3a3a') ?>;">
                                <?= strtoupper(substr($a['materia'], 0, 2)) ?>
                            </div>
                            <h3 class="materia-nombre"><?= esc($a['materia']) ?></h3>
                        </div>

                        <div class="materia-shortcuts">
                            <button title="Lista de alumnos"><i class="fas fa-users"></i></button>
                            <button title="Asistencias"><i class="fas fa-calendar-check"></i></button>
                            <button title="Tareas"><i class="fas fa-tasks"></i></button>
                            <button title="Proyectos"><i class="fas fa-rocket"></i></button>
                            <button title="Exámenes"><i class="fas fa-book"></i></button>
                            <button title="Participación"><i class="fas fa-comments"></i></button>
                            <button title="Calificaciones"><i class="fas fa-chart-line"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align:center;">No tienes asignaciones registradas.</p>
        <?php endif; ?>
    </section>

    <!-- ===============================
   📅 HORARIO SEMANAL
   =============================== -->
    <section class="horario-semanal">
        <h2 class="seccion-titulo">
            <i class="fas fa-calendar-alt"></i> Horario Semanal
        </h2>

        <?php
        $diasSemana = ['L' => 'Lunes', 'M' => 'Martes', 'X' => 'Miércoles', 'J' => 'Jueves', 'V' => 'Viernes'];
        ?>

        <div class="horario-grid">
            <?php foreach ($diasSemana as $clave => $nombreDia): ?>
                <div class="horario-dia">
                    <h4><i class="fas fa-calendar-day"></i> <?= $nombreDia ?></h4>

                    <?php
                    $hayClase = false;
                    foreach ($asignaciones as $a):
                        if (!empty($a['horario_detalle'][$clave])):
                            $hayClase = true;
                            foreach ($a['horario_detalle'][$clave] as $hora): ?>
                                <div class="horario-item" data-link="<?= base_url('profesor/grupos/ver/' . $a['id']) ?>">
                                    <strong>
                                        <i class="fas fa-book"></i> <?= esc($a['materia']) ?>
                                    </strong>
                                    <span class="grupo">
                                        <i class="fas fa-users"></i> <?= esc($a['grupo']) ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-clock"></i> <?= esc($hora) ?>
                                    </span>
                                    <?php if (!empty($a['aula'])): ?>
                                        <span class="aula">
                                            <i class="fas fa-door-open"></i> <?= esc($a['aula']) ?>
                                        </span>
                                    <?php endif; ?>


                                </div>
                            <?php endforeach;
                        endif;
                    endforeach;

                    if (!$hayClase): ?>
                        <p class="sin-clase">—</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</section>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // =============================================
        // 🔹 Card principal clickeable
        // =============================================
        document.querySelectorAll(".materia-card").forEach(card => {
            card.addEventListener("click", e => {
                if (!e.target.closest("button")) {
                    const link = card.dataset.link;
                    if (link) window.location.href = link;
                }
            });
        });

        // =============================================
        // 🔹 Horario clickeable
        // =============================================
        document.querySelectorAll(".horario-item").forEach(item => {
            item.style.cursor = "pointer";
            item.addEventListener("click", () => {
                const link = item.dataset.link;
                if (link) window.location.href = link;
            });
        });

        // =============================================
        // 🚀 BOTONES DE ATAJO: abren el grupo con el TAB correspondiente
        // =============================================
        document.querySelectorAll(".materia-shortcuts button").forEach(btn => {
            btn.addEventListener("click", e => {
                e.stopPropagation();

                const title = btn.getAttribute("title");
                const card = btn.closest(".materia-card");
                const linkBase = card?.dataset.link;

                if (!linkBase) return;

                // 🔸 Mapeo de shortcuts → pestañas del grupo
                const tabMap = {
                    "Lista de alumnos": "alumnos",
                    "Asistencias": "asistencias",
                    "Tareas": "tareas",
                    "Actividades": "actividades",
                    "Participación": "inicio",
                    "Exámenes": "examenes",
                    "Proyectos": "proyectos",
                    "Calificaciones": "calificaciones"
                };

                const tabDestino = tabMap[title] || "inicio";
                window.location.href = `${linkBase}?tab=${tabDestino}`;
            });
        });
    });
</script>


<?= $this->endSection() ?>