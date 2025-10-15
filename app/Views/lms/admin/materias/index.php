<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Materias</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/materias.css') ?>">
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
            <h2>Gestión de Materias</h2>
            <br>

            <button type="button" id="btnNuevaMateria" class="btn-nuevo" style="margin-bottom:1rem;">
                <i class="fa fa-plus"></i> Nueva Materia
            </button>

            <!-- ✅ Flash (usa alertas nativas) -->
            <?php if (session()->getFlashdata('msg')): ?>
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        mostrarAlerta("<?= esc(session()->getFlashdata('msg')) ?>", "success");
                    });
                </script>
            <?php endif; ?>

            <!-- 📘 Tabla de Materias -->
            <table class="tabla-crud">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Clave</th>
                        <th>Nombre</th>
                        <th>Créditos</th>
                        <th>Horas/Semana</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyMaterias">
                    <?php foreach ($materias as $m): ?>
                        <tr data-id="<?= $m['id'] ?>">
                            <td><?= esc($m['id']) ?></td>
                            <td><?= esc($m['clave']) ?></td>
                            <td><?= esc($m['nombre']) ?></td>
                            <td><?= esc($m['creditos']) ?> créditos</td>
                            <td><?= esc($m['horas_semana']) ?> h</td>
                            <td><?= $m['activo'] ? '✅' : '❌' ?></td>
                            <td>
                                <button class="btn-action btn-edit" data-id="<?= $m['id'] ?>"
                                    data-clave="<?= esc($m['clave']) ?>" data-nombre="<?= esc($m['nombre']) ?>"
                                    data-creditos="<?= esc($m['creditos']) ?>" data-horas="<?= esc($m['horas_semana']) ?>"
                                    data-activo="<?= $m['activo'] ?>">
                                    <i class="fa fa-edit"></i>
                                </button>

                                <button class="btn-action btn-delete"
                                    data-url="<?= base_url('admin/materias/eliminar/' . $m['id']) ?>">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- 🪶 MODAL NUEVA/EDITAR MATERIA -->
    <div id="modalMateria" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3 id="modalTitle" style="margin-bottom:1rem;color:var(--primary);">Nueva Materia</h3>

            <!-- Solo AJAX: sin action/method para evitar doble envío -->
            <form id="formMateria" class="form-modal" onsubmit="return false;">
                <input type="hidden" name="id" id="idMateria">

                <div class="modal-body">
                    <div class="form-group">
                        <label>Clave:</label>
                        <input type="text" name="clave" id="clave" required maxlength="20">
                    </div>

                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="text" name="nombre" id="nombre" required maxlength="150">
                    </div>

                    <div class="form-group">
                        <label>Créditos:</label>
                        <input type="number" name="creditos" id="creditos" min="0" max="20">
                    </div>

                    <div class="form-group">
                        <label>Horas por semana:</label>
                        <input type="number" name="horas_semana" id="horas_semana" min="0" max="40">
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

    <!-- JS -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const BASE_URL = "<?= base_url() ?>";
            const modal = document.getElementById("modalMateria");
            const closeBtn = modal.querySelector(".close-btn");
            const btnNueva = document.getElementById("btnNuevaMateria");
            const form = document.getElementById("formMateria");
            const modalTitle = document.getElementById("modalTitle");
            const tbody = document.getElementById("tbodyMaterias");

            let currentRow = null;
            let isSubmitting = false;

            // Helpers
            const cerrarModal = () => modal.style.display = "none";
            const abrirModal = () => { modal.style.display = "flex"; isSubmitting = false; };

            function bindRowActions(tr) {
                const editBtn = tr.querySelector(".btn-edit");
                const delBtn = tr.querySelector(".btn-delete");

                // Editar
                editBtn.addEventListener("click", () => {
                    form.reset();
                    const id = editBtn.dataset.id;
                    form.setAttribute("data-action", `${BASE_URL}/admin/materias/actualizar/${id}`);
                    document.getElementById("idMateria").value = id;
                    document.getElementById("clave").value = editBtn.dataset.clave;
                    document.getElementById("nombre").value = editBtn.dataset.nombre;
                    document.getElementById("creditos").value = editBtn.dataset.creditos;
                    document.getElementById("horas_semana").value = editBtn.dataset.horas;
                    document.getElementById("activo").value = editBtn.dataset.activo;
                    modalTitle.textContent = "Editar Materia";
                    currentRow = tr;
                    abrirModal();
                });

                // Eliminar
                delBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    const url = delBtn.dataset.url;
                    mostrarConfirmacion("Eliminar materia", "¿Deseas eliminar esta materia?", async () => {
                        try {
                            const res = await fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } });
                            const data = await res.json();
                            if (data.ok) {
                                tr.remove();
                                mostrarAlerta(data.msg, "success");
                            } else {
                                mostrarAlerta(data.msg || "No se pudo eliminar", "error");
                            }
                        } catch {
                            mostrarAlerta("Error de red o servidor", "error");
                        }
                    });
                });
            }

            // Bind inicial a filas existentes
            document.querySelectorAll("#tbodyMaterias tr").forEach(bindRowActions);

            // Nueva materia
            btnNueva.addEventListener("click", () => {
                form.reset();
                form.setAttribute("data-action", `${BASE_URL}/admin/materias/crear`);
                modalTitle.textContent = "Nueva Materia";
                currentRow = null;
                abrirModal();
            });

            // Cerrar modal
            closeBtn.addEventListener("click", cerrarModal);
            window.addEventListener("click", (e) => { if (e.target === modal) cerrarModal(); });

            // Guardar (crear/editar) con fetch
            form.addEventListener("submit", async (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (isSubmitting) return;
                isSubmitting = true;

                const url = form.getAttribute("data-action");
                const isUpdate = url.includes("/actualizar/");
                const fd = new FormData(form);

                try {
                    const res = await fetch(url, {
                        method: "POST",
                        body: fd,
                        headers: { "X-Requested-With": "XMLHttpRequest" }
                    });
                    const data = await res.json();

                    if (!data.ok) {
                        mostrarAlerta(data.msg || "Error al guardar la materia", "error");
                        isSubmitting = false;
                        return;
                    }

                    cerrarModal();
                    mostrarAlerta(data.msg, "success");

                    // Actualización dinámica
                    const valores = {
                        id: data.id || fd.get("id"),
                        clave: fd.get("clave"),
                        nombre: fd.get("nombre"),
                        creditos: fd.get("creditos"),
                        horas: fd.get("horas_semana"),
                        activo: fd.get("activo") === "1"
                    };

                    if (isUpdate && currentRow) {
                        const cells = currentRow.querySelectorAll("td");
                        cells[1].textContent = valores.clave;
                        cells[2].textContent = valores.nombre;
                        cells[3].textContent = `${valores.creditos} créditos`;
                        cells[4].textContent = `${valores.horas} h`;
                        cells[5].textContent = valores.activo ? "✅" : "❌";

                        // refrescar data-* del botón editar
                        const editBtn = currentRow.querySelector(".btn-edit");
                        editBtn.dataset.clave = valores.clave;
                        editBtn.dataset.nombre = valores.nombre;
                        editBtn.dataset.creditos = valores.creditos;
                        editBtn.dataset.horas = valores.horas;
                        editBtn.dataset.activo = valores.activo ? "1" : "0";

                        currentRow = null;
                        isSubmitting = false;
                    } else {
                        // Crear fila nueva sin recargar
                        const newId = data.id; // el controlador devuelve 'id'
                        const tr = document.createElement("tr");
                        tr.setAttribute("data-id", newId);
                        tr.innerHTML = `
            <td>${newId}</td>
            <td>${valores.clave}</td>
            <td>${valores.nombre}</td>
            <td>${valores.creditos} créditos</td>
            <td>${valores.horas} h</td>
            <td>${valores.activo ? "✅" : "❌"}</td>
            <td>
              <button class="btn-action btn-edit"
                      data-id="${newId}"
                      data-clave="${valores.clave}"
                      data-nombre="${valores.nombre}"
                      data-creditos="${valores.creditos}"
                      data-horas="${valores.horas}"
                      data-activo="${valores.activo ? "1" : "0"}">
                <i class="fa fa-edit"></i>
              </button>
              <button class="btn-action btn-delete"
                      data-url="${BASE_URL}/admin/materias/eliminar/${newId}">
                <i class="fa fa-trash"></i>
              </button>
            </td>
          `;
                        tbody.prepend(tr);
                        bindRowActions(tr); // engancha eventos a la nueva fila
                        isSubmitting = false;
                    }
                } catch (err) {
                    console.error(err);
                    mostrarAlerta("Error de red o servidor", "error");
                    isSubmitting = false;
                }
            });

            // 🔎 Validación de clave única (usa alerta nativa)
            const inputClave = document.getElementById("clave");
            let timerClave;
            inputClave.addEventListener("input", () => {
                clearTimeout(timerClave);
                const clave = inputClave.value.trim();
                const idActual = document.getElementById("idMateria").value;
                if (clave.length < 2) return;

                timerClave = setTimeout(async () => {
                    try {
                        const resp = await fetch(`${BASE_URL}/admin/materias/verificar-clave?clave=${encodeURIComponent(clave)}&id=${idActual}`, {
                            headers: { "X-Requested-With": "XMLHttpRequest" }
                        });
                        const data = await resp.json();
                        if (data.existe) {
                            inputClave.classList.add("input-error");
                            mostrarAlerta(`Ya existe una materia con la clave "${clave}".`, "warning");
                        } else {
                            inputClave.classList.remove("input-error");
                        }
                    } catch (e) {
                        console.error(e);
                    }
                }, 350);
            });
        });
    </script>

    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script src="<?= base_url('assets/js/alert.js') ?>"></script>
</body>

</html>