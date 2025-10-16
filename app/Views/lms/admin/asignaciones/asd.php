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
                <div id="horarioGrid" class="horario-grid-canvas hidden">
                    <h4 style="margin: 1rem 0; color: var(--primary);">
                        <i class="fa-regular fa-calendar-days"></i> Horario actual del grupo
                    </h4>

                    <div class="horario-wrapper">
                        <div class="grid-header">
                            <div>Hora</div>
                            <div>Lunes</div>
                            <div>Martes</div>
                            <div>Miércoles</div>
                            <div>Jueves</div>
                            <div>Viernes</div>
                        </div>
                        <div class="grid-canvas" id="gridCanvas"></div>
                    </div>
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
                                    <option value="<?= $g['id'] ?>"><?= esc($g['grupo']) ?></option>
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
        const formAsignacion = document.getElementById("formAsignacion");
        const gridCanvas = document.getElementById("gridCanvas");
        const horarioContainer = document.getElementById("horarioGrid");
        const HORARIOS = {
            "Matutino": ["07:30", "08:20", "09:10", "10:00", "10:50", "11:40", "12:30", "13:20", "14:10", "15:00"],
            "Vespertino": ["16:40", "17:20", "18:00", "18:40", "19:20", "20:00", "20:40", "21:20", "22:00"]
        };

        // 🧱 Render de horas al costado
        function renderGrid(turno = "Matutino") {
            gridCanvas.innerHTML = "";
            const bloques = HORARIOS[turno];
            bloques.forEach((h, i) => {
                const hora = document.createElement("div");
                hora.className = "celda-hora";
                hora.style.gridColumn = "1 / 2";
                hora.style.gridRow = (i + 1);
                hora.textContent = h;
                gridCanvas.appendChild(hora);
            });
        }

        // 🔄 Cargar materias y renderizar horario
        grupoSelect.addEventListener("change", async () => {
            const grupoId = grupoSelect.value;
            if (!grupoId) { horarioContainer.classList.add("hidden"); return; }
            const turno = grupoSelect.selectedOptions[0].dataset.turno || "Matutino";
            renderGrid(turno);
            horarioContainer.classList.remove("hidden");
            await cargarHorarioActual();
        });

        // 📦 Cargar asignaciones
        async function cargarHorarioActual() {
            const grupoId = grupoSelect.value;
            if (!grupoId) return;
            const res = await fetch(`<?= base_url('admin/asignaciones/horario-grupo/') ?>${grupoId}`);
            const data = await res.json();
            if (!data.ok) return;
            const turno = grupoSelect.selectedOptions[0].dataset.turno || "Matutino";
            renderGrid(turno);
            const anchoCol = (gridCanvas.offsetWidth - 70) / 5;
            data.asignaciones.forEach(a => {
                const [inicio, fin] = a.rango;
                const alto = (fin - inicio) / 60 * 60;
                const top = ((inicio - (parseInt(HORARIOS[turno][0].replace(":", "")))) / 100 * 60) || 0;
                a.dias.forEach(d => {
                    const col = ["L", "M", "X", "J", "V"].indexOf(d);
                    if (col >= 0) {
                        const bloque = document.createElement("div");
                        bloque.className = "bloque";
                        bloque.textContent = a.materia;
                        bloque.style.left = (70 + col * anchoCol) + "px";
                        bloque.style.top = top + "px";
                        bloque.style.width = (anchoCol - 8) + "px";
                        bloque.style.height = alto + "px";
                        bloque.dataset.dia = d;
                        gridCanvas.appendChild(bloque);
                    }
                });
            });
            initInteract(anchoCol);
        }

        // 🧲 Interact con snap
        function initInteract(anchoCol) {
            interact(".bloque")
                .draggable({
                    modifiers: [
                        interact.modifiers.snap({
                            targets: [interact.snappers.grid({ x: anchoCol, y: 60 })],
                            range: Infinity,
                            relativePoints: [{ x: 0, y: 0 }]
                        }),
                        interact.modifiers.restrictRect({ restriction: gridCanvas, endOnly: true })
                    ],
                    listeners: {
                        move(event) {
                            const x = (parseFloat(event.target.getAttribute("data-x")) || 0) + event.dx;
                            const y = (parseFloat(event.target.getAttribute("data-y")) || 0) + event.dy;
                            event.target.style.transform = `translate(${x}px,${y}px)`;
                            event.target.setAttribute("data-x", x);
                            event.target.setAttribute("data-y", y);
                        },
                        end() {
                            mostrarAlerta("📦 Movido a nueva celda (aún sin guardar)", "info");
                        }
                    }
                })
                .resizable({
                    edges: { top: true, bottom: true },
                    modifiers: [
                        interact.modifiers.snapSize({
                            targets: [interact.snappers.grid({ x: anchoCol, y: 60 })],
                            range: Infinity
                        }),
                        interact.modifiers.restrictEdges({ outer: gridCanvas })
                    ],
                    listeners: {
                        move(event) {
                            let y = parseFloat(event.target.getAttribute("data-y")) || 0;
                            event.target.style.height = event.rect.height + "px";
                            y += event.deltaRect.top;
                            event.target.style.transform = `translateY(${y}px)`;
                            event.target.setAttribute("data-y", y);
                        },
                        end() {
                            mostrarAlerta("↕️ Duración ajustada a bloque completo", "info");
                        }
                    }
                });
        }

        // ⚙️ Menú contextual
        gridCanvas.addEventListener("contextmenu", e => {
            e.preventDefault();
            const bloque = e.target.closest(".bloque");
            if (!bloque) return;
            const menu = document.createElement("div");
            menu.className = "menu-contextual";
            menu.innerHTML = `
    <button class="btn-mini editar"><i class="fa fa-pen"></i> Editar</button>
    <button class="btn-mini eliminar"><i class="fa fa-trash"></i> Eliminar</button>`;
            document.body.appendChild(menu);
            menu.style.left = e.pageX + "px";
            menu.style.top = e.pageY + "px";
            const closeMenu = () => menu.remove();
            document.addEventListener("click", closeMenu, { once: true });
            menu.querySelector(".editar").onclick = () => mostrarAlerta("✏️ Edición pendiente", "info");
            menu.querySelector(".eliminar").onclick = () => {
                bloque.remove();
                mostrarAlerta("🗑️ Clase eliminada.", "success");
            };
        });
    </script>
</body>

</html>