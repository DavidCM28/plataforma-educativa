<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Asignaciones</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/asignaciones.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <div id="alertContainer" class="alert-container"></div>
    <!-- ✅ Modal de confirmación -->
    <div id="confirmModal" class="confirm-modal hidden">
        <div class="confirm-box">
            <h3 id="confirmTitle">Confirmar acción</h3>
            <p id="confirmMessage"></p>
            <div class="confirm-buttons">
                <button id="confirmAceptar" class="btn-confirmar">Aceptar</button>
                <button id="confirmCancelar" class="btn-cancelar">Cancelar</button>
            </div>
        </div>
    </div>



    <main class="content-dark">
        <div class="crud-container">
            <h2><i class="fa-solid fa-link"></i> Gestión de Asignaciones</h2>
            <p class="descripcion">Administra las asignaciones de profesores y alumnos de forma rápida y visual.</p>

            <div class="tabs">
                <button class="tab-btn active" data-tab="profesores"><i class="fa-solid fa-user-tie"></i>
                    Profesores</button>
                <button class="tab-btn" data-tab="alumnos"><i class="fa-solid fa-user-graduate"></i> Alumnos</button>
            </div>

            <!-- ==================== PROFESORES ==================== -->
            <section id="profesores" class="tab-content active">
                <form id="formAsignacion" class="form-asignacion" autocomplete="off">
                    <h3>Asignar Profesor a Materia y Grupo</h3>
                    <small>Los alumnos del grupo se vincularán automáticamente.</small>

                    <div class="form-row">
                        <div>
                            <label>Grupo:</label>
                            <select name="grupo_id" id="grupo_id" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($grupos as $g): ?>
                                    <option value="<?= $g['id'] ?>" data-turno="<?= esc($g['turno']) ?>">
                                        <?= esc($g['grupo']) ?> (<?= esc($g['turno']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label>Materia:</label>
                            <select name="materia_id" id="materia_id" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($materias as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= esc($m['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Indicador de frecuencias -->
                            <div id="frecuenciasInfo" class="frecuencias-info hidden">
                                <div class="frecuencia-barra">
                                    <div class="frecuencia-barra-progreso"></div>
                                </div>
                                <small class="frecuencia-texto">
                                    Selecciona una materia para ver las frecuencias asignadas.
                                </small>
                            </div>
                        </div>

                        <div>
                            <label>Profesor:</label>
                            <select name="profesor_id" id="profesor_id" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($profesores as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= esc($p['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label>Ciclo:</label>
                            <select name="ciclo_id" id="ciclo" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($ciclos as $c): ?>
                                    <option value="<?= $c['id'] ?>" data-nombre="<?= esc($c['nombre']) ?>">
                                        <?= esc($c['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label>Aula:</label>
                            <input type="text" name="aula" id="aula" placeholder="Ej. A-203">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Selecciona los días y horas de clase:</label>

                        <div id="horarioSelectorContainer">
                            <p class="turno-info">Selecciona un grupo para mostrar los horarios.</p>
                            <div id="horarioSelector" class="horario-selector hidden">
                                <div class="fila encabezado">
                                    <div class="celda hora">Hora</div>
                                    <div class="celda dia">L</div>
                                    <div class="celda dia">M</div>
                                    <div class="celda dia">X</div>
                                    <div class="celda dia">J</div>
                                    <div class="celda dia">V</div>
                                </div>
                                <!-- filas se generan dinámicamente -->
                            </div>
                        </div>

                        <small class="ayuda">Haz clic o arrastra para seleccionar los bloques de clase.</small>
                    </div>

                    <input type="hidden" name="horarios_json" id="horarios_json">

                    <button type="submit" class="btn-nuevo">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                    <button type="button" id="btnCancelar" class="btn-secundario hidden">
                        <i class="fa fa-xmark"></i> Cancelar edición
                    </button>
                </form>
            </section>

            <!-- ==================== ALUMNOS ==================== -->
            <section id="alumnos" class="tab-content">
                <div class="subtabs">
                    <button class="subtab-btn active" data-subtab="carreras">
                        <i class="fa-solid fa-graduation-cap"></i> Vincular a Carrera
                    </button>
                    <button class="subtab-btn" data-subtab="grupos">
                        <i class="fa-solid fa-users"></i> Asignar a Grupo
                    </button>
                </div>

                <!-- ==================== SUBTAB 1: ALUMNO ↔ CARRERA ==================== -->
                <div id="subtab-carreras" class="subtab-content active">
                    <form id="formVincularCarrera" class="form-asignacion"
                        action="<?= base_url('admin/asignaciones/vincular-alumno-carrera') ?>" method="POST">
                        <h3><i class="fa-solid fa-link"></i> Vincular Alumno a Carrera</h3>

                        <div class="form-row">
                            <div>
                                <label>Alumno:</label>
                                <select name="alumno_id" id="alumnoSelect" required>
                                    <option value="">-- Selecciona alumno --</option>
                                    <?php foreach ($alumnos as $a): ?>
                                        <option value="<?= $a['id'] ?>"><?= esc($a['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>Carrera:</label>
                                <select name="carrera_id" id="carreraSelect" required>
                                    <option value="">-- Selecciona carrera --</option>
                                    <?php foreach ($carreras as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= esc($c['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn-nuevo">
                            <i class="fa fa-save"></i> Vincular
                        </button>
                    </form>

                    <div class="tabla-asignados">
                        <h4><i class="fa-solid fa-table-list"></i> Alumnos y sus Carreras</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Alumno</th>
                                    <th>Carrera</th>
                                    <th>Estatus</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAlumnoCarrera">
                                <?php foreach ($vinculos as $v): ?>
                                    <tr>
                                        <td><?= $v['id'] ?></td>
                                        <td><?= esc($v['alumno']) ?></td>
                                        <td><?= esc($v['carrera']) ?></td>
                                        <td><span class="badge"><?= esc($v['estatus']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== SUBTAB 2: ALUMNO ↔ GRUPO ==================== -->
                <div id="subtab-grupos" class="subtab-content">
                    <form id="formAsignarAlumnos" class="form-asignacion"
                        action="<?= base_url('admin/asignaciones/asignar-alumno') ?>" method="POST">
                        <h3><i class="fa-solid fa-user-plus"></i> Asignar Alumnos a Grupo</h3>
                        <small>Solo se mostrarán los alumnos que pertenezcan a la carrera del grupo
                            seleccionado.</small>

                        <div class="form-row">
                            <div>
                                <label>Grupo:</label>
                                <select name="grupo_id" id="grupoAlumnoSelect" required>
                                    <option value="">-- Selecciona grupo --</option>
                                    <?php foreach ($grupos as $g): ?>
                                        <option value="<?= $g['id'] ?>"><?= esc($g['grupo']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label>Alumnos disponibles:</label>
                                <select name="alumnos[]" id="alumnosSelect" multiple size="8" required
                                    style="min-width:280px;">
                                    <option value="">Selecciona un grupo primero...</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn-nuevo">
                            <i class="fa fa-plus"></i> Inscribir seleccionados
                        </button>
                    </form>

                    <div class="tabla-asignados">
                        <h4><i class="fa-solid fa-users"></i> Alumnos inscritos</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Alumno</th>
                                    <th>Grupo</th>
                                    <th>Estatus</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAlumnosInscritos">
                                <?php foreach ($inscripciones as $i): ?>
                                    <tr>
                                        <td><?= $i['id'] ?></td>
                                        <td><?= esc($i['alumno']) ?></td>
                                        <td><?= esc($i['grupo']) ?></td>
                                        <td><span class="badge"><?= esc($i['estatus'] ?? 'Inscrito') ?></span></td>
                                        <td>
                                            <button type="button" class="btn-mini btn-eliminar" data-id="<?= $i['id'] ?>">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script src="<?= base_url('assets/js/alert.js') ?>"></script>
    <script src="<?= base_url('assets/js/admin/asignaciones.js') ?>"></script>
    <script>
        document.querySelectorAll(".tab-btn").forEach((btn) => {
            btn.addEventListener("click", () => {
                // Quitar clase active de todos
                document.querySelectorAll(".tab-btn").forEach((b) => b.classList.remove("active"));
                document.querySelectorAll(".tab-content").forEach((c) => c.classList.remove("active"));

                // Activar la pestaña seleccionada
                btn.classList.add("active");
                const tabId = btn.dataset.tab;
                document.getElementById(tabId).classList.add("active");
            });
        });
        // ==========================================================
        // 🧩 Subpestañas (Carrera / Grupo)
        // ==========================================================
        document.querySelectorAll(".subtab-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                document.querySelectorAll(".subtab-btn").forEach(b => b.classList.remove("active"));
                document.querySelectorAll(".subtab-content").forEach(c => c.classList.remove("active"));

                btn.classList.add("active");
                document.getElementById("subtab-" + btn.dataset.subtab).classList.add("active");
            });
        });

        // ==========================================================
        // 🎓 Vincular alumno a carrera (POST simple)
        // ==========================================================
        const formVincularCarrera = document.getElementById("formVincularCarrera");
        formVincularCarrera?.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(formVincularCarrera);
            const res = await fetch(`${baseUrl}admin/asignaciones/vincular-alumno-carrera`, {
                method: "POST",
                body: formData
            });
            const data = await res.json();
            mostrarAlerta(data.msg, data.ok ? "success" : "error");
            if (data.ok) setTimeout(() => window.location.reload(), 1000);
        });

    </script>


</body>

</html>