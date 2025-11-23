<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<!-- 🌟 Alertas y modal global -->
<div id="alertContainer" class="alert-container"></div>

<div id="confirmModal" class="confirm-modal hidden">
    <div class="confirm-box">
        <h3 id="confirmTitle">Confirmar acción</h3>
        <p id="confirmMessage">¿Estás seguro de continuar?</p>
        <div class="confirm-buttons">
            <button id="confirmCancelar">Cancelar</button>
            <button id="confirmAceptar">Aceptar</button>
        </div>
    </div>
</div>

<!-- 🎓 CONTENEDOR PRINCIPAL -->
<div class="calificaciones-alumno">

    <h2 class="titulo-calificaciones">
        <i class="fas fa-star"></i> Calificaciones
    </h2>

    <!-- =============================
         🧭 TABS
    =============================== -->
    <div class="cal-tabs">
        <button class="tab-btn activo" data-tab="especificas">
            <i class="fas fa-book"></i> Por Materia
        </button>
        <button class="tab-btn" data-tab="kardex">
            <i class="fas fa-layer-group"></i> Kárdex
        </button>
    </div>

    <!-- =============================
    1️⃣ POR MATERIA (TAB)
============================== -->
    <div id="tab-especificas" class="tab-content" style="display:block">

        <!-- 📌  TABLA GENERAL DEL CICLO -->
        <div id="vista-tabla-general">

            <h3 class="subtitulo">
                <i class="fas fa-graduation-cap"></i> Desempeño del ciclo actual
            </h3>

            <table class="tabla-ciclo" id="tablaCiclo">
                <thead id="theadCiclo"></thead>
                <tbody id="tbodyCiclo"></tbody>
            </table>

        </div>

        <!-- 📌 VISTA DE CRITERIOS DEL PARCIAL -->
        <div id="vista-criterios" class="oculto">

            <button id="btnVolverTabla" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver
            </button>

            <h3 class="subtitulo">
                <i class="fas fa-list-ul"></i>
                Criterios del Parcial <span id="critParcialNumero"></span> —
                <span id="critMateriaNombre"></span>
            </h3>

            <table class="tabla-criterios">
                <thead>
                    <tr>
                        <th>Criterio</th>
                        <th>Porcentaje</th>
                        <th>Calificación</th>
                        <th>Obtenido</th>

                    </tr>
                </thead>
                <tbody id="tbodyCriterios"></tbody>
            </table>

            <div class="resumen-criterios">
                <h4>Calificación del Parcial: <span id="critParcialFinal"></span></h4>
            </div>

        </div>

    </div>


    <!-- =============================
         2️⃣ KÁRDEX GENERAL
    =============================== -->
    <div id="tab-kardex" class="tab-content" style="display:none">

        <h3 class="subtitulo">
            <i class="fas fa-history"></i> Historial académico
        </h3>

        <?php if (!empty($kardex)): ?>

            <table class="tabla-kardex">
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Ciclo</th>
                        <th>Profesor</th>
                        <th>Final</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kardex as $k): ?>
                        <tr>
                            <td><?= esc($k['materia']) ?></td>
                            <td><?= esc($k['ciclo']) ?></td>
                            <td><?= esc($k['profesor']) ?></td>
                            <td><?= esc($k['final']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="promedio-global">
                <h4>
                    <i class="fas fa-chart-pie"></i> Promedio General:
                    <span><?= esc($promedioGeneral) ?></span>
                </h4>
            </div>

        <?php else: ?>
            <p class="placeholder">No hay registros en tu historial académico.</p>
        <?php endif; ?>

    </div>

</div>

<script>
    // Tabs
    document.querySelectorAll(".tab-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("activo"));
            btn.classList.add("activo");

            const tab = btn.dataset.tab;
            document.querySelectorAll(".tab-content").forEach(c => c.style.display = "none");
            document.getElementById("tab-" + tab).style.display = "block";
        });
    });
</script>

<link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/alumnos/calificaciones.css') ?>">
<script src="<?= base_url('assets/js/alert.js') ?>"></script>
<script src="<?= base_url('assets/js/alumnos/calificaciones.js') ?>"></script>

<?= $this->endSection() ?>