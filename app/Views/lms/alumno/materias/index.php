<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/alumnos/materia_detalle.css') ?>">
<script src="<?= base_url('assets/js/alert.js') ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">
<script>
    window.base_url = "<?= rtrim(site_url(), '/') ?>/";
</script>
<script src="<?= base_url('assets/js/alumnos/tareas.js') ?>"></script>
<!-- 🔔 Contenedor global de alertas -->
<div id="alertContainer" class="alert-container"></div>

<!-- ⚠️ Modal de confirmación -->
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
<section class="materia-teams">
    <!-- ============================================================
         🧩 ENCABEZADO DEL EQUIPO
    ============================================================ -->
    <header class="materia-header">
        <div class="materia-info">
            <h2><?= esc($materia['nombre']) ?> <span class="grupo-tag"><?= esc($materia['grupo']) ?></span></h2>
            <p><i class="fas fa-chalkboard-teacher"></i> Profesor: <?= esc($materia['profesor'] ?? 'No asignado') ?></p>
        </div>
        <div class="materia-actions">
            <button class="btn-sec"><i class="fas fa-arrow-left"></i> Regresar</button>
        </div>
    </header>

    <!-- ============================================================
         🧭 NAVEGACIÓN TIPO TEAMS
    ============================================================ -->
    <nav class="tabs-teams">
        <button class="tab-btn active" data-tab="inicio"><i class="fas fa-comments"></i> Publicaciones</button>
        <button class="tab-btn" data-tab="tareas"><i class="fas fa-tasks"></i> Tareas</button>
        <button class="tab-btn" data-tab="proyectos"><i class="fas fa-rocket"></i> Proyectos</button>
        <button class="tab-btn" data-tab="examenes"><i class="fas fa-book"></i> Exámenes</button>
        <button class="tab-btn" data-tab="asistencias"><i class="fas fa-calendar-check"></i> Asistencias</button>
    </nav>

    <!-- ============================================================
         📰 SECCIÓN DE PUBLICACIONES
    ============================================================ -->
    <div class="tab-content active" id="inicio">
        <div class="feed-publicaciones">
            <div class="publicacion">
                <div class="pub-header">
                    <img src="https://ui-avatars.com/api/?background=ff9e64&color=000&name=<?= urlencode($materia['profesor']) ?>"
                        alt="Profesor">
                    <div>
                        <strong><?= esc($materia['profesor']) ?></strong>
                        <p class="fecha">22/10/2025 09:05 a.m.</p>
                    </div>
                </div>
                <div class="pub-body">
                    <h4>Proyecto 2do Parcial</h4>
                    <p>Entrega el 30 de octubre. Revisa los lineamientos en el documento adjunto.</p>
                    <button class="btn-main"><i class="fas fa-eye"></i> Ver proyecto</button>
                </div>
            </div>

            <div class="publicacion">
                <div class="pub-header">
                    <img src="https://ui-avatars.com/api/?background=ff9e64&color=000&name=<?= urlencode($materia['profesor']) ?>"
                        alt="Profesor">
                    <div>
                        <strong><?= esc($materia['profesor']) ?></strong>
                        <p class="fecha">30/10/2025 10:38 a.m.</p>
                    </div>
                </div>
                <div class="pub-body">
                    <h4>Actividad: Creación de API REST</h4>
                    <p>Fecha de entrega: 6 de noviembre. Desarrolla los endpoints solicitados y súbelos en formato ZIP.
                    </p>
                    <button class="btn-main"><i class="fas fa-eye"></i> Ver tarea</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         🧾 TAREAS
    ============================================================ -->
    <div class="tab-content" id="tareas">
        <div class="contenedor-carga">
            <i class="fas fa-spinner fa-spin"></i> Cargando tareas...
        </div>
    </div>

    <!-- ============================================================
         🚀 PROYECTOS
    ============================================================ -->
    <div class="tab-content" id="proyectos">
        <div class="contenedor-carga">
            <i class="fas fa-spinner fa-spin"></i> Cargando proyectos...
        </div>
    </div>

    <!-- ============================================================
         📘 EXÁMENES
    ============================================================ -->
    <div class="tab-content" id="examenes">
        <div class="contenedor-carga">
            <i class="fas fa-spinner fa-spin"></i> Cargando exámenes...
        </div>
    </div>

    <!-- ============================================================
         📅 ASISTENCIAS
    ============================================================ -->
    <div class="tab-content" id="asistencias">
        <div class="contenedor-carga">
            <i class="fas fa-spinner fa-spin"></i> Cargando historial de asistencias...
        </div>
    </div>
</section>

<script>
    document.querySelectorAll(".tab-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            // Quitar clases activas
            document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
            document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));

            // Activar tab seleccionada
            btn.classList.add("active");
            const tabId = btn.dataset.tab;
            const tab = document.getElementById(tabId);
            tab.classList.add("active");

            // Si es tareas, cargar la vista parcial
            if (tabId === "tareas" && !tab.dataset.loaded) {
                const asignacionId = <?= esc($materia['id']) ?>;

                tab.innerHTML = `<div class='contenedor-carga'><i class='fas fa-spinner fa-spin'></i> Cargando tareas...</div>`;
                fetch(`<?= base_url('alumno/materias/tareas/') ?>${asignacionId}`)
                    .then(res => res.text())
                    .then(html => {
                        tab.innerHTML = html;
                        tab.dataset.loaded = true;

                        // ✅ Inicializa el módulo JS una vez cargada la vista
                        if (window.TareasAlumnoUI) {
                            window.TareasAlumnoUI.inicializar(asignacionId);
                        }
                    })
                    .catch(err => {
                        tab.innerHTML = `<p class='error'>Error al cargar las tareas.</p>`;
                        console.error(err);
                    });
            }

        });
    });

    // Botón de regreso
    document.querySelector(".btn-sec")?.addEventListener("click", () => history.back());
</script>

<?= $this->endSection() ?>