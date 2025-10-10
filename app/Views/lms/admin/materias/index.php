<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Materias</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/materias.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <main class="content-dark">
        <div class="crud-container">
            <h2>Gestión de Materias</h2>
            <br>
            <button type="button" id="btnNuevaMateria" class="btn-nuevo" style="margin-bottom: 1rem;">
                <i class="fa fa-plus"></i> Nueva Materia
            </button>


            <!-- ✅ SweetAlert2 dinámico -->
            <?php if (session()->getFlashdata('msg')): ?>
                <?php
                $msg = session()->getFlashdata('msg');
                $icon = str_contains($msg, 'elimin') ? 'error' : (str_contains($msg, 'actualiz') ? 'success' : 'info');
                ?>
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        Swal.fire({
                            icon: '<?= $icon ?>',
                            title: 'Operación completada',
                            html: `<i class="fa fa-<?= $icon === 'error' ? 'trash' : ($icon === 'success' ? 'pen' : 'book') ?>"></i> <?= $msg ?>`,
                            background: '#1e1f25',
                            color: '#f9f9fb',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                            heightAuto: false,
                            didOpen: () => {
                                Swal.showLoading();
                                Swal.getContainer().style.pointerEvents = 'auto';
                            },
                            willClose: () => {
                                document.body.classList.remove('swal2-shown');
                                document.querySelectorAll('.swal2-container').forEach(el => el.remove());
                            }
                        });
                        setTimeout(() => { if (Swal.isVisible()) Swal.close(); }, 2200);
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
                <tbody>
                    <?php foreach ($materias as $m): ?>
                        <tr>
                            <td><?= esc($m['id']) ?></td>
                            <td><?= esc($m['clave']) ?></td>
                            <td><?= esc($m['nombre']) ?></td>
                            <td><?= esc($m['creditos']) ?> créditos</td>
                            <td><?= esc($m['horas_semana']) ?> h</td>
                            <td><?= $m['activo'] ? '✅' : '❌' ?></td>
                            <td>
                                <a href="#" class="btn-action btn-edit" data-id="<?= $m['id'] ?>"
                                    data-clave="<?= esc($m['clave']) ?>" data-nombre="<?= esc($m['nombre']) ?>"
                                    data-creditos="<?= esc($m['creditos']) ?>" data-horas="<?= esc($m['horas_semana']) ?>"
                                    data-activo="<?= $m['activo'] ?>">
                                    <i class="fa fa-edit"></i>
                                </a>

                                <a href="<?= base_url('admin/materias/eliminar/' . $m['id']) ?>"
                                    class="btn-action btn-delete" onclick="return confirm('¿Eliminar esta materia?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- MODAL NUEVA/EDITAR MATERIA -->
    <div id="modalMateria" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3 id="modalTitle" style="margin-bottom: 1rem; color: var(--primary);">Nueva Materia</h3>


            <form id="formMateria" class="form-modal" method="POST" action="<?= base_url('admin/materias/crear') ?>">
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
            const modal = document.getElementById("modalMateria");
            const closeBtn = modal.querySelector(".close-btn");
            const btnNueva = document.getElementById("btnNuevaMateria");
            const form = document.getElementById("formMateria");
            const modalTitle = document.getElementById("modalTitle");

            btnNueva.addEventListener("click", () => {
                form.action = "<?= base_url('admin/materias/crear') ?>";
                form.reset();
                modalTitle.textContent = "Nueva Materia";
                modal.style.display = "flex";
            });

            closeBtn.addEventListener("click", () => modal.style.display = "none");

            document.querySelectorAll(".btn-edit").forEach(btn => {
                btn.addEventListener("click", () => {
                    const id = btn.dataset.id;
                    form.action = `<?= base_url('admin/materias/actualizar') ?>/${id}`;
                    document.getElementById("idMateria").value = id;
                    document.getElementById("clave").value = btn.dataset.clave;
                    document.getElementById("nombre").value = btn.dataset.nombre;
                    document.getElementById("creditos").value = btn.dataset.creditos;
                    document.getElementById("horas_semana").value = btn.dataset.horas;
                    document.getElementById("activo").value = btn.dataset.activo;
                    modalTitle.textContent = "Editar Materia";
                    modal.style.display = "flex";
                });
            });

            window.addEventListener("click", e => {
                if (e.target === modal) modal.style.display = "none";
            });
        });

        // 🧩 Validación de clave única (AJAX)
        const inputClave = document.getElementById("clave");
        let timerClave;

        inputClave.addEventListener("input", () => {
            clearTimeout(timerClave);
            const clave = inputClave.value.trim();
            const idActual = document.getElementById("idMateria").value;

            if (clave.length < 2) return;

            timerClave = setTimeout(async () => {
                try {
                    const response = await fetch(`<?= base_url('admin/materias/verificar-clave') ?>?clave=${encodeURIComponent(clave)}&id=${idActual}`);
                    const data = await response.json();

                    if (data.existe) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Clave duplicada',
                            text: `Ya existe una materia registrada con la clave "${clave}".`,
                            background: '#1e1f25',
                            color: '#f9f9fb',
                            confirmButtonColor: '#ff9e64',
                            confirmButtonText: 'Entendido'
                        });
                        inputClave.classList.add('input-error');
                    } else {
                        inputClave.classList.remove('input-error');
                    }
                } catch (error) {
                    console.error('Error al verificar clave:', error);
                }
            }, 400); // pequeña espera para no saturar el servidor
        });

    </script>

    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
</body>

</html>