<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/profesores/grupos.css') ?>">

<section class="grupo-panel">
    <h2><i class="fas fa-users"></i> <?= esc($grupo['materia']) ?> - <?= esc($grupo['grupo']) ?></h2>

    <div class="tabs">
        <button class="tab-btn active" data-tab="listados">👥 Lista de Alumnos</button>
        <button class="tab-btn" data-tab="asistencias">📊 Asistencias</button>
        <button class="tab-btn" data-tab="tareas">📝 Tareas</button>
        <button class="tab-btn" data-tab="actividades">🎯 Actividades</button>
        <button class="tab-btn" data-tab="participacion">💬 Participación</button>
        <button class="tab-btn" data-tab="examenes">📚 Exámenes</button>
        <button class="tab-btn" data-tab="proyectos">🚀 Proyectos</button>
        <button class="tab-btn" data-tab="calificaciones">📈 Calificaciones</button>
    </div>

    <!-- Lista de Alumnos -->
    <div class="tab-content active" id="listados">
        <?= view('lms/profesor/grupos/listado', ['alumnos' => $alumnos]) ?>
    </div>

    <!-- Asistencias -->
    <div class="tab-content" id="asistencias">
        <div class="tabla-header">
            <h3>Control de Asistencias</h3>
            <button class="btn-primary">Registrar Asistencia</button>
        </div>
        <table class="alumnos-tabla">
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Asistencias</th>
                    <th>Faltas</th>
                    <th>Porcentaje</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($alumnos)): ?>
                    <?php foreach ($alumnos as $alumno): ?>
                        <tr>
                            <td><?= esc($alumno['nombre'] . ' ' . $alumno['apellido_paterno']) ?></td>
                            <td><span class="estado-badge estado-completado">15</span></td>
                            <td><span class="estado-badge estado-pendiente">2</span></td>
                            <td>88%</td>
                            <td>
                                <button class="btn-action btn-info">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No hay alumnos en este grupo.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Tareas -->
    <div class="tab-content" id="tareas">
        <?= view('lms/profesor/grupos/tareas', ['tareas' => $tareas, 'alumnos' => $alumnos]) ?>
    </div>

    <!-- Actividades -->
    <div class="tab-content" id="actividades">
        <div class="tabla-header">
            <h3>Actividades del Grupo</h3>
            <button class="btn-primary">Nueva Actividad</button>
        </div>
        <table class="alumnos-tabla">
            <thead>
                <tr>
                    <th>Actividad</th>
                    <th>Fecha</th>
                    <th>Entregados</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Presentación Oral</td>
                    <td>10/12/2024</td>
                    <td><?= count($alumnos) ?>/<?= count($alumnos) ?></td>
                    <td><span class="estado-badge estado-completado">Completado</span></td>
                    <td>
                        <button class="btn-action btn-info">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Debate Grupal</td>
                    <td>18/12/2024</td>
                    <td>15/<?= count($alumnos) ?></td>
                    <td><span class="estado-badge estado-en-progreso">En Progreso</span></td>
                    <td>
                        <button class="btn-action btn-info">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Participación -->
    <div class="tab-content" id="participacion">
        <div class="tabla-header">
            <h3>Participación en Clase</h3>
            <button class="btn-primary">Ver Estadísticas</button>
        </div>
        <table class="alumnos-tabla">
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Participaciones</th>
                    <th>Nivel</th>
                    <th>Última Actividad</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($alumnos)): ?>
                    <?php foreach ($alumnos as $alumno): ?>
                        <tr>
                            <td><?= esc($alumno['nombre'] . ' ' . $alumno['apellido_paterno']) ?></td>
                            <td>15</td>
                            <td>
                                <div class="nivel-participacion">
                                    <div class="nivel-punto activo"></div>
                                    <div class="nivel-punto activo"></div>
                                    <div class="nivel-punto activo"></div>
                                    <div class="nivel-punto"></div>
                                    <div class="nivel-punto"></div>
                                </div>
                            </td>
                            <td>Hoy</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No hay alumnos en este grupo.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Exámenes -->
    <div class="tab-content" id="examenes">
        <div class="tabla-header">
            <h3>Gestión de Exámenes</h3>
            <button class="btn-primary">Programar Examen</button>
        </div>
        <table class="alumnos-tabla">
            <thead>
                <tr>
                    <th>Examen</th>
                    <th>Fecha</th>
                    <th>Duración</th>
                    <th>Ponderación</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Examen Parcial 1</td>
                    <td>05/12/2024</td>
                    <td>2 horas</td>
                    <td>30%</td>
                    <td><span class="estado-badge estado-completado">Calificado</span></td>
                </tr>
                <tr>
                    <td>Examen Parcial 2</td>
                    <td>15/12/2024</td>
                    <td>2 horas</td>
                    <td>30%</td>
                    <td><span class="estado-badge estado-pendiente">Pendiente</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Proyectos -->
    <div class="tab-content" id="proyectos">
        <div class="tabla-header">
            <h3>Proyectos del Grupo</h3>
            <button class="btn-primary">Nuevo Proyecto</button>
        </div>
        <table class="alumnos-tabla">
            <thead>
                <tr>
                    <th>Proyecto</th>
                    <th>Fecha Entrega</th>
                    <th>Grupos</th>
                    <th>Progreso</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Proyecto Final - Sistema Web</td>
                    <td>20/12/2024</td>
                    <td>5/8</td>
                    <td>
                        <div class="progreso-bar">
                            <div class="progreso-fill" style="width: 60%"></div>
                        </div>
                    </td>
                    <td><span class="estado-badge estado-en-progreso">En Desarrollo</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Calificaciones -->
    <div class="tab-content" id="calificaciones">
        <?= view('lms/profesor/grupos/calificaciones', ['calificaciones' => $calificaciones, 'alumnos' => $alumnos]) ?>
    </div>
</section>

<script>
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.tab).classList.add('active');
        });
    });
</script>

<?= $this->endSection() ?>