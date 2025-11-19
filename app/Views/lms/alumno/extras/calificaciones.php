<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<!-- 🌟 Alertas y modal global (compatibles con tu sistema) -->
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
         🧭 TABS PRINCIPALES
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
         1️⃣ CALIFICACIONES POR MATERIA
    =============================== -->
    <div id="tab-especificas" class="tab-content" style="display:block">

        <h3 class="subtitulo">
            <i class="fas fa-graduation-cap"></i> Materias del ciclo actual
        </h3>

        <?php if (!empty($materiasActuales)): ?>
            <div class="lista-materias">
                <?php foreach ($materiasActuales as $m): ?>
                    <div class="materia-item" data-id="<?= $m['asignacion_id'] ?>">
                        <div class="materia-info">
                            <strong><?= esc($m['materia']) ?></strong>
                            <span class="grupo"><?= esc($m['grupo']) ?></span>
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- PARCIALES DINÁMICOS -->
            <div id="cal-parciales" class="cal-parciales hidden">
                <h3 class="subtitulo">
                    <i class="fas fa-chart-line"></i> Parciales de la materia
                </h3>

                <table class="tabla-parciales">
                    <thead>
                        <tr>
                            <th>Parcial</th>
                            <th>Calificación</th>
                        </tr>
                    </thead>
                    <tbody id="parciales-body">
                        <!-- JS llenará esto -->
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <p class="placeholder">Aún no tienes materias asignadas en este ciclo.</p>
        <?php endif; ?>

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

<!-- =============================
     SCRIPTS Y CSS
============================== -->

<script>
    document.querySelectorAll(".tab-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("activo"));
            btn.classList.add("activo");

            let tab = btn.dataset.tab;
            document.querySelectorAll(".tab-content").forEach(c => c.style.display = "none");
            document.getElementById("tab-" + tab).style.display = "block";
        });
    });

    // Selección de materia para mostrar parciales
    document.querySelectorAll(".materia-item").forEach(item => {
        item.addEventListener("click", () => {
            const id = item.dataset.id;

            // TODO — Aquí harás el fetch real
            const parciales = {
                1: 88,
                2: 90,
                3: 85,
                final: 88.5
            };

            let html = `
            <tr><td>Parcial 1</td><td>${parciales[1]}</td></tr>
            <tr><td>Parcial 2</td><td>${parciales[2]}</td></tr>
            <tr><td>Parcial 3</td><td>${parciales[3]}</td></tr>
            <tr><td>Final</td><td>${parciales['final']}</td></tr>
        `;

            document.getElementById("parciales-body").innerHTML = html;
            document.getElementById("cal-parciales").classList.remove("hidden");
        });
    });
</script>

<link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">
<script src="<?= base_url('assets/js/alert.js') ?>"></script>

<?= $this->endSection() ?>