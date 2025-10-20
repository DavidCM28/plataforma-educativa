<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>

    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/usuarios.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <!-- 🔔 Alertas y confirmaciones -->
    <div id="alertContainer" class="alert-container"></div>

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
            <h2><i class="fa-solid fa-users-gear"></i> Gestión de Usuarios</h2>
            <br>

            <!-- 🔹 Botones principales -->
            <button type="button" id="btnAbrirModal" class="btn-nuevo">
                <i class="fa fa-plus"></i> Nuevo Usuario
            </button>
            <button type="button" id="btnImportarExcel" class="btn-importar">
                <i class="fa fa-file-import"></i> Importar desde Excel
            </button>

            <!-- 🔹 Nuevos botones -->
            <a href="<?= base_url('admin/usuarios/plantilla-alumnos') ?>" class="btn-excel">
                <i class="fa fa-file-excel"></i> Plantilla Alumnos
            </a>
            <a href="<?= base_url('admin/usuarios/plantilla-empleados') ?>" class="btn-excel">
                <i class="fa fa-file-excel"></i> Plantilla Empleados
            </a>

            <?php
            // Agrupar usuarios por tipo de rol
            $grupos = [
                'alumnos' => [],
                'profesores' => [],
                'escolares' => [],
                'administrativos' => [],
                'otros' => []
            ];

            foreach ($usuarios as $u) {
                $rol = strtolower($u['rol']);
                if (strpos($rol, 'alumno') !== false) {
                    $grupos['alumnos'][] = $u;
                } elseif (strpos($rol, 'profesor') !== false) {
                    $grupos['profesores'][] = $u;
                } elseif (strpos($rol, 'escolar') !== false) {
                    $grupos['escolares'][] = $u;
                } elseif (strpos($rol, 'administrativo') !== false) {
                    $grupos['administrativos'][] = $u;
                } else {
                    $grupos['otros'][] = $u;
                }
            }
            ?>

            <div class="tabs-container">
                <ul class="tabs">
                    <?php foreach ($grupos as $nombre => $lista): ?>
                        <?php if (!empty($lista)): ?>
                            <li class="tab-item" data-tab="<?= $nombre ?>"><?= ucfirst($nombre) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php foreach ($grupos as $nombre => $lista): ?>
                <?php if (!empty($lista)): ?>
                    <section class="tab-content" id="tab-<?= $nombre ?>">
                        <table class="tabla-crud">
                            <thead>
                                <tr>
                                    <?php if ($nombre === 'alumnos'): ?>
                                        <th>Matrícula</th>
                                    <?php else: ?>
                                        <th>Número Empleado</th>
                                    <?php endif; ?>
                                    <th>Nombre completo</th>
                                    <th>Correo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista as $u): ?>
                                    <tr>
                                        <td><?= $nombre === 'alumnos' ? esc($u['matricula']) : esc($u['num_empleado']) ?></td>
                                        <td><?= esc($u['nombre']) . ' ' . esc($u['apellido_paterno']) . ' ' . esc($u['apellido_materno']) ?>
                                        </td>
                                        <td><?= esc($u['email']) ?></td>
                                        <td class="acciones">
                                            <a href="#" class="btn-action btn-view" data-id="<?= $u['id'] ?>" title="Ver detalles">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="#" class="btn-action btn-edit" data-id="<?= $u['id'] ?>" title="Editar">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <button class="btn-action btn-delete"
                                                data-delete-url="<?= base_url('admin/usuarios/eliminar/' . $u['id']) ?>"
                                                data-type="usuario" title="Eliminar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>
                <?php endif; ?>
            <?php endforeach; ?>

        </div>
    </main>

    <!-- Modal Detalle de Usuario -->
    <div id="userModal" class="modal">
        <div class="modal-content user-detail-card">
            <span class="close-btn">&times;</span>
            <div class="user-header">
                <img id="modal-foto" src="" alt="Foto del usuario" class="foto-usuario">
                <h3 id="modal-nombre" class="user-name">Nombre del Usuario</h3>
                <span id="modal-activo" class="user-status">Activo</span>
                <p id="modal-rol" class="user-role">Rol: Admin</p>
            </div>

            <div class="user-info">
                <div class="info-row">
                    <i class="fa fa-envelope"></i>
                    <div><strong>Correo:</strong>
                        <p id="modal-email"></p>
                    </div>
                </div>
                <div class="info-row" id="detalleMatricula">
                    <i class="fa fa-id-card"></i>
                    <div><strong>Matrícula:</strong>
                        <p id="modal-matricula"></p>
                    </div>
                </div>
                <div class="info-row" id="detalleEmpleado">
                    <i class="fa fa-briefcase"></i>
                    <div><strong>Número de Empleado:</strong>
                        <p id="modal-num_empleado"></p>
                    </div>
                </div>
                <div class="info-row"><i class="fa fa-calendar-check"></i>
                    <div><strong>Último login:</strong>
                        <p id="modal-ultimo_login"></p>
                    </div>
                </div>
                <div class="info-row"><i class="fa fa-clock"></i>
                    <div><strong>Fecha de creación:</strong>
                        <p id="modal-created_at"></p>
                    </div>
                </div>
                <div class="info-row"><i class="fa fa-refresh"></i>
                    <div><strong>Última actualización:</strong>
                        <p id="modal-updated_at"></p>
                    </div>
                </div>
                <div class="info-row"><i class="fa fa-user-check"></i>
                    <div><strong>Verificado:</strong>
                        <p id="modal-verificado"></p>
                    </div>
                </div>
                <div class="info-row"><i class="fa fa-trash"></i>
                    <div><strong>Eliminado:</strong>
                        <p id="modal-deleted_at"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear Usuario -->
    <div id="modalCrearUsuario" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3>Nuevo Usuario</h3>

            <form id="formCrearUsuario" class="form-modal" data-ajax="true">

                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombres:</label>
                        <input type="text" name="nombres" id="nombres" required>
                    </div>
                    <div class="form-group">
                        <label>Apellido paterno:</label>
                        <input type="text" name="apellido_paterno" id="apellido_paterno" required>
                    </div>
                    <div class="form-group">
                        <label>Apellido materno:</label>
                        <input type="text" name="apellido_materno" id="apellido_materno">
                    </div>
                    <div class="form-group">
                        <label>Rol:</label>
                        <select name="rol_id" id="selectRol" required>
                            <option value="">Seleccione un rol</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?= $rol['id'] ?>"><?= esc($rol['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="campoCarrera" style="display:none;">
                        <label>Carrera:</label>
                        <select name="carrera_id" id="selectCarrera">
                            <option value="">Seleccione una carrera</option>
                            <?php if (isset($carreras)):
                                foreach ($carreras as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= esc($c['nombre']) ?></option>
                                <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-nuevo">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div id="modalEditarUsuario" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3>Editar Usuario</h3>

            <form id="formEditarUsuario" class="form-modal">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="form-group"><label>Nombres:</label><input type="text" name="nombres" id="edit_nombres"
                            required></div>
                    <div class="form-group"><label>Apellido paterno:</label><input type="text" name="apellido_paterno"
                            id="edit_apellido_paterno" required></div>
                    <div class="form-group"><label>Apellido materno:</label><input type="text" name="apellido_materno"
                            id="edit_apellido_materno"></div>
                    <div class="form-group">
                        <label>Rol:</label>
                        <select name="rol_id" id="edit_rol_id" required>
                            <option value="">Seleccione un rol</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?= $rol['id'] ?>"><?= esc($rol['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nueva contraseña (opcional):</label>
                        <input type="password" name="password" id="edit_password"
                            placeholder="Dejar vacío si no desea cambiarla">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-nuevo">Actualizar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Importar Excel -->
    <div id="modalImportar" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3>Importar Usuarios desde Excel</h3>
            <form action="<?= base_url('admin/usuarios/importar') ?>" method="POST" enctype="multipart/form-data"
                class="form-modal">
                <div class="form-group">
                    <label>Selecciona archivo Excel:</label>
                    <input type="file" name="archivo_excel" accept=".xlsx,.xls" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-nuevo">Importar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const baseUrl = "<?= base_url() ?>";
    </script>
    <script src="<?= base_url('assets/js/alert.js') ?>"></script>
    <script src="<?= base_url('assets/js/admin/usuarios.js') ?>"></script>
    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const tabs = document.querySelectorAll(".tab-item");
            const contents = document.querySelectorAll(".tab-content");
            if (tabs.length > 0) {
                tabs[0].classList.add("active");
                contents[0].classList.add("active");
                tabs.forEach(tab => {
                    tab.addEventListener("click", () => {
                        const target = tab.dataset.tab;
                        tabs.forEach(t => t.classList.remove("active"));
                        contents.forEach(c => c.classList.remove("active"));
                        tab.classList.add("active");
                        document.getElementById(`tab-${target}`).classList.add("active");
                    });
                });
            }

            const selectRol = document.getElementById("selectRol");
            const campoCarrera = document.getElementById("campoCarrera");
            selectRol?.addEventListener("change", () => {
                const texto = selectRol.options[selectRol.selectedIndex]?.text.toLowerCase() || "";
                campoCarrera.style.display = texto.includes("alumno") ? "block" : "none";
            });
        });
    </script>
</body>

</html>