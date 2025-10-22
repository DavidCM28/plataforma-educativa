<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Asignaciones</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/asignaciones.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/asignaciones-alumnos.css') ?>">
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
                            <div class="buscador-alumno">
                                <label>Buscar alumno:</label>
                                <input type="text" id="buscadorAlumno" placeholder="Escribe nombre o matrícula..."
                                    autocomplete="off">
                                <ul id="resultadosBusqueda" class="resultados-lista"></ul>

                                <!-- Al seleccionar, estos campos se llenan -->
                                <input type="hidden" name="alumno_id" id="alumno_id">
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
                </div>

                <!-- ==================== SUBTAB 2: ALUMNO ↔ GRUPO ==================== -->
                <div id="subtab-grupos" class="subtab-content">
                    <form id="formAsignarAlumnos" class="form-asignacion"
                        action="<?= base_url('admin/asignaciones-alumnos/asignar-alumno') ?>" <h3><i
                            class="fa-solid fa-user-plus"></i> Asignar Alumnos a Grupo</h3>
                        <small>Solo se mostrarán los alumnos que pertenezcan a la carrera del grupo
                            seleccionado.</small>

                        <div class="form-row">
                            <div>
                                <label>Carrera:</label>
                                <select id="filtroCarreraSelect">
                                    <option value="">-- Todas las carreras --</option>
                                    <?php foreach ($carreras as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= esc($c['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label>Grupo:</label>
                                <select name="grupo_id" id="grupoAlumnoSelect" required>
                                    <option value="">-- Selecciona grupo --</option>
                                    <?php foreach ($gruposPrimerCiclo as $g): ?>
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
                    <div class="gestion-grupos">
                        <label for="grupoPromover">Seleccionar grupo:</label>
                        <select id="grupoPromover">
                            <option value="">-- Selecciona grupo --</option>
                            <?php foreach ($grupos as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= esc($g['grupo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button id="btnPromoverGrupo" class="btn-nuevo" type="button">
                            <i class="fa-solid fa-arrow-up"></i> Promover al siguiente ciclo
                        </button>
                    </div>


                    <div class="tabla-asignados">
                        <h4><i class="fa-solid fa-users"></i> Alumnos inscritos</h4>

                        <div class="acciones-tabla">
                            <label><input type="checkbox" id="selectAllAlumnos"> Seleccionar todos</label>
                            <button id="btnEliminarSeleccionados" class="btn-mini btn-danger" type="button">
                                <i class="fa fa-trash"></i> Eliminar seleccionados
                            </button>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th></th> <!-- columna de selección -->
                                    <th>Matrícula</th>
                                    <th>Alumno</th>
                                    <th>Grupo</th>
                                    <th>Estatus</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAlumnosInscritos"></tbody>
                        </table>
                    </div>

                </div>
            </section>
        </div>
    </main>

    <script>
        const gruposPrimerCiclo = <?= json_encode($gruposPrimerCiclo) ?>;
        const gruposTotales = <?= json_encode($gruposTotales) ?>;
        const carrerasLista = <?= json_encode($carreras) ?>;
    </script>


    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script src="<?= base_url('assets/js/alert.js') ?>"></script>
    <script src="<?= base_url('assets/js/admin/asignaciones.js') ?>"></script>
    <script src="<?= base_url('assets/js/admin/asignaciones-alumnos.js') ?>"></script>
</body>

</html>