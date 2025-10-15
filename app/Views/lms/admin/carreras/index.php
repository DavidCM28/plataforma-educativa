<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Carreras</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/carreras.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <!-- 🔔 Contenedor global de alertas -->
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
            <h2>Gestión de Carreras</h2>
            <br>

            <button type="button" id="btnNuevaCarrera" class="btn-nuevo">
                <i class="fa fa-plus"></i> Nueva Carrera
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
                        <th>Nombre</th>
                        <th>Siglas</th>
                        <th>Duración</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carreras as $c): ?>
                        <tr>
                            <td><?= esc($c['nombre']) ?></td>
                            <td><?= esc($c['siglas']) ?></td>
                            <td><?= esc($c['duracion']) ?> ciclos</td>
                            <td><?= $c['activo'] ? '✅' : '❌' ?></td>
                            <td class="acciones">
                                <button class="btn-action btn-edit" data-id="<?= $c['id'] ?>"
                                    data-nombre="<?= esc($c['nombre']) ?>" data-siglas="<?= esc($c['siglas']) ?>"
                                    data-duracion="<?= esc($c['duracion']) ?>" data-activo="<?= $c['activo'] ?>">
                                    <i class="fa fa-edit"></i>
                                </button>

                                <button class="btn-action btn-delete"
                                    data-url="<?= base_url('admin/carreras/eliminar/' . $c['id']) ?>">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- 🪶 MODAL NUEVA / EDITAR CARRERA -->
    <div id="modalCarrera" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3 id="modalTitle">Nueva Carrera</h3>

            <form id="formCarrera" class="form-modal" onsubmit="return false;">

                <input type="hidden" name="id" id="idCarrera">

                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="text" name="nombre" id="nombre" required>
                    </div>

                    <div class="form-group">
                        <label>Siglas:</label>
                        <input type="text" name="siglas" id="siglas" required>
                    </div>

                    <div class="form-group">
                        <label>Duración (en ciclos):</label>
                        <input type="number" name="duracion" id="duracion" min="1">
                    </div>

                    <div class="form-group">
                        <label>Activo:</label>
                        <select name="activo" id="activo">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-nuevo">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ⚙️ Scripts -->
    <script>
        const BASE_URL = "<?= base_url() ?>";
        document.addEventListener("DOMContentLoaded", () => {
            const modal = document.getElementById("modalCarrera");
            const closeBtn = modal.querySelector(".close-btn");
            const btnNueva = document.getElementById("btnNuevaCarrera");
            const form = document.getElementById("formCarrera");
            const modalTitle = document.getElementById("modalTitle");

            let currentRow = null;
            let isSubmitting = false; // 🔒 evita doble envío

            // 🔸 Abrir modal para nueva carrera
            btnNueva.addEventListener("click", () => {
                form.reset();
                form.action = `${BASE_URL}/admin/carreras/crear`;
                document.getElementById("idCarrera").value = "";
                modalTitle.textContent = "Nueva Carrera";
                currentRow = null;
                isSubmitting = false;
                modal.style.display = "flex";
            });

            // 🔸 Abrir modal para editar
            document.querySelectorAll(".btn-edit").forEach(btn => {
                btn.addEventListener("click", () => {
                    form.reset();
                    const id = btn.dataset.id;
                    form.action = `${BASE_URL}/admin/carreras/actualizar/${id}`;
                    document.getElementById("idCarrera").value = id;
                    document.getElementById("nombre").value = btn.dataset.nombre;
                    document.getElementById("siglas").value = btn.dataset.siglas;
                    document.getElementById("duracion").value = btn.dataset.duracion;
                    document.getElementById("activo").value = btn.dataset.activo;

                    modalTitle.textContent = "Editar Carrera";
                    currentRow = btn.closest("tr");
                    isSubmitting = false;
                    modal.style.display = "flex";
                });
            });

            // 🔸 Cerrar modal
            const cerrarModal = () => { modal.style.display = "none"; };
            closeBtn.addEventListener("click", cerrarModal);
            window.addEventListener("click", e => { if (e.target === modal) cerrarModal(); });

            // 🔸 Interceptar submit (crear/editar)
            form.addEventListener("submit", async (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                // 🚫 Si ya se está enviando, salimos
                if (isSubmitting) return;
                isSubmitting = true;

                const actionUrl = form.getAttribute("action");
                const isUpdate = actionUrl.includes("/actualizar/");
                const fd = new FormData(form);

                try {
                    const res = await fetch(actionUrl, {
                        method: "POST",
                        body: fd,
                        headers: { "X-Requested-With": "XMLHttpRequest" }
                    });

                    if (!res.ok) {
                        mostrarAlerta("No se pudo guardar la carrera", "error");
                        isSubmitting = false;
                        return;
                    }

                    cerrarModal();
                    mostrarAlerta(isUpdate ? "Carrera actualizada" : "Carrera creada", "success");

                    if (isUpdate && currentRow) {
                        const nombre = document.getElementById("nombre").value.trim();
                        const siglas = document.getElementById("siglas").value.trim();
                        const duracion = document.getElementById("duracion").value.trim();
                        const activo = document.getElementById("activo").value;

                        const cells = currentRow.querySelectorAll("td");
                        cells[0].textContent = nombre;
                        cells[1].textContent = siglas;
                        cells[2].textContent = `${duracion} ciclos`;
                        cells[3].textContent = (activo === "1") ? "✅" : "❌";

                        const editBtn = currentRow.querySelector(".btn-edit");
                        if (editBtn) {
                            editBtn.dataset.nombre = nombre;
                            editBtn.dataset.siglas = siglas;
                            editBtn.dataset.duracion = duracion;
                            editBtn.dataset.activo = activo;
                        }

                        currentRow = null;
                        isSubmitting = false;
                    } else {
                        // 🔁 Para nuevas carreras, refrescar la tabla
                        setTimeout(() => location.reload(), 600);
                    }

                } catch (err) {
                    console.error(err);
                    mostrarAlerta("Error de red o servidor", "error");
                    isSubmitting = false;
                }
            });

            // 🔸 Confirmar eliminación
            document.querySelectorAll(".btn-delete").forEach(btn => {
                btn.addEventListener("click", () => {
                    const url = btn.dataset.url;
                    mostrarConfirmacion("Eliminar carrera", "¿Deseas eliminar esta carrera?", async () => {
                        try {
                            const res = await fetch(url);
                            if (res.ok) {
                                btn.closest("tr").remove();
                                mostrarAlerta("Carrera eliminada correctamente", "success");
                            } else {
                                mostrarAlerta("No se pudo eliminar la carrera", "error");
                            }
                        } catch (err) {
                            console.error(err);
                            mostrarAlerta("Error de red o servidor", "error");
                        }
                    });
                });
            });
        });
    </script>



    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script src="<?= base_url('assets/js/alert.js') ?>"></script>

</body>

</html>