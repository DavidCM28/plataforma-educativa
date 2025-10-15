<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Planes de Estudio</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/planes_estudio.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <!-- 🔔 Sistema de alertas -->
    <div id="alertContainer" class="alert-container"></div>

    <!-- ⚠️ Modal de confirmación -->
    <div id="confirmModal" class="confirm-modal hidden">
        <div class="confirm-box">
            <h3 id="confirmTitle">Confirmar acción</h3>
            <p id="confirmMessage">¿Estás seguro de continuar?</p>
            <div class="confirm-buttons">
                <button id="btnCancelar">Cancelar</button>
                <button id="btnAceptar">Aceptar</button>
            </div>
        </div>
    </div>

    <main class="content-dark">
        <div class="crud-container">
            <h2>Gestión de Planes de Estudio</h2>

            <div class="tabs">
                <button class="tab-btn active" data-tab="planes">Planes de Estudio</button>
                <button class="tab-btn" data-tab="materias">Asignar Materias</button>
            </div>

            <!-- ✅ Mensaje flash -->
            <?php if (session()->getFlashdata('msg')): ?>
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        mostrarAlerta("<?= session()->getFlashdata('msg') ?>", "success");
                    });
                </script>
            <?php endif; ?>

            <!-- 🧱 SECCIÓN PLANES -->
            <section id="planes" class="tab-content active">
                <button type="button" id="btnNuevoPlan" class="btn-nuevo">
                    <i class="fa fa-plus"></i> Nuevo Plan
                </button>

                <table class="tabla-crud">
                    <thead>
                        <tr>
                            <th>Carrera</th>
                            <th>Nombre del Plan</th>
                            <th>Vigencia</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyPlanes">
                        <?php foreach ($planes as $p): ?>
                            <tr data-id="<?= $p['plan_id'] ?>">
                                <td><?= esc($p['carrera']) ?></td>
                                <td><?= esc($p['nombre']) ?></td>
                                <td><?= esc($p['fecha_vigencia']) ?: '—' ?></td>
                                <td><?= $p['activo'] ? '✅' : '❌' ?></td>
                                <td class="acciones">
                                    <button class="btn-action btn-edit" data-id="<?= $p['plan_id'] ?>"
                                        data-carrera="<?= $p['carrera_id'] ?>" data-nombre="<?= esc($p['nombre']) ?>"
                                        data-vigencia="<?= esc($p['fecha_vigencia']) ?>" data-activo="<?= $p['activo'] ?>">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-delete"
                                        data-url="<?= base_url('admin/planes-estudio/eliminar/' . $p['plan_id']) ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            </section>

            <!-- 📚 SECCIÓN ASIGNAR MATERIAS -->
            <section id="materias" class="tab-content">
                <form id="formAsignar" class="form-asignacion"
                    action="<?= base_url('admin/planes-estudio/agregarMateria') ?>" novalidate>
                    <div class="form-group">
                        <label>Selecciona un Plan:</label>
                        <select name="plan_id" id="planSelect" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($planes as $p): ?>
                                <option value="<?= $p['plan_id'] ?>" data-carrera="<?= $p['carrera_id'] ?>">
                                    <?= esc($p['nombre']) ?> (<?= esc($p['carrera']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Materia:</label>
                        <select name="materia_id" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($materias as $m): ?>
                                <option value="<?= $m['id'] ?>">
                                    <?= esc($m['nombre']) ?> (<?= esc($m['clave']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ciclo:</label>
                        <select name="cuatrimestre" id="selectCuatrimestre" required>
                            <option value="">-- Selecciona --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tipo:</label>
                        <select name="tipo" required>
                            <option value="Tronco Común">Tronco Común</option>
                            <option value="Especialidad">Especialidad</option>
                            <option value="Optativa">Optativa</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-nuevo">
                        <i class="fa fa-plus"></i> Asignar Materia
                    </button>
                </form>

                <div id="materiasAsignadas" class="tabla-container" style="margin-top:2rem;display:none;">
                    <h3>Materias asignadas</h3>
                    <table class="tabla-crud">
                        <thead>
                            <tr>
                                <th>Clave</th>
                                <th>Materia</th>
                                <th>Cuatrimestre</th>
                                <th>Tipo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaMateriasBody">
                            <tr>
                                <td colspan="5" style="text-align:center;">Selecciona un plan para ver sus materias</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <!-- 🪶 MODAL PLAN -->
    <div id="modalPlan" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3 id="modalTitle">Nuevo Plan</h3>

            <!-- Solo AJAX -->
            <form id="formPlan" class="form-modal" data-ajax="false" novalidate>

                <input type="hidden" name="id" id="idPlan">

                <div class="form-group">
                    <label>Carrera:</label>
                    <select name="carrera_id" id="carrera_id" required>
                        <option value="">-- Selecciona --</option>
                        <?php foreach ($carreras as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= esc($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nombre del Plan:</label>
                    <input type="text" name="nombre" id="nombre" required maxlength="150">
                </div>

                <div class="form-group">
                    <label>Fecha de Vigencia:</label>
                    <input type="date" name="fecha_vigencia" id="fecha_vigencia">
                </div>

                <div class="form-group">
                    <label>Activo:</label>
                    <select name="activo" id="activo">
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-nuevo">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ⚙️ Scripts -->
    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script src="<?= base_url('assets/js/alert.js') ?>"></script>
    <script>
        const DURACIONES = <?= json_encode($duracionesPorCarrera) ?>;
        const BASE_URL = "<?= base_url() ?>";

        document.addEventListener("DOMContentLoaded", () => {
            // === Tabs ===
            document.querySelectorAll(".tab-btn").forEach(btn => {
                btn.addEventListener("click", () => {
                    document.querySelectorAll(".tab-btn, .tab-content").forEach(el => el.classList.remove("active"));
                    btn.classList.add("active");
                    document.getElementById(btn.dataset.tab).classList.add("active");
                });
            });

            // === Modal ===
            const modal = document.getElementById("modalPlan");
            const closeBtn = modal.querySelector(".close-btn");
            const btnNuevo = document.getElementById("btnNuevoPlan");
            const form = document.getElementById("formPlan");
            const modalTitle = document.getElementById("modalTitle");
            const tbody = document.getElementById("tbodyPlanes");
            let currentRow = null;
            let isSubmitting = false;

            const cerrarModal = () => modal.style.display = "none";

            btnNuevo.addEventListener("click", () => {
                form.reset();
                form.setAttribute("data-action", `${BASE_URL.replace(/\/$/, '')}/admin/planes-estudio/crear`);
                modalTitle.textContent = "Nuevo Plan de Estudio";
                modal.style.display = "flex";
                currentRow = null;
                isSubmitting = false;
            });

            closeBtn.addEventListener("click", cerrarModal);
            window.addEventListener("click", e => { if (e.target === modal) cerrarModal(); });

            document.querySelectorAll(".btn-edit").forEach(btn => {
                btn.addEventListener("click", () => {
                    form.reset();
                    const id = btn.dataset.id;
                    form.setAttribute("data-action", `${BASE_URL.replace(/\/$/, '')}/admin/planes-estudio/actualizar/${id}`);
                    document.getElementById("idPlan").value = id;
                    document.getElementById("carrera_id").value = btn.dataset.carrera;
                    document.getElementById("nombre").value = btn.dataset.nombre;
                    document.getElementById("fecha_vigencia").value = btn.dataset.vigencia;
                    document.getElementById("activo").value = btn.dataset.activo;
                    modalTitle.textContent = "Editar Plan de Estudio";
                    modal.style.display = "flex";
                    currentRow = btn.closest("tr");
                    isSubmitting = false;
                });
            });

            // Guardar con fetch (solo AJAX)
            form.addEventListener("submit", async (e) => {
                e.preventDefault();
                if (isSubmitting) return;
                isSubmitting = true;

                const url = form.getAttribute("data-action");
                const isUpdate = url.includes("/actualizar/");
                const fd = new FormData(form);

                try {
                    const res = await fetch(url, { method: "POST", body: fd, headers: { "X-Requested-With": "XMLHttpRequest" } });
                    const data = await res.json();

                    if (!data.ok) {
                        mostrarAlerta(data.msg || "Error al guardar el plan", "error");
                        isSubmitting = false;
                        return;
                    }

                    cerrarModal();
                    mostrarAlerta(data.msg, "success");

                    const carrera = form.querySelector("#carrera_id").selectedOptions[0].textContent;
                    const nombre = fd.get("nombre");
                    const vigencia = fd.get("fecha_vigencia") || "—";
                    const activo = fd.get("activo") === "1" ? "✅" : "❌";

                    if (isUpdate && currentRow) {
                        const celdas = currentRow.querySelectorAll("td");
                        celdas[0].textContent = carrera;
                        celdas[1].textContent = nombre;
                        celdas[2].textContent = vigencia;
                        celdas[3].textContent = activo;

                        const editBtn = currentRow.querySelector(".btn-edit");
                        editBtn.dataset.carrera = fd.get("carrera_id");
                        editBtn.dataset.nombre = nombre;
                        editBtn.dataset.vigencia = fd.get("fecha_vigencia");
                        editBtn.dataset.activo = fd.get("activo");
                        currentRow = null;
                        isSubmitting = false;
                    } else {
                        const id = data.id;
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
            <td>${carrera}</td>
            <td>${nombre}</td>
            <td>${vigencia}</td>
            <td>${activo}</td>
            <td class="acciones">
              <button class="btn-action btn-edit"
                      data-id="${id}"
                      data-carrera="${fd.get("carrera_id")}"
                      data-nombre="${nombre}"
                      data-vigencia="${fd.get("fecha_vigencia")}"
                      data-activo="${fd.get("activo")}">
                <i class="fa fa-edit"></i>
              </button>
              <button class="btn-action btn-delete"
                      data-url="${BASE_URL}/admin/planes-estudio/eliminar/${id}">
                <i class="fa fa-trash"></i>
              </button>
            </td>`;
                        tbody.prepend(tr);
                        isSubmitting = false;
                    }
                } catch (err) {
                    console.error(err);
                    mostrarAlerta("Error de red o servidor", "error");
                    isSubmitting = false;
                }
            });

            // Asignar Materias (igual que antes)
            const planSelect = document.getElementById("planSelect");
            const tablaMateriasBody = document.getElementById("tablaMateriasBody");
            const tablaContainer = document.getElementById("materiasAsignadas");
            const selectCuatrimestre = document.getElementById("selectCuatrimestre");
            const formAsignar = document.getElementById("formAsignar");

            planSelect.addEventListener("change", () => {
                const carreraId = planSelect.selectedOptions[0]?.dataset.carrera;
                selectCuatrimestre.innerHTML = '<option value="">-- Selecciona --</option>';
                if (carreraId && DURACIONES[carreraId]) {
                    for (let i = 1; i <= DURACIONES[carreraId]; i++) {
                        selectCuatrimestre.innerHTML += `<option value="${i}">${i}</option>`;
                    }
                }
            });

            async function cargarMaterias(planId) {
                try {
                    const res = await fetch(`${BASE_URL}/admin/planes-estudio/materias-por-plan/${planId}`);
                    const data = await res.json();
                    tablaMateriasBody.innerHTML = "";

                    if (data.length === 0) {
                        tablaMateriasBody.innerHTML = `<tr><td colspan="5" style="text-align:center;">Este plan no tiene materias asignadas.</td></tr>`;
                    } else {
                        data.forEach(m => {
                            const row = document.createElement("tr");
                            row.innerHTML = `
              <td>${m.clave}</td>
              <td>${m.nombre}</td>
              <td>${m.cuatrimestre}</td>
              <td>${m.tipo}</td>
              <td><button class="btn-action btn-delete" data-id="${m.id}"><i class="fa fa-trash"></i></button></td>`;
                            tablaMateriasBody.appendChild(row);
                        });
                        tablaMateriasBody.querySelectorAll(".btn-delete").forEach(btn => {
                            btn.addEventListener("click", () => {
                                mostrarConfirmacion("Eliminar materia", "¿Deseas eliminar esta materia del plan?", async () => {
                                    const id = btn.dataset.id;
                                    await fetch(`${BASE_URL}/admin/planes-estudio/eliminar-materia/${id}`);
                                    btn.closest("tr").remove();
                                    mostrarAlerta("Materia eliminada del plan", "success");
                                });
                            });
                        });
                    }
                    tablaContainer.style.display = "block";
                } catch (err) {
                    console.error(err);
                    mostrarAlerta("Error al cargar materias del plan", "error");
                }
            }

            planSelect.addEventListener("change", () => {
                const planId = planSelect.value;
                if (planId) cargarMaterias(planId);
                else tablaContainer.style.display = "none";
            });

            formAsignar.addEventListener("submit", async (e) => {
                e.preventDefault();
                if (isSubmitting) return;
                isSubmitting = true;
                const fd = new FormData(formAsignar);
                const planId = fd.get("plan_id");
                try {
                    const res = await fetch(formAsignar.action, { method: "POST", body: fd, headers: { "X-Requested-With": "XMLHttpRequest" } });
                    const data = await res.json();
                    if (data.ok) {
                        mostrarAlerta(data.msg, "success");
                        formAsignar.reset();
                        planSelect.value = planId;
                        planSelect.dispatchEvent(new Event("change"));
                    } else {
                        mostrarAlerta(data.msg || "No se pudo asignar la materia", "error");
                    }
                } catch (err) {
                    console.error(err);
                    mostrarAlerta("Error de red o servidor", "error");
                }
                isSubmitting = false;
            });

            // Eliminar plan
            document.querySelectorAll(".btn-delete").forEach(btn => {
                btn.addEventListener("click", () => {
                    const url = btn.dataset.url;
                    mostrarConfirmacion("Eliminar plan", "¿Deseas eliminar este plan de estudio?", async () => {
                        await fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } });
                        btn.closest("tr").remove();
                        mostrarAlerta("Plan eliminado correctamente", "success");
                    });
                });
            });
        });
    </script>
</body>

</html>