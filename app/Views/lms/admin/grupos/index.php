<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Grupos</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/grupos.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <!-- 🔔 Sistema de alertas -->
    <div id="alertContainer" class="alert-container"></div>

    <main class="content-dark">
        <div class="crud-container">
            <h2>Gestión de Grupos</h2>
            <br>

            <button type="button" id="btnNuevoGrupo" class="btn-nuevo">
                <i class="fa fa-plus"></i> Nuevo Grupo
            </button>

            <!-- ✅ Mensaje Flash -->
            <?php if (session()->getFlashdata('msg')): ?>
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        mostrarAlerta("<?= session()->getFlashdata('msg') ?>", "success");
                    });
                </script>
            <?php endif; ?>

            <table class="tabla-crud">
                <thead>
                    <tr>
                        <th>Carrera</th>
                        <th>Grupo</th>
                        <th>Periodo</th>
                        <th>Turno</th>
                        <th>Tutor</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyGrupos">
                    <?php if (!empty($grupos)): ?>
                        <?php foreach ($grupos as $g): ?>
                            <tr data-id="<?= $g['id'] ?>">
                                <td><?= esc($g['siglas']) ?></td>
                                <td><strong><?= esc($g['grupo']) ?></strong></td>
                                <td><?= esc($g['periodo']) ?></td>
                                <td><?= esc($g['turno']) ?></td>
                                <td><?= esc($g['tutor'] ?? '—') ?></td>
                                <td><?= $g['activo'] ? '✅' : '❌' ?></td>
                                <td class="acciones">
                                    <button class="btn-action btn-edit" data-id="<?= $g['id'] ?>"
                                        data-carrera="<?= $g['carrera_id'] ?>" data-periodo="<?= $g['periodo'] ?>"
                                        data-turno="<?= $g['turno'] ?>" data-tutor="<?= $g['tutor_id'] ?? '' ?>">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-delete" data-id="<?= $g['id'] ?>"
                                        data-url="<?= base_url('admin/grupos/eliminar/' . $g['id']) ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">No hay grupos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- 🧱 MODAL NUEVO GRUPO -->
    <div id="modalGrupo" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3 id="modalTitle">Nuevo Grupo</h3>

            <form id="formGrupo" class="form-modal" data-ajax="false" onsubmit="return false;">

                <div class="form-group">
                    <label>Carrera:</label>
                    <select name="carrera_id" id="carrera_id" required>
                        <option value="">-- Selecciona --</option>
                        <?php foreach ($carreras as $c): ?>
                            <option value="<?= $c['id'] ?>" data-duracion="<?= $c['duracion'] ?>">
                                <?= esc($c['siglas']) ?> - <?= esc($c['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ciclo o Periodo:</label>
                    <select name="periodo" id="periodo" required>
                        <option value="">-- Selecciona un ciclo --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Turno:</label>
                    <select name="turno" id="turno" required>
                        <option value="Matutino">Matutino</option>
                        <option value="Vespertino">Vespertino</option>
                        <option value="Mixto">Mixto</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tutor (opcional):</label>
                    <select name="tutor_id" id="tutor_id">
                        <option value="">-- Sin asignar --</option>
                        <?php foreach ($tutores as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= esc($t['nombre']) ?></option>
                        <?php endforeach; ?>
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
        document.addEventListener("DOMContentLoaded", () => {
            const modal = document.getElementById("modalGrupo");
            const closeBtn = modal.querySelector(".close-btn");
            const btnNuevo = document.getElementById("btnNuevoGrupo");
            const form = document.getElementById("formGrupo");
            const tbody = document.getElementById("tbodyGrupos");
            const carreraSelect = document.getElementById("carrera_id");
            const periodoSelect = document.getElementById("periodo");
            const modalTitle = document.getElementById("modalTitle");

            let isSubmitting = false;
            let editId = null;
            let currentRow = null;

            const cerrarModal = () => modal.style.display = "none";

            // Abrir modal nuevo
            btnNuevo.addEventListener("click", () => {
                form.reset();
                modalTitle.textContent = "Nuevo Grupo";
                editId = null;
                currentRow = null;
                modal.style.display = "flex";
                isSubmitting = false;
            });

            // Cerrar modal
            closeBtn.addEventListener("click", cerrarModal);
            window.addEventListener("click", e => { if (e.target === modal) cerrarModal(); });

            // Generar ciclos por carrera
            carreraSelect.addEventListener("change", () => {
                const duracion = carreraSelect.selectedOptions[0]?.dataset.duracion || 0;
                periodoSelect.innerHTML = `<option value="">-- Selecciona un ciclo --</option>`;
                for (let i = 1; i <= duracion; i++) {
                    periodoSelect.innerHTML += `<option value="${i}">${i}</option>`;
                }
            });

            // Guardar / Actualizar grupo
            form.addEventListener("submit", async (e) => {
                e.preventDefault();
                if (isSubmitting) return;
                isSubmitting = true;

                const fd = new FormData(form);
                const url = editId
                    ? `<?= base_url('admin/grupos/actualizar/') ?>${editId}`
                    : `<?= base_url('admin/grupos/crear') ?>`;

                try {
                    const res = await fetch(url, {
                        method: "POST",
                        body: fd,
                        headers: { "X-Requested-With": "XMLHttpRequest" },
                    });

                    const data = await res.json();

                    if (!data.ok) {
                        mostrarAlerta(data.msg || "Error al guardar grupo", "error");
                        isSubmitting = false;
                        return;
                    }

                    const g = data.grupo;
                    mostrarAlerta(data.msg, "success");
                    cerrarModal();

                    if (editId && currentRow) {
                        // 🔄 Actualizar fila existente
                        const celdas = currentRow.querySelectorAll("td");
                        celdas[0].textContent = g.siglas;
                        celdas[1].innerHTML = `<strong>${g.grupo}</strong>`;
                        celdas[2].textContent = g.periodo;
                        celdas[3].textContent = g.turno;
                        celdas[4].textContent = g.tutor ?? "—";

                        // 🔁 Actualizar atributos del botón editar
                        const editBtn = currentRow.querySelector(".btn-edit");
                        if (editBtn) {
                            editBtn.dataset.carrera = fd.get("carrera_id");
                            editBtn.dataset.periodo = g.periodo;
                            editBtn.dataset.turno = g.turno;
                            editBtn.dataset.tutor = fd.get("tutor_id") || "";
                        }
                    }
                    else {
                        // ➕ Crear nueva fila
                        const row = document.createElement("tr");
                        row.innerHTML = `
          <td>${g.siglas}</td>
          <td><strong>${g.grupo}</strong></td>
          <td>${g.periodo}</td>
          <td>${g.turno}</td>
          <td>${g.tutor ?? "—"}</td>
          <td>${g.activo ? "✅" : "❌"}</td>
          <td class="acciones">
            <button class="btn-action btn-edit"
                data-id="${g.id}"
                data-carrera="${fd.get("carrera_id")}"
                data-periodo="${g.periodo}"
                data-turno="${g.turno}"
                data-tutor="${fd.get("tutor_id")}">
              <i class="fa fa-edit"></i>
            </button>
            <button class="btn-action btn-delete"
                data-id="${g.id}"
                data-url="<?= base_url('admin/grupos/eliminar') ?>/${g.id}">
              <i class="fa fa-trash"></i>
            </button>
          </td>`;
                        tbody.prepend(row);
                    }

                    editId = null;
                    currentRow = null;
                    isSubmitting = false;

                } catch (err) {
                    console.error(err);
                    mostrarAlerta("Error de red o servidor", "error");
                    isSubmitting = false;
                }
            });

            // 🖊️ Editar grupo
            tbody.addEventListener("click", (e) => {
                const btn = e.target.closest(".btn-edit");
                if (!btn) return;

                editId = btn.dataset.id;
                currentRow = btn.closest("tr");
                modalTitle.textContent = "Editar Grupo";

                // Pre-cargar valores
                document.getElementById("carrera_id").value = btn.dataset.carrera;
                carreraSelect.dispatchEvent(new Event("change"));
                setTimeout(() => {
                    document.getElementById("periodo").value = btn.dataset.periodo;
                }, 100);
                document.getElementById("turno").value = btn.dataset.turno;
                document.getElementById("tutor_id").value = btn.dataset.tutor || "";

                modal.style.display = "flex";
                isSubmitting = false;
            });

            // 🗑️ Eliminar grupo
            tbody.addEventListener("click", (e) => {
                const btn = e.target.closest(".btn-delete");
                if (!btn) return;
                const url = btn.dataset.url;

                mostrarConfirmacion("Eliminar grupo", "¿Deseas eliminar este grupo?", async () => {
                    await fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } });
                    btn.closest("tr").remove();
                    mostrarAlerta("Grupo eliminado correctamente", "success");
                });
            });
        });

    </script>
</body>

</html>