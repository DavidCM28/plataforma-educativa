<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Asignaciones</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/asignaciones.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <main class="content-dark">
        <div class="crud-container">
            <h2>Gestión de Asignaciones</h2>

            <div class="tabs">
                <button class="tab-btn active" data-tab="profesores">Profesores</button>
                <button class="tab-btn" data-tab="alumnos">Alumnos</button>
            </div>

            <!-- ✅ Flash -->
            <?php if (session()->getFlashdata('msg')): ?>
                <script>
                    document.addEventListener("DOMContentLoaded", () =>
                        Swal.fireSuccess("<?= session()->getFlashdata('msg') ?>")
                    );
                </script>
            <?php endif; ?>

            <!-- ================= PROFESORES ================= -->
            <section id="profesores" class="tab-content active">
                <form class="form-asignacion" action="<?= base_url('admin/asignaciones/asignar-profesor') ?>"
                    method="POST">
                    <h3>Asignar Profesor a Materia y Grupo</h3>
                    <small>Los alumnos del grupo se vincularán automáticamente a esta materia.</small>

                    <div class="form-group">
                        <label>Grupo:</label>
                        <select name="grupo_id" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($grupos as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= esc($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Materia:</label>
                        <select name="materia_id" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($materias as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= esc($m['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Profesor:</label>
                        <select name="profesor_id" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($profesores as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= esc($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ciclo:</label>
                        <select name="ciclo" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($ciclos as $c): ?>
                                <option value="<?= esc($c['nombre']) ?>"><?= esc($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div>
                            <label>Días:</label>
                            <select name="dias[]" multiple required>
                                <option value="L">Lunes</option>
                                <option value="M">Martes</option>
                                <option value="X">Miércoles</option>
                                <option value="J">Jueves</option>
                                <option value="V">Viernes</option>
                                <option value="S">Sábado</option>
                            </select>
                            <small>Puedes seleccionar varios con Ctrl o Shift</small>
                        </div>

                        <div>
                            <label>Hora inicio:</label>
                            <input type="time" name="hora_inicio" required>
                        </div>

                        <div>
                            <label>Hora fin:</label>
                            <input type="time" name="hora_fin" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <input type="text" name="aula" placeholder="Aula (opcional)">
                    </div>

                    <button type="submit" class="btn-nuevo"><i class="fa fa-plus"></i> Asignar</button>
                </form>

                <h3>Asignaciones actuales</h3>
                <table class="tabla-crud">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th>Materia</th>
                            <th>Profesor</th>
                            <th>Ciclo</th>
                            <th>Aula</th>
                            <th>Horario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($asignaciones as $a): ?>
                            <tr>
                                <td><?= esc($a['grupo']) ?></td>
                                <td><?= esc($a['materia']) ?></td>
                                <td><?= esc($a['profesor']) ?></td>
                                <td><?= esc($a['ciclo']) ?></td>
                                <td><?= esc($a['aula']) ?></td>
                                <td><?= esc($a['horario']) ?></td>
                                <td><button class="btn-action btn-delete"
                                        data-url="<?= base_url('admin/asignaciones/eliminar-profesor/' . $a['id']) ?>"><i
                                            class="fa fa-trash"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <!-- ================= ALUMNOS ================= -->
            <section id="alumnos" class="tab-content">
                <form class="form-asignacion" action="<?= base_url('admin/asignaciones/asignar-alumno') ?>"
                    method="POST">
                    <h3>Asignar Alumno a Grupo</h3>
                    <small>El alumno se vinculará automáticamente a todas las materias activas del grupo.</small>

                    <div class="form-group">
                        <label>Grupo:</label>
                        <select name="grupo_id" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($grupos as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= esc($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Alumno:</label>
                        <select name="alumno_id" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($alumnos as $a): ?>
                                <option value="<?= $a['id'] ?>"><?= esc($a['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-nuevo"><i class="fa fa-plus"></i> Inscribir</button>
                </form>

                <h3>Alumnos inscritos</h3>
                <table class="tabla-crud">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Grupo</th>
                            <th>Fecha</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inscripciones as $i): ?>
                            <tr>
                                <td><?= esc($i['alumno']) ?></td>
                                <td><?= esc($i['grupo']) ?></td>
                                <td><?= esc($i['fecha_inscripcion']) ?></td>
                                <td><?= esc($i['estatus']) ?></td>
                                <td><button class="btn-action btn-delete"
                                        data-url="<?= base_url('admin/asignaciones/eliminar-alumno/' . $i['id']) ?>"><i
                                            class="fa fa-trash"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(".tab-btn").forEach(btn => {
                btn.addEventListener("click", () => {
                    document.querySelectorAll(".tab-btn, .tab-content").forEach(el => el.classList.remove("active"));
                    btn.classList.add("active");
                    document.getElementById(btn.dataset.tab).classList.add("active");
                });
            });

            document.querySelectorAll(".btn-delete").forEach(btn => {
                btn.addEventListener("click", async () => {
                    const url = btn.dataset.url;
                    const confirm = await Swal.fireConfirm("¿Eliminar asignación?", "Esta acción no se puede deshacer");
                    if (confirm.isConfirmed) {
                        await fetch(url);
                        Swal.fireSuccess("Eliminado correctamente");
                        setTimeout(() => location.reload(), 1000);
                    }
                });
            });
        });
    </script>

    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script src="<?= base_url('assets/js/alert.js') ?>"></script>
</body>

</html>