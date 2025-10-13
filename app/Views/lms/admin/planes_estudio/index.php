<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Planes de Estudio</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/planes_estudio.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <main class="content-dark">
        <div class="crud-container">
            <h2>Gestión de Planes de Estudio</h2>

            <!-- === TABS === -->
            <div class="tabs">
                <button class="tab-btn active" data-tab="planes">Planes de Estudio</button>
                <button class="tab-btn" data-tab="materias">Asignar Materias</button>
            </div>

            <!-- ✅ MENSAJE FLASH (con fix de cierre) -->
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
                            html: `<i class="fa fa-<?= $icon === 'error' ? 'trash' : 'book' ?>"></i> <?= $msg ?>`,
                            background: '#1e1f25',
                            color: '#f9f9fb',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                            heightAuto: false,
                            didOpen: () => {
                                Swal.showLoading();
                                const container = Swal.getContainer();
                                // Permitir clics en el resto de la página
                                container.style.pointerEvents = 'none';
                            },
                            willClose: () => {
                                // Limpieza segura del contenedor
                                document.body.classList.remove('swal2-shown');
                                document.querySelectorAll('.swal2-container').forEach(el => el.remove());
                            }
                        });

                        // Fallback: cierre forzado si el timer no limpia bien
                        setTimeout(() => {
                            if (Swal.isVisible()) Swal.close();
                            document.querySelectorAll('.swal2-container').forEach(el => el.remove());
                        }, 2500);
                    });
                </script>
            <?php endif; ?>


            <!-- ===============================
            🧱 SECCIÓN 1 - PLANES
      ================================ -->
            <section id="planes" class="tab-content active">
                <button type="button" id="btnNuevoPlan" class="btn-nuevo">
                    <i class="fa fa-plus"></i> Nuevo Plan
                </button>

                <table class="tabla-crud">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Carrera</th>
                            <th>Nombre del Plan</th>
                            <th>Vigencia</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($planes as $p): ?>
                            <tr>
                                <td><?= esc($p['id']) ?></td>
                                <td><?= esc($p['carrera']) ?></td>
                                <td><?= esc($p['nombre']) ?></td>
                                <td><?= esc($p['fecha_vigencia']) ?: '—' ?></td>
                                <td><?= $p['activo'] ? '✅' : '❌' ?></td>
                                <td class="acciones">
                                    <a href="#" class="btn-action btn-edit" data-id="<?= $p['id'] ?>"
                                        data-carrera="<?= $p['carrera_id'] ?>" data-nombre="<?= esc($p['nombre']) ?>"
                                        data-vigencia="<?= esc($p['fecha_vigencia']) ?>" data-activo="<?= $p['activo'] ?>">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <a href="<?= base_url('admin/planes-estudio/eliminar/' . $p['id']) ?>"
                                        class="btn-action btn-delete"
                                        onclick="return confirm('¿Eliminar este plan de estudios?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <!-- ===============================
            📚 SECCIÓN 2 - ASIGNAR MATERIAS
      ================================ -->
            <section id="materias" class="tab-content">
                <form id="formAsignar" action="<?= base_url('admin/planes-estudio/agregarMateria') ?>" method="POST"
                    class="form-asignacion">
                    <div class="form-group">
                        <label>Selecciona un Plan:</label>
                        <select name="plan_id" id="planSelect" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($planes as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= esc($p['nombre']) ?> (<?= esc($p['carrera']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Materia:</label>
                        <select name="materia_id" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($materias as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= esc($m['nombre']) ?> (<?= esc($m['clave']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ciclo:</label>
                        <input type="number" name="cuatrimestre" min="1" max="15" required>
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

                <!-- 🧾 Tabla de materias del plan seleccionado -->
                <div id="materiasAsignadas" class="tabla-container" style="margin-top: 2rem; display: none;">
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

    <!-- ===============================
        🪶 MODAL NUEVO/EDITAR PLAN
  ================================ -->
    <div id="modalPlan" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3 id="modalTitle">Nuevo Plan</h3>

            <form id="formPlan" class="form-modal" method="POST" action="<?= base_url('admin/planes-estudio/crear') ?>">
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

    <!-- ===============================
        ⚙️ SCRIPTS
  ================================ -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const tabs = document.querySelectorAll('.tab-btn');
            const contents = document.querySelectorAll('.tab-content');

            // Alternar pestañas
            tabs.forEach(btn => {
                btn.addEventListener('click', () => {
                    tabs.forEach(b => b.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));
                    btn.classList.add('active');
                    document.getElementById(btn.dataset.tab).classList.add('active');
                });
            });

            // Modal Plan
            const modal = document.getElementById("modalPlan");
            const closeBtn = modal.querySelector(".close-btn");
            const btnNuevo = document.getElementById("btnNuevoPlan");
            const form = document.getElementById("formPlan");
            const modalTitle = document.getElementById("modalTitle");

            btnNuevo.addEventListener("click", () => {
                form.action = "<?= base_url('admin/planes-estudio/crear') ?>";
                form.reset();
                modalTitle.textContent = "Nuevo Plan de Estudio";
                modal.style.display = "flex";
            });

            closeBtn.addEventListener("click", () => modal.style.display = "none");

            document.querySelectorAll(".btn-edit").forEach(btn => {
                btn.addEventListener("click", () => {
                    const id = btn.dataset.id;
                    form.action = `<?= base_url('admin/planes-estudio/actualizar') ?>/${id}`;
                    document.getElementById("idPlan").value = id;
                    document.getElementById("carrera_id").value = btn.dataset.carrera;
                    document.getElementById("nombre").value = btn.dataset.nombre;
                    document.getElementById("fecha_vigencia").value = btn.dataset.vigencia;
                    document.getElementById("activo").value = btn.dataset.activo;
                    modalTitle.textContent = "Editar Plan de Estudio";
                    modal.style.display = "flex";
                });
            });

            window.addEventListener("click", e => {
                if (e.target === modal) modal.style.display = "none";
            });

            // === AJAX: cargar materias del plan seleccionado ===
            const planSelect = document.getElementById('planSelect');
            const tablaMateriasBody = document.getElementById('tablaMateriasBody');
            const tablaContainer = document.getElementById('materiasAsignadas');

            planSelect.addEventListener('change', async () => {
                const planId = planSelect.value;
                if (!planId) {
                    tablaContainer.style.display = 'none';
                    return;
                }

                try {
                    const res = await fetch(`<?= base_url('admin/planes-estudio/materias-por-plan') ?>/${planId}`);
                    const data = await res.json();
                    tablaMateriasBody.innerHTML = '';

                    if (data.length === 0) {
                        tablaMateriasBody.innerHTML = `<tr><td colspan="5" style="text-align:center;">Este plan no tiene materias asignadas.</td></tr>`;
                    } else {
                        data.forEach(m => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                <td>${m.clave}</td>
                <td>${m.nombre}</td>
                <td>${m.cuatrimestre}</td>
                <td>${m.tipo}</td>
                <td><button class="btn-action btn-delete" data-id="${m.id}"><i class="fa fa-trash"></i></button></td>`;
                            tablaMateriasBody.appendChild(row);
                        });

                        // Asignar evento a botones de eliminar
                        tablaMateriasBody.querySelectorAll('.btn-delete').forEach(btn => {
                            btn.addEventListener('click', async () => {
                                const id = btn.dataset.id;
                                const confirm = await Swal.fire({
                                    title: '¿Eliminar materia del plan?',
                                    text: 'Esta acción no se puede deshacer',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ff9e64',
                                    cancelButtonColor: '#666',
                                    confirmButtonText: 'Sí, eliminar'
                                });

                                if (confirm.isConfirmed) {
                                    await fetch(`<?= base_url('admin/planes-estudio/eliminar-materia') ?>/${id}`);
                                    btn.closest('tr').remove();
                                }
                            });
                        });
                    }

                    tablaContainer.style.display = 'block';
                } catch (error) {
                    console.error(error);
                    Swal.fire('Error', 'No se pudieron cargar las materias del plan', 'error');
                }
            });
        });
    </script>

    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script src="<?= base_url('assets/js/alerts.js') ?>"></script>
</body>

</html>