<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/usuarios.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <main class="content-dark">
        <div class="crud-container">
            <h2>Gestión de Usuarios</h2>
            <br>

            <button type="button" id="btnAbrirModal" class="btn-nuevo">
                <i class="fa fa-plus"></i> Nuevo Usuario
            </button>
            <button type="button" id="btnImportarExcel" class="btn-importar">
                <i class="fa fa-file-import"></i> Importar desde Excel
            </button>

            <a href="<?= base_url('admin/usuarios/plantilla') ?>" class="btn-excel">
                <i class="fa fa-file-excel"></i> Descargar plantilla (Formulario)
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

            <!-- Mostrar secciones por grupo -->
            <!-- Tabs de Roles -->
            <div class="tabs-container">
                <ul class="tabs">
                    <?php foreach ($grupos as $nombre => $lista): ?>
                        <?php if (!empty($lista)): ?>
                            <li class="tab-item" data-tab="<?= $nombre ?>"><?= ucfirst($nombre) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Contenido de cada Tab -->
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
                                            <a href="<?= base_url('admin/usuarios/eliminar/' . $u['id']) ?>"
                                                class="btn-action btn-delete" title="Eliminar" data-id="<?= $u['id'] ?>">
                                                <i class="fa fa-trash"></i>
                                            </a>

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
                    <div>
                        <strong>Correo:</strong>
                        <p id="modal-email"></p>
                    </div>
                </div>

                <div class="info-row" id="detalleMatricula">
                    <i class="fa fa-id-card"></i>
                    <div>
                        <strong>Matrícula:</strong>
                        <p id="modal-matricula"></p>
                    </div>
                </div>

                <div class="info-row" id="detalleEmpleado">
                    <i class="fa fa-briefcase"></i>
                    <div>
                        <strong>Número de Empleado:</strong>
                        <p id="modal-num_empleado"></p>
                    </div>
                </div>

                <div class="info-row">
                    <i class="fa fa-calendar-check"></i>
                    <div>
                        <strong>Último login:</strong>
                        <p id="modal-ultimo_login"></p>
                    </div>
                </div>

                <div class="info-row">
                    <i class="fa fa-clock"></i>
                    <div>
                        <strong>Fecha de creación:</strong>
                        <p id="modal-created_at"></p>
                    </div>
                </div>

                <div class="info-row">
                    <i class="fa fa-refresh"></i>
                    <div>
                        <strong>Última actualización:</strong>
                        <p id="modal-updated_at"></p>
                    </div>
                </div>

                <div class="info-row">
                    <i class="fa fa-user-check"></i>
                    <div>
                        <strong>Verificado:</strong>
                        <p id="modal-verificado"></p>
                    </div>
                </div>

                <div class="info-row">
                    <i class="fa fa-trash"></i>
                    <div>
                        <strong>Eliminado:</strong>
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

            <form id="formCrearUsuario" class="form-modal">
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

                    <div class="form-group">
                        <label>Nombres:</label>
                        <input type="text" name="nombres" id="edit_nombres" required>
                    </div>

                    <div class="form-group">
                        <label>Apellido paterno:</label>
                        <input type="text" name="apellido_paterno" id="edit_apellido_paterno" required>
                    </div>

                    <div class="form-group">
                        <label>Apellido materno:</label>
                        <input type="text" name="apellido_materno" id="edit_apellido_materno">
                    </div>

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

    <?php if (session()->getFlashdata('success')): ?>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Importación completada',
                    html: `
      <?= session()->getFlashdata('success') ?><br><br>
      <?php if (session()->getFlashdata('import_report')): ?>
        <div style="max-height:300px;overflow:auto;text-align:left;">
          <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
              <tr style="background:#f5f5f5;">
                <th style="padding:4px;border:1px solid #ccc;">Nombre</th>
                <th style="padding:4px;border:1px solid #ccc;">Rol</th>
                <th style="padding:4px;border:1px solid #ccc;">Código</th>
                <th style="padding:4px;border:1px solid #ccc;">Correo</th>
                <th style="padding:4px;border:1px solid #ccc;">Contraseña</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (json_decode(session()->getFlashdata('import_report'), true) as $r): ?>
                <tr>
                  <td style="padding:4px;border:1px solid #ccc;"><?= esc($r['nombre']) ?></td>
                  <td style="padding:4px;border:1px solid #ccc;"><?= esc($r['rol']) ?></td>
                  <td style="padding:4px;border:1px solid #ccc;"><?= esc($r['codigo']) ?></td>
                  <td style="padding:4px;border:1px solid #ccc;"><?= esc($r['email']) ?></td>
                  <td style="padding:4px;border:1px solid #ccc;"><?= esc($r['password']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <br>
        <a id="autoDownload" href="<?= base_url('admin/usuarios/descargar-credenciales') ?>"
           style="display:none;" download></a>
      <?php endif; ?>
    `,
                    width: 800,
                    confirmButtonText: 'Aceptar'
                });

                // 🚀 Descargar automáticamente el CSV
                const link = document.getElementById('autoDownload');
                if (link) {
                    setTimeout(() => link.click(), 1200);
                }
            });
        </script>
    <?php endif; ?>



    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const tabs = document.querySelectorAll(".tab-item");
            const contents = document.querySelectorAll(".tab-content");

            if (tabs.length > 0) {
                // Activar la primera pestaña por defecto
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
        });

    </script>
    <script>
        const baseUrl = "<?= base_url() ?>";
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const btnImportar = document.getElementById("btnImportarExcel");
            const modalImportar = document.getElementById("modalImportar");
            const closeBtns = document.querySelectorAll(".close-btn");

            btnImportar.addEventListener("click", () => {
                modalImportar.style.display = "flex"; // ✅ centrado correctamente
            });

            closeBtns.forEach(btn =>
                btn.addEventListener("click", () => {
                    btn.closest(".modal").style.display = "none";
                })
            );

            window.addEventListener("click", (e) => {
                if (e.target === modalImportar) {
                    modalImportar.style.display = "none";
                }
            });
        });


    </script>
    <script src="<?= base_url('assets/js/admin/usuarios.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script>
        (function initSidebar() {
            // Espera hasta que existan los nodos
            const btn = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const content = document.querySelector('.content');

            if (!btn || !sidebar || !content) {
                requestAnimationFrame(initSidebar);
                return;
            }

            // Colapsado por defecto
            sidebar.classList.add('collapsed');
            content.classList.add('collapsed');

            // Delegación en captura: gana a otros listeners que bloquean el click
            document.addEventListener('click', function (e) {
                const hit = e.target.closest('#sidebarToggle');
                if (!hit) return;

                e.preventDefault();
                // e.stopPropagation(); // opcional

                sidebar.classList.toggle('collapsed');
                content.classList.toggle('collapsed');
            }, true); // 👈 captura
        })();
    </script>

</body>

</html>