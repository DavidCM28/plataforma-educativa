<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Criterios</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/criterios.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <main class="content-dark">
        <div class="crud-container">
            <h2>Gestión de Criterios y Ponderaciones</h2>

            <div class="tabs">
                <button class="tab-btn active" data-tab="criterios">Criterios</button>
                <button class="tab-btn" data-tab="ponderaciones">Ponderaciones</button>
            </div>

            <!-- ✅ TAB 1: Criterios -->
            <section id="criterios" class="tab-content active">
                <button id="btnNuevoCriterio" class="btn-nuevo"><i class="fa fa-plus"></i> Nuevo Criterio</button>
                <table class="tabla-crud">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($criterios as $c): ?>
                            <tr>
                                <td><?= $c['id'] ?></td>
                                <td><?= esc($c['nombre']) ?></td>
                                <td><?= esc($c['descripcion'] ?? '—') ?></td>
                                <td><?= $c['activo'] ? '✅' : '❌' ?></td>
                                <td class="acciones">
                                    <button class="btn-action btn-status"
                                        data-url="<?= base_url('admin/criterios/estado/' . $c['id']) ?>">
                                        <i class="fa fa-sync-alt"></i>
                                    </button>
                                    <button class="btn-action btn-delete"
                                        data-url="<?= base_url('admin/criterios/eliminar/' . $c['id']) ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <!-- ✅ TAB 2: Ponderaciones -->
            <section id="ponderaciones" class="tab-content">
                <form class="form-asignacion" method="POST"
                    action="<?= base_url('admin/criterios/ponderaciones/guardar') ?>">
                    <h3>Asignar Ponderación</h3>
                    <div class="form-row">
                        <select name="ciclo_id" required>
                            <option value="">-- Ciclo --</option>
                            <?php foreach ($ciclos as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= esc($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select name="parcial_num" required disabled>
                            <option value="">-- Selecciona un ciclo primero --</option>
                        </select>
                        <small id="infoParciales"
                            style="color: var(--text-light); display:block; margin-top: 4px;"></small>


                        <select name="criterio_id" required>
                            <option value="">-- Criterio --</option>
                            <?php foreach ($criterios as $cr): ?>
                                <option value="<?= $cr['id'] ?>"><?= esc($cr['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <input type="number" step="0.01" name="porcentaje" placeholder="% Ponderación" required>
                        <button type="submit" id="btnGuardarPond" class="btn-nuevo">
                            <i class="fa fa-save"></i>
                        </button>

                    </div>
                </form>

                <!-- 🔹 Barra de progreso -->
                <div id="progresoPonderacion" class="barra-progreso">
                    <div class="barra" style="width: 0%">0%</div>
                </div>


                <!-- 🔹 Tarjetas dinámicas -->
                <div id="contenedorPonderaciones" class="ponderaciones-grid"></div>

            </section>
        </div>
    </main>

    <!-- Modal Crear Criterio -->
    <div id="modalCriterio" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3>Nuevo Criterio</h3>
            <form method="POST" action="<?= base_url('admin/criterios/crear') ?>" class="form-modal">
                <div class="form-group">
                    <label><i class="fa fa-book"></i> Nombre:</label>
                    <input type="text" name="nombre" placeholder="Ej. Participación" required>
                </div>
                <div class="form-group">
                    <label><i class="fa fa-align-left"></i> Descripción:</label>
                    <textarea name="descripcion" placeholder="Opcional..." rows="3"></textarea>
                </div>
                <label><input type="checkbox" name="activo" checked> Activo</label>
                <div class="modal-footer">
                    <button type="submit" class="btn-nuevo"><i class="fa fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // === Tabs ===
            document.querySelectorAll(".tab-btn").forEach(btn => {
                btn.addEventListener("click", () => {
                    document.querySelectorAll(".tab-btn, .tab-content").forEach(el => el.classList.remove("active"));
                    btn.classList.add("active");
                    document.getElementById(btn.dataset.tab).classList.add("active");
                });
            });

            // === Modal de criterios ===
            const modal = document.getElementById("modalCriterio");
            document.getElementById("btnNuevoCriterio").onclick = () => modal.style.display = "flex";
            modal.querySelector(".close-btn").onclick = () => modal.style.display = "none";
            window.onclick = e => { if (e.target === modal) modal.style.display = "none"; };

            // === Acciones eliminar criterio ===
            document.querySelectorAll(".btn-delete").forEach(btn => {
                btn.addEventListener("click", async () => {
                    const url = btn.dataset.url;
                    const confirm = await Swal.fireConfirm("¿Eliminar?", "No podrás revertirlo");
                    if (confirm.isConfirmed) {
                        await fetch(url);
                        Swal.fireSuccess("Eliminado correctamente");
                        setTimeout(() => location.reload(), 1000);
                    }
                });
            });

            // === Control de Ponderaciones ===
            const formPond = document.querySelector(".form-asignacion");
            if (formPond) {
                const cicloSel = formPond.querySelector("[name='ciclo_id']");
                const parcialSel = formPond.querySelector("[name='parcial_num']");
                const criterioSel = formPond.querySelector("[name='criterio_id']");
                const porcentajeInput = formPond.querySelector("[name='porcentaje']");
                const btnGuardar = document.getElementById("btnGuardarPond");
                const barra = document.querySelector("#progresoPonderacion .barra");
                const contenedorBarra = document.querySelector("#progresoPonderacion");
                const infoParciales = document.getElementById("infoParciales");
                const contenedorPonderaciones = document.getElementById("contenedorPonderaciones");

                // === 1️⃣ Cargar parciales dinámicos según ciclo ===
                async function cargarParciales() {
                    const ciclo = cicloSel.value;
                    parcialSel.innerHTML = '<option value="">-- Parcial --</option>';
                    infoParciales.textContent = "";
                    parcialSel.disabled = true;

                    if (!ciclo) return;

                    try {
                        const res = await fetch(`${window.location.origin}/admin/criterios/ciclo-parciales/${ciclo}`);
                        const data = await res.json();

                        if (data.num_parciales && data.num_parciales > 0) {
                            for (let i = 1; i <= data.num_parciales; i++) {
                                const opt = document.createElement("option");
                                opt.value = i;
                                opt.textContent = `Parcial ${i}`;
                                parcialSel.appendChild(opt);
                            }
                            parcialSel.disabled = false;
                        }
                    } catch {
                        infoParciales.textContent = "⚠️ Error al cargar los parciales.";
                    }
                }

                // === 2️⃣ Cargar ponderaciones dinámicamente ===
                async function cargarPonderaciones() {
                    const ciclo = cicloSel.value;
                    const parcial = parcialSel.value;
                    contenedorPonderaciones.innerHTML = "";

                    if (!ciclo || !parcial) return;

                    const res = await fetch(`${window.location.origin}/admin/criterios/ponderaciones/listar/${ciclo}/${parcial}`);
                    const data = await res.json();

                    if (data.length === 0) {
                        contenedorPonderaciones.innerHTML = `<p style="color: var(--text-light); margin-top: .8rem;">No hay ponderaciones asignadas aún.</p>`;
                        return;
                    }

                    data.forEach(p => {
                        const card = document.createElement("div");
                        card.className = "ponderacion-card";
                        card.innerHTML = `
                    <div class="ponderacion-info">
                        <strong>${p.criterio}</strong><br>
                        ${p.porcentaje}% asignado
                    </div>
                    <button class="btn-delete-mini" data-id="${p.id}">
                        <i class="fa fa-trash"></i>
                    </button>
                `;
                        contenedorPonderaciones.appendChild(card);
                    });

                    // 🔹 Eliminar ponderación sin recargar
                    document.querySelectorAll(".btn-delete-mini").forEach(btn => {
                        btn.addEventListener("click", async () => {
                            const cicloActual = cicloSel.value;
                            const parcialActual = parcialSel.value;

                            const confirm = await Swal.fireConfirm("¿Eliminar ponderación?", "No podrás revertirlo");
                            if (!confirm.isConfirmed) return;

                            await fetch(`${window.location.origin}/admin/criterios/ponderaciones/eliminar/${btn.dataset.id}`);
                            Swal.fireSuccess("Eliminado correctamente");

                            // Mantener selección actual y refrescar
                            cicloSel.value = cicloActual;
                            parcialSel.value = parcialActual;

                            cargarPonderaciones();
                            actualizarBarra();
                        });
                    });
                }

                // === 3️⃣ Actualizar barra de progreso ===
                async function actualizarBarra() {
                    const ciclo = cicloSel.value;
                    const parcial = parcialSel.value;
                    if (!ciclo || !parcial) {
                        barra.style.width = "0%";
                        barra.textContent = "0%";
                        return;
                    }

                    const res = await fetch(`${window.location.origin}/admin/criterios/ponderaciones/total/${ciclo}/${parcial}`);
                    const data = await res.json();
                    const total = parseFloat(data.total) || 0;

                    barra.style.width = `${Math.min(total, 100)}%`;
                    barra.textContent = `${total.toFixed(2)}%`;
                    contenedorBarra.dataset.ok = total > 100 ? "false" : "true";
                }

                // === 4️⃣ Guardar ponderación sin recargar ===
                formPond.addEventListener("submit", async (e) => {
                    e.preventDefault();

                    const cicloActual = cicloSel.value;
                    const parcialActual = parcialSel.value;
                    btnGuardar.disabled = true;

                    // 🚫 Validar si ya está al 100%
                    const totalTexto = barra.textContent.replace("%", "").trim();
                    const totalActual = parseFloat(totalTexto) || 0;

                    if (totalActual >= 100) {
                        Swal.close(); // cierra cualquier alerta previa
                        await Swal.fire({
                            icon: "warning",
                            title: "Límite alcanzado",
                            text: "Este parcial ya tiene el 100% asignado. No puedes añadir más criterios.",
                            confirmButtonText: "Entendido",
                            confirmButtonColor: "#ff9e64",
                            background: "#1e1e1e",
                            color: "#fff",
                            customClass: { popup: "swal-custom" },
                        });


                        // 🔹 Forzar limpieza de posibles modales residuales
                        document.querySelectorAll(".modal").forEach(m => (m.style.display = "none"));


                        btnGuardar.disabled = false;
                        return;
                    }



                    const formData = new FormData(formPond);
                    const res = await fetch(formPond.action, { method: "POST", body: formData });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fireSuccess("Ponderación guardada correctamente");

                        // 🔹 Limpiar solo criterio y porcentaje
                        criterioSel.value = "";
                        porcentajeInput.value = "";

                        // 🔹 Mantener selección
                        cicloSel.value = cicloActual;
                        parcialSel.value = parcialActual;

                        // 🔹 Actualizar dinámicamente
                        await cargarPonderaciones();
                        await actualizarBarra();
                    } else {
                        Swal.fireError("Error al guardar ponderación");
                    }

                    btnGuardar.disabled = false;
                });


                // === 5️⃣ Eventos reactivos ===
                cicloSel.addEventListener("change", async () => {
                    await cargarParciales();
                    contenedorPonderaciones.innerHTML = "";
                    actualizarBarra();
                });

                parcialSel.addEventListener("change", () => {
                    cargarPonderaciones();
                    actualizarBarra();
                });

                porcentajeInput.addEventListener("input", actualizarBarra);

                // Inicializar barra
                actualizarBarra();
            }
        });
    </script>



    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script src="<?= base_url('assets/js/alert.js') ?>"></script>
</body>

</html>