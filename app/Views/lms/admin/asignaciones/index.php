<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Asignaciones</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/asignaciones.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <div id="alertContainer" class="alert-container"></div>

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
                                        <?= esc($g['nombre']) ?> (<?= esc($g['turno']) ?>)
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
                            <select name="ciclo" id="ciclo" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($ciclos as $c): ?>
                                    <option value="<?= $c['nombre'] ?>"><?= esc($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label>Hora inicio:</label>
                            <select name="hora_inicio" id="hora_inicio" required>
                                <option value="">-- Selecciona --</option>
                            </select>
                        </div>
                        <div>
                            <label>Hora fin:</label>
                            <select name="hora_fin" id="hora_fin" required>
                                <option value="">-- Selecciona --</option>
                            </select>
                        </div>

                        <div>
                            <label>Aula:</label>
                            <input type="text" name="aula" id="aula" placeholder="Ej. A-203">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Días de clase:</label>
                        <div class="dias-check">
                            <?php
                            $dias = ['L' => 'Lun', 'M' => 'Mar', 'X' => 'Mié', 'J' => 'Jue', 'V' => 'Vie'];
                            foreach ($dias as $k => $d): ?>
                                <label><input type="checkbox" name="dias[]" value="<?= $k ?>"> <?= $d ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn-nuevo"><i class="fa fa-save"></i> Guardar</button>
                    <button type="button" id="btnCancelar" class="btn-secundario hidden">
                        <i class="fa fa-xmark"></i> Cancelar edición
                    </button>
                </form>

                <!-- 🗓️ HORARIO VISUAL -->
                <div id="horarioGrupo" class="horario-grid hidden">
                    <h4 style="margin: 1rem 0; color: var(--primary);">
                        <i class="fa-regular fa-calendar-days"></i> Horario actual del grupo
                    </h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Lun</th>
                                <th>Mar</th>
                                <th>Mié</th>
                                <th>Jue</th>
                                <th>Vie</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyHorario"></tbody>
                    </table>
                </div>
            </section>

            <!-- ==================== ALUMNOS ==================== -->
            <section id="alumnos" class="tab-content">
                <form class="form-asignacion" action="<?= base_url('admin/asignaciones/asignar-alumno') ?>"
                    method="POST">
                    <h3>Asignar Alumno a Grupo</h3>
                    <div class="form-row">
                        <div>
                            <label>Grupo:</label>
                            <select name="grupo_id" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($grupos as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= esc($g['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label>Alumno:</label>
                            <select name="alumno_id" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($alumnos as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= esc($a['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-nuevo"><i class="fa fa-plus"></i> Inscribir</button>
                </form>
            </section>
        </div>
    </main>

    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script src="<?= base_url('assets/js/alert.js') ?>"></script>

    <script>
        const grupoSelect = document.getElementById("grupo_id");
        const horarioContainer = document.getElementById("horarioGrupo");
        const tbodyHorario = document.getElementById("tbodyHorario");

        // 🕒 Plantillas fijas de horarios
        const HORARIOS = {
            "Matutino": [
                "07:30", "08:20", "09:10", "10:00", "10:50", "11:40", "12:30", "13:20", "14:10", "15:00"
            ],
            "Vespertino": [
                "16:40", "17:20", "18:00", "18:40", "19:20", "20:00", "20:40", "21:20", "22:00"
            ]
        };

        const selectInicio = document.getElementById("hora_inicio");
        const selectFin = document.getElementById("hora_fin");

        // 🕒 Generar selects de hora según turno
        function generarSelectHoras(turno = "Matutino") {
            const bloques = HORARIOS[turno];
            selectInicio.innerHTML = '<option value="">-- Selecciona --</option>';
            selectFin.innerHTML = '<option value="">-- Selecciona --</option>';

            // Llenar ambas listas
            bloques.forEach(h => {
                const opt1 = document.createElement("option");
                const opt2 = document.createElement("option");
                opt1.value = h;
                opt2.value = h;
                opt1.textContent = h;
                opt2.textContent = h;
                selectInicio.appendChild(opt1);
                selectFin.appendChild(opt2);
            });

            // Ajuste lógico: la hora fin debe ser posterior a inicio
            selectInicio.onchange = () => {
                const idx = selectInicio.selectedIndex;
                selectFin.innerHTML = '<option value="">-- Selecciona --</option>';
                bloques.forEach((h, i) => {
                    if (i > idx - 1) {
                        const opt = document.createElement("option");
                        opt.value = h;
                        opt.textContent = h;
                        selectFin.appendChild(opt);
                    }
                });
            };
        }


        // 🗓️ Generar tabla según turno
        function generarTablaHorario(turno = "Matutino") {
            const bloques = HORARIOS[turno];
            tbodyHorario.innerHTML = "";
            bloques.forEach(hora => {
                const fila = document.createElement("tr");
                fila.innerHTML = `<td>${hora}</td>` +
                    ["L", "M", "X", "J", "V"].map(() => `<td class="disponible">—</td>`).join("");
                tbodyHorario.appendChild(fila);
            });
        }

        // 🔄 Cargar horario actual del grupo
        grupoSelect.addEventListener("change", async () => {
            const grupoId = grupoSelect.value;
            if (!grupoId) {
                horarioContainer.classList.add("hidden");
                return;
            }

            const turno = grupoSelect.selectedOptions[0].dataset.turno || "Matutino";
            generarSelectHoras(turno);
            generarTablaHorario(turno);
            horarioContainer.classList.remove("hidden");


            try {
                const res = await fetch(`<?= base_url('admin/asignaciones/horario-grupo/') ?>${grupoId}`);
                const data = await res.json();
                if (data.ok && Array.isArray(data.asignaciones)) {
                    data.asignaciones.forEach(asig => {
                        const dias = asig.dias || [];
                        const [inicio, fin] = asig.rango;
                        const filas = Array.from(tbodyHorario.children);
                        filas.forEach((fila, i) => {
                            const hora = parseInt(fila.firstChild.textContent.replace(":", ""));
                            if (hora >= inicio && hora < fin) {
                                dias.forEach(d => {
                                    const col = ["L", "M", "X", "J", "V"].indexOf(d) + 1;
                                    if (col >= 1 && fila.children[col]) {
                                        fila.children[col].textContent = asig.materia;
                                        fila.children[col].classList.add("ocupado");
                                        fila.children[col].classList.remove("disponible");
                                    }
                                });
                            }
                        });
                    });
                }
            } catch (err) {
                console.error(err);
            }
        });
    </script>
</body>

</html>