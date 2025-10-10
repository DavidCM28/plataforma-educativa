<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Carreras</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/carreras.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <main class="content-dark">
        <div class="crud-container">
            <h2>Gestión de Carreras</h2>
            <br>

            <button type="button" id="btnNuevaCarrera" class="btn-nuevo">
                <i class="fa fa-plus"></i> Nueva Carrera
            </button>

            <?php if (session()->getFlashdata('msg')): ?>
                <?php
                $msg = session()->getFlashdata('msg');
                $icon = str_contains($msg, 'elimin') ? 'error' : (str_contains($msg, 'actualiz') ? 'success' : 'info');
                ?>
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        const swalInstance = Swal.fire({
                            icon: '<?= $icon ?>',
                            title: 'Operación completada',
                            html: `<i class="fa fa-<?= $icon === 'error' ? 'trash' : 'pen' ?>"></i> <?= $msg ?>`,
                            background: '#1e1f25',
                            color: '#f9f9fb',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                            heightAuto: false, // 🔧 evita salto del layout
                            didOpen: () => {
                                Swal.showLoading();
                                // 🔧 fuerza interacción sin bloquear
                                const container = Swal.getContainer();
                                container.style.pointerEvents = 'auto';
                            },
                            willClose: () => {
                                // 🔧 restablece el body y elimina manualmente el contenedor
                                document.body.classList.remove('swal2-shown');
                                document.querySelectorAll('.swal2-container').forEach(el => el.remove());
                            }
                        });

                        // 🔧 fallback: si no se cerró por timer, forzar cierre
                        setTimeout(() => {
                            if (Swal.isVisible()) Swal.close();
                        }, 2200);
                    });
                </script>
            <?php endif; ?>




            <table class="tabla-crud">
                <thead>
                    <tr>
                        <th>ID</th>
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
                            <td><?= esc($c['id']) ?></td>
                            <td><?= esc($c['nombre']) ?></td>
                            <td><?= esc($c['siglas']) ?></td>
                            <td><?= esc($c['duracion']) ?> ciclos</td>
                            <td><?= $c['activo'] ? '✅' : '❌' ?></td>
                            <td class="acciones">
                                <a href="#" class="btn-action btn-edit" data-id="<?= $c['id'] ?>"
                                    data-nombre="<?= esc($c['nombre']) ?>" data-siglas="<?= esc($c['siglas']) ?>"
                                    data-duracion="<?= esc($c['duracion']) ?>" data-activo="<?= $c['activo'] ?>">
                                    <i class="fa fa-edit"></i>
                                </a>

                                <a href="<?= base_url('admin/carreras/eliminar/' . $c['id']) ?>"
                                    class="btn-action btn-delete" onclick="return confirm('¿Eliminar esta carrera?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- MODAL NUEVA/EDITAR CARRERA -->
    <div id="modalCarrera" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3 id="modalTitle">Nueva Carrera</h3>

            <form id="formCarrera" class="form-modal" method="POST" action="<?= base_url('admin/carreras/crear') ?>">
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

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const modal = document.getElementById("modalCarrera");
            const closeBtn = modal.querySelector(".close-btn");
            const btnNueva = document.getElementById("btnNuevaCarrera");
            const form = document.getElementById("formCarrera");
            const modalTitle = document.getElementById("modalTitle");

            btnNueva.addEventListener("click", () => {
                form.action = "<?= base_url('admin/carreras/crear') ?>";
                form.reset();
                modalTitle.textContent = "Nueva Carrera";
                modal.style.display = "flex";
            });

            closeBtn.addEventListener("click", () => modal.style.display = "none");

            document.querySelectorAll(".btn-edit").forEach(btn => {
                btn.addEventListener("click", () => {
                    const id = btn.dataset.id;
                    form.action = `<?= base_url('admin/carreras/actualizar') ?>/${id}`;
                    document.getElementById("idCarrera").value = id;
                    document.getElementById("nombre").value = btn.dataset.nombre;
                    document.getElementById("siglas").value = btn.dataset.siglas;
                    document.getElementById("duracion").value = btn.dataset.duracion;
                    document.getElementById("activo").value = btn.dataset.activo;
                    modalTitle.textContent = "Editar Carrera";
                    modal.style.display = "flex";
                });
            });

            window.addEventListener("click", (e) => {
                if (e.target === modal) modal.style.display = "none";
            });
        });
    </script>

    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>

</body>

</html>