<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Ciclos Académicos</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/ciclos.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <div id="alertContainer" class="alert-container"></div>
    <!-- ✅ Modal de confirmación -->
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
            <h2>Gestión de Ciclos Académicos</h2>
            <br>

            <button id="btnNuevoCiclo" class="btn-nuevo"><i class="fa fa-plus"></i> Nuevo Ciclo</button>

            <?php if (session()->getFlashdata('msg')): ?>
                <script>
                    document.addEventListener("DOMContentLoaded", () =>
                        Swal.fireSuccess("<?= session()->getFlashdata('msg') ?>")
                    );
                </script>
            <?php endif; ?>

            <table class="tabla-crud">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Parciales</th>
                        <th>Duración</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ciclos)): ?>
                        <?php foreach ($ciclos as $c): ?>
                            <tr>
                                <td><?= esc($c['id']) ?></td>
                                <td><?= esc($c['nombre']) ?></td>
                                <td><?= esc($c['num_parciales']) ?></td>
                                <td><?= esc($c['duracion_meses']) ?> meses</td>
                                <td><?= esc($c['fecha_inicio']) ?></td>
                                <td><?= esc($c['fecha_fin']) ?></td>
                                <td><?= $c['activo'] ? '✅' : '❌' ?></td>
                                <td class="acciones">
                                    <button class="btn-action btn-status"
                                        data-url="<?= base_url('admin/ciclos/estado/' . $c['id']) ?>"
                                        data-tooltip="Cambiar estado">
                                        <i class="fa fa-sync-alt"></i>
                                    </button>

                                    <button class="btn-action btn-edit" data-id="<?= $c['id'] ?>"
                                        data-nombre="<?= esc($c['nombre']) ?>" data-descripcion="<?= esc($c['descripcion']) ?>"
                                        data-parciales="<?= $c['num_parciales'] ?>" data-duracion="<?= $c['duracion_meses'] ?>"
                                        data-inicio="<?= $c['fecha_inicio'] ?>" data-fin="<?= $c['fecha_fin'] ?>"
                                        data-activo="<?= $c['activo'] ?>" data-tooltip="Editar ciclo">
                                        <i class="fa fa-edit"></i>
                                    </button>

                                    <button class="btn-action btn-delete"
                                        data-url="<?= base_url('admin/ciclos/eliminar/' . $c['id']) ?>"
                                        data-tooltip="Eliminar ciclo">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No hay ciclos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- 🧱 Modal Crear Ciclo -->
    <div id="modalCiclo" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3>Nuevo Ciclo Académico</h3>
            <form method="POST" action="<?= base_url('admin/ciclos/crear') ?>" class="form-modal">

                <div class="form-group">
                    <label><i class="fa fa-book"></i> Nombre:</label>
                    <input type="text" name="nombre" placeholder="Ej. Enero-Junio 2025" required>
                </div>

                <div class="form-group">
                    <label><i class="fa fa-align-left"></i> Descripción:</label>
                    <textarea name="descripcion" placeholder="Opcional..." rows="3"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fa fa-list-ol"></i> Parciales:</label>
                        <select name="num_parciales" required>
                            <option value="">-- Selecciona --</option>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> Parcial<?= $i > 1 ? 'es' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-clock"></i> Duración:</label>
                        <select name="duracion_meses" id="duracionSelect" required>
                            <option value="">-- Selecciona --</option>
                            <option value="2">Bimestral (2 meses)</option>
                            <option value="3">Trimestral (3 meses)</option>
                            <option value="4">Cuatrimestral (4 meses)</option>
                            <option value="6">Semestral (6 meses)</option>
                            <option value="12">Anual (12 meses)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fa fa-calendar"></i> Fecha de inicio:</label>
                        <input type="date" name="fecha_inicio" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-calendar"></i> Fecha fin:</label>
                        <input type="date" name="fecha_fin" required>
                    </div>
                </div>


                <div class="form-group-inline">
                    <label><input type="checkbox" name="activo" checked> Activo</label>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-nuevo">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>


    <div id="modalEditarCiclo" class="modal">
        <div class="modal-content">
            <span class="close-btn-edit">&times;</span>
            <h3>Editar Ciclo Académico</h3>

            <form method="POST" action="<?= base_url('admin/ciclos/actualizar') ?>" class="form-modal">
                <?= csrf_field() ?>

                <input type="hidden" name="id" id="edit_id">

                <div class="form-group">
                    <label><i class="fa fa-book"></i> Nombre:</label>
                    <input type="text" name="nombre" id="edit_nombre" required>
                </div>

                <div class="form-group">
                    <label><i class="fa fa-align-left"></i> Descripción:</label>
                    <textarea name="descripcion" id="edit_descripcion"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Parciales:</label>
                        <input type="number" name="num_parciales" id="edit_parciales" min="1" max="6" required>
                    </div>

                    <div class="form-group">
                        <label>Duración (meses):</label>
                        <input type="number" name="duracion_meses" id="edit_duracion" min="1" max="12" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha inicio:</label>
                        <input type="date" name="fecha_inicio" id="edit_inicio" required>
                    </div>

                    <div class="form-group">
                        <label>Fecha fin:</label>
                        <input type="date" name="fecha_fin" id="edit_fin" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><input type="checkbox" name="activo" id="edit_activo"> Activo</label>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-nuevo">
                        <i class="fa fa-save"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>


    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const modal = document.getElementById("modalCiclo");
            const btn = document.getElementById("btnNuevoCiclo");
            const close = modal.querySelector(".close-btn");
            const select = document.getElementById("duracionSelect");
            select.addEventListener("change", () => {
                const meses = select.value;
                if (meses) console.log(`Duración seleccionada: ${meses} meses`);
            });

            btn.onclick = () => modal.style.display = "flex";
            close.onclick = () => modal.style.display = "none";
            modal.addEventListener("click", e => {
                if (e.target === modal) modal.style.display = "none";
            });


            document.querySelectorAll(".btn-delete").forEach(btn => {
                btn.addEventListener("click", async () => {
                    const url = btn.dataset.url;
                    const confirm = await Swal.fireConfirm("¿Eliminar ciclo?", "Esta acción no se puede deshacer");
                    if (confirm.isConfirmed) {
                        await fetch(url);
                        Swal.fireSuccess("Ciclo eliminado");
                        setTimeout(() => location.reload(), 1000);
                    }
                });
            });

            document.querySelectorAll(".btn-status").forEach(btn => {
                btn.addEventListener("click", async () => {
                    await fetch(btn.dataset.url);
                    Swal.fireSuccess("Estado actualizado");
                    setTimeout(() => location.reload(), 800);
                });
            });
        });

        document.querySelectorAll(".btn-edit").forEach(btn => {
            btn.addEventListener("click", () => {

                document.getElementById("edit_id").value = btn.dataset.id;
                document.getElementById("edit_nombre").value = btn.dataset.nombre;
                document.getElementById("edit_descripcion").value = btn.dataset.descripcion;
                document.getElementById("edit_parciales").value = btn.dataset.parciales;
                document.getElementById("edit_duracion").value = btn.dataset.duracion;
                document.getElementById("edit_inicio").value = btn.dataset.inicio;
                document.getElementById("edit_fin").value = btn.dataset.fin;

                const checkActivo = document.getElementById("edit_activo");
                checkActivo.checked = btn.dataset.activo == "1";

                document.getElementById("modalEditarCiclo").style.display = "flex";
            });
        });

        document.querySelector(".close-btn-edit").onclick = () => {
            document.getElementById("modalEditarCiclo").style.display = "none";
        };

    </script>

    <script>
        document.querySelector('form[action*="actualizar"]').addEventListener('submit', e => {
            console.log("SUBMIT DETECTADO");
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            const formActualizar = document.querySelector('form[action*="actualizar"]');

            formActualizar.addEventListener('submit', function (e) {

                const inicio = document.getElementById("edit_inicio").value;
                const fin = document.getElementById("edit_fin").value;
                const duracionEsperada = parseInt(document.getElementById("edit_duracion").value);

                const fechaInicio = new Date(inicio);
                const fechaFin = new Date(fin);

                const milisegundos = fechaFin - fechaInicio;
                const diasReales = milisegundos / (1000 * 60 * 60 * 24);

                const mesesCalculados = Math.round(diasReales / 30);

                if (mesesCalculados !== duracionEsperada) {
                    e.preventDefault();

                    mostrarAlerta(
                        `Las fechas seleccionadas producen <b>${mesesCalculados} meses</b>, ` +
                        `pero elegiste <b>${duracionEsperada} meses</b>.`,
                        "warning",
                        5000
                    );

                    return;
                }

                console.log("VALIDACIÓN OK — enviando formulario");
            });

        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            const formCrear = document.querySelector('form[action*="crear"]');


            formActualizar.addEventListener('submit', function (e) {

                const inicio = document.getElementById("edit_inicio").value;
                const fin = document.getElementById("edit_fin").value;
                const duracionEsperada = parseInt(document.getElementById("edit_duracion").value);

                const fechaInicio = new Date(inicio);
                const fechaFin = new Date(fin);

                const milisegundos = fechaFin - fechaInicio;
                const diasReales = milisegundos / (1000 * 60 * 60 * 24);

                const mesesCalculados = Math.round(diasReales / 30);

                if (mesesCalculados !== duracionEsperada) {
                    e.preventDefault();

                    mostrarAlerta(
                        `Las fechas seleccionadas producen <b>${mesesCalculados} meses</b>, ` +
                        `pero elegiste <b>${duracionEsperada} meses</b>.`,
                        "warning",
                        5000
                    );

                    return;
                }

                console.log("VALIDACIÓN OK — enviando formulario");
            });

        });
    </script>




    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script src="<?= base_url('assets/js/alert.js') ?>"></script>
</body>

</html>