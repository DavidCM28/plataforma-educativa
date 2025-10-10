<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Grupos</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/grupos.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

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
                        Swal.fireSuccess("<?= session()->getFlashdata('msg') ?>");
                    });
                </script>
            <?php endif; ?>

            <!-- ===============================
           📋 TABLA DE GRUPOS
      ================================ -->
            <table class="tabla-crud">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Carrera</th>
                        <th>Grupo</th>
                        <th>Periodo</th>
                        <th>Turno</th>
                        <th>Tutor</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($grupos)): ?>
                        <?php foreach ($grupos as $g): ?>
                            <tr>
                                <td><?= esc($g['id']) ?></td>
                                <td><?= esc($g['siglas']) ?></td>
                                <td><strong><?= esc($g['grupo']) ?></strong></td>
                                <td><?= esc($g['periodo']) ?></td>
                                <td><?= esc($g['turno']) ?></td>
                                <td><?= esc($g['tutor'] ?? '—') ?></td>
                                <td><?= $g['activo'] ? '✅' : '❌' ?></td>
                                <td class="acciones">
                                    <button class="btn-action btn-delete" data-id="<?= $g['id'] ?>">
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

    <!-- ===============================
       🧱 MODAL NUEVO GRUPO
  ================================ -->
    <div id="modalGrupo" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3 id="modalTitle">Nuevo Grupo</h3>

            <form id="formGrupo" class="form-modal" method="POST" action="<?= base_url('admin/grupos/crear') ?>">
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
                    <label>Ciclo o Periodo:</label><select name="periodo" id="periodo" required>
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

    <!-- ===============================
       ⚙️ SCRIPTS
  ================================ -->
    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script src="<?= base_url('assets/js/alert.js') ?>"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const modal = document.getElementById("modalGrupo");
            const closeBtn = modal.querySelector(".close-btn");
            const btnNuevo = document.getElementById("btnNuevoGrupo");

            btnNuevo.addEventListener("click", () => {
                modal.style.display = "flex";
            });

            closeBtn.addEventListener("click", () => modal.style.display = "none");
            window.addEventListener("click", (e) => {
                if (e.target === modal) modal.style.display = "none";
            });

            // 🗑️ Eliminar grupo
            document.querySelectorAll(".btn-delete").forEach(btn => {
                btn.addEventListener("click", async () => {
                    const id = btn.dataset.id;
                    const confirm = await Swal.fireConfirm("¿Eliminar grupo?", "Esta acción no se puede deshacer");

                    if (confirm.isConfirmed) {
                        await fetch(`<?= base_url('admin/grupos/eliminar') ?>/${id}`);
                        Swal.fireSuccess("Grupo eliminado correctamente");
                        setTimeout(() => location.reload(), 1000);
                    }
                });
            });
            // === Generar ciclos automáticamente según la duración de la carrera ===
            const carreraSelect = document.getElementById("carrera_id");
            const periodoSelect = document.getElementById("periodo");

            carreraSelect.addEventListener("change", () => {
                const duracion = carreraSelect.selectedOptions[0]?.dataset.duracion || 0;
                periodoSelect.innerHTML = `<option value="">-- Selecciona un ciclo --</option>`;

                if (duracion > 0) {
                    for (let i = 1; i <= duracion; i++) {
                        const opt = document.createElement("option");
                        opt.value = i;
                        opt.textContent = `${i}`;
                        periodoSelect.appendChild(opt);
                    }
                }
            });
        });

    </script>
</body>

</html>