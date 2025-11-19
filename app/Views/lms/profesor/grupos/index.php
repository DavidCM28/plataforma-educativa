<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>
<script>
    window.base_url = "<?= rtrim(site_url(), '/') ?>/";

</script>


<script src="<?= base_url('assets/js/alert.js') ?>"></script>
<script src="<?= base_url('assets/js/profesores/asistencias.js') ?>"></script>
<script src="<?= base_url('assets/js/profesores/publicaciones.js') ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/profesores/grupos.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/profesores/tareas.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/profesores/proyectos.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/profesores/examenes.css') ?>">
<script src="<?= base_url('assets/js/profesores/tareas.js') ?>"></script>
<script src="<?= base_url('assets/js/profesores/proyectos.js') ?>"></script>
<script src="<?= base_url('assets/js/profesores/examenes.js') ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/css/profesores/tareas_entregas.css') ?>">
<script src="<?= base_url('assets/js/profesores/tareas_entregas.js') ?>"></script>
<script src="<?= base_url('assets/js/profesores/proyectos_entregas.js') ?>"></script>


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


<section class="grupo-teams">
    <div class="grupo-header">
        <div class="grupo-info">
            <h2><?= esc($grupo['materia']) ?> <span class="grupo-tag"><?= esc($grupo['grupo']) ?></span></h2>
            <p class="grupo-sub"><i class="fas fa-chalkboard-teacher"></i> Profesor:
                <?= esc(($grupo['profesor_nombre'] ?? '') !== ''
                    ? $grupo['profesor_nombre'] . ' ' . $grupo['profesor_ap'] . ' ' . $grupo['profesor_am']
                    : 'Sin asignar') ?>
            </p>
        </div>
    </div>

    <!-- ============================================================
📁 NAVEGACIÓN DEL GRUPO (Tabs principales tipo Teams)
============================================================ -->
    <nav class="tabs-teams">
        <div class="tabs-main">
            <button class="tab-btn active" data-tab="inicio" title="Publicaciones">
                <i class="fas fa-comments"></i><span> Publicaciones</span>
            </button>

            <button class="tab-btn" data-tab="alumnos" title="Lista de alumnos">
                <i class="fas fa-users"></i> <span> Alumnos</span>
            </button>

            <button class="tab-btn" data-tab="asistencias" title="Asistencias">
                <i class="fas fa-calendar-check"></i> <span> Asistencias</span>
            </button>

            <button class="tab-btn" data-tab="tareas" title="Tareas">
                <i class="fas fa-tasks"></i> <span> Tareas</span>
            </button>

            <button class="tab-btn" data-tab="proyectos" title="Proyectos">
                <i class="fas fa-rocket"></i> <span> Proyectos</span>
            </button>

            <button class="tab-btn" data-tab="examenes" title="Exámenes">
                <i class="fas fa-book"></i> <span> Exámenes</span>
            </button>

            <!--<button class="tab-btn" data-tab="calificaciones" title="Calificaciones">
                <i class="fas fa-chart-line"></i> <span> Calificaciones</span>
            </button>-->
        </div>
    </nav>



    <!-- 📰 INICIO (Publicaciones tipo Teams) -->
    <div class="tab-content active" id="inicio">
        <div class="publicar-card">
            <form id="formPublicacion" enctype="multipart/form-data">
                <textarea name="contenido" id="contenido"
                    placeholder="Escribe un aviso o mensaje para el grupo..."></textarea>
                <div class="acciones-publicar">
                    <label for="archivos" class="btn-archivo"><i class="fas fa-paperclip"></i> Adjuntar</label>
                    <input type="file" name="archivos[]" id="archivos" multiple hidden>
                    <button type="submit" class="btn-main"><i class="fas fa-paper-plane"></i> Publicar</button>
                </div>
            </form>
        </div>

        <div id="feedPublicaciones" class="feed-publicaciones" data-asignacion="<?= $asignacionId ?>">
            <p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando publicaciones...</p>
        </div>

    </div>

    <!-- LISTA DE ALUMNOS -->
    <div class="tab-content" id="alumnos">

        <!-- 🔹 Barra de filtros -->
        <div class="filtros-alumnos">
            <div class="buscador">
                <i class="fas fa-search"></i>
                <input type="text" id="buscarAlumno" placeholder="Buscar alumno o matrícula...">
            </div>

            <div class="orden">
                <label for="ordenarPor"><i class="fas fa-sort"></i> Ordenar por:</label>
                <select id="ordenarPor">
                    <option value="apellido">Apellidos</option>
                    <option value="matricula">Matrícula</option>
                </select>
            </div>

            <button id="btnResetFiltros" class="btn-reset">
                <i class="fas fa-sync-alt"></i> Reiniciar
            </button>
        </div>

        <!-- 🧑‍🎓 Tabla de alumnos -->
        <table class="alumnos-lista" id="tablaAlumnos">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nombre</th>
                    <th>Matrícula</th>
                    <th>Carrera</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($alumnos)): ?>
                    <?php foreach ($alumnos as $a): ?>
                        <tr>
                            <td>
                                <?php if (!empty($a['foto'])): ?>
                                    <?php
                                    $esCloud = str_contains($a['foto'], 'cloudinary.com') || str_contains($a['foto'], 'http');
                                    $rutaFoto = $esCloud ? $a['foto'] : base_url('uploads/usuarios/' . esc($a['foto']));
                                    ?>
                                    <img src="<?= esc($rutaFoto) ?>" class="foto-alumno">
                                <?php else: ?>
                                    <?php $iniciales = strtoupper(substr($a['nombre'], 0, 1) . substr($a['apellido_paterno'], 0, 1)); ?>
                                    <div class="avatar-iniciales"><?= $iniciales ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($a['apellido_paterno'] . ' ' . $a['apellido_materno'] . ' ' . $a['nombre']) ?></td>
                            <td><?= esc($a['matricula']) ?></td>
                            <td><?= esc($a['carrera'] ?? '-') ?></td>
                            <td>
                                <button class="btn-icon ver-detalle" data-id="<?= $a['alumno_id'] ?>" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="sin-alumnos">No hay alumnos registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ============================================================
     📅 ASISTENCIAS
============================================================ -->
    <div class="tab-content" id="asistencias">
        <div id="contenedorAsistencias" class="asistencias-cargando">
            <p><i class="fas fa-spinner fa-spin"></i> Cargando asistencias...</p>
        </div>
    </div>

    <!-- ============================================================
📚 TAREAS (se carga desde tareas.php)
============================================================ -->
    <div class="tab-content" id="tareas">
        <div id="contenedorTareas" class="tareas-cargando">
            <p><i class="fas fa-spinner fa-spin"></i> Cargando tareas...</p>
        </div>
    </div>

    <!-- ============================================================
📘 EXÁMENES
============================================================ -->
    <div class="tab-content" id="examenes">

    </div>

    <!-- ============================================================
🚀 PROYECTOS
============================================================ -->
    <div class="tab-content" id="proyectos">

    </div>

    <!-- ============================================================
📊 CALIFICACIONES
============================================================ 
    <div class="tab-content" id="calificaciones">
        <p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Módulo de calificaciones en desarrollo...</p>
    </div> -->


    <!-- 🧩 Modal Detalles Alumno (Diseño Moderno Tipo Perfil) -->
    <div id="modalAlumno" class="modal hidden">
        <div class="modal-card">
            <span class="close">&times;</span>

            <div class="modal-header">
                <div class="foto-alumno-modal">
                    <img id="fotoAlumnoModal" src="" alt="Foto del alumno">
                </div>
                <div class="info-basica">
                    <h2 id="nombreAlumnoModal">Alumno</h2>
                    <p id="matriculaAlumnoModal" class="texto-muted"></p>
                    <p id="correoAlumnoModal" class="texto-muted"></p>
                </div>
            </div>

            <div class="modal-seccion">
                <h3>📘 Datos Académicos</h3>
                <div class="campo"><label>Carrera:</label><span id="carreraAlumnoModal">-</span></div>
                <div class="campo"><label>Grupo:</label><span id="grupoAlumnoModal">-</span></div>
                <div class="campo"><label>Ciclo:</label><span id="cicloAlumnoModal">-</span></div>
                <div class="campo"><label>Turno:</label><span id="turnoAlumnoModal">-</span></div>
            </div>

            <div class="modal-seccion">
                <h3>📋 Datos Personales</h3>
                <div class="campo"><label>CURP:</label><span id="curpAlumnoModal">-</span></div>
                <div class="campo"><label>Fecha Nacimiento:</label><span id="fechaAlumnoModal">-</span></div>
                <div class="campo"><label>Teléfono:</label><span id="telefonoAlumnoModal">-</span></div>
            </div>

            <div class="modal-footer">
                <button class="btn-main cerrar-modal">Cerrar</button>
            </div>
        </div>
    </div>

</section>

<script>
    document.querySelectorAll(".tab-btn").forEach(btn => {
        btn.addEventListener("click", async () => {
            document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
            document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));

            btn.classList.add("active");
            const target = document.getElementById(btn.dataset.tab);
            if (!target) return;

            target.classList.add("active");

            // 🚀 Si es la pestaña de asistencias, carga el contenido dinámicamente
            if (btn.dataset.tab === "asistencias") {
                const contenedor = document.getElementById("contenedorAsistencias");
                contenedor.innerHTML = `<p><i class="fas fa-spinner fa-spin"></i> Cargando asistencias...</p>`;

                try {
                    const res = await fetch("<?= base_url('profesor/grupos/asistencias/' . $asignacionId) ?>");
                    const html = await res.text();
                    contenedor.innerHTML = html;

                    // ✅ Inicializar lógica desde el archivo externo
                    window.AsistenciasUI?.inicializar(<?= $asignacionId ?>);
                } catch (error) {
                    contenedor.innerHTML = `<p class="error">❌ Error al cargar asistencias: ${error.message}</p>`;
                }
            }

        });
    });


    // 🔹 Aplicar diseño tipo perfil en el modal existente
    document.querySelectorAll(".ver-detalle").forEach(btn => {
        btn.addEventListener("click", async () => {
            const id = btn.dataset.id;
            const modal = document.getElementById("modalAlumno");

            try {
                const res = await fetch("<?= base_url('profesor/grupos/detalles-alumno/') ?>" + id);
                const data = await res.json();

                if (data.error) {
                    alert("⚠️ Error del servidor: " + data.error);
                    console.error(data.error);
                    return;
                }

                // 🖼️ Foto
                const foto = data.usuario?.foto || "";
                const esCloud = foto.includes("cloudinary.com") || foto.includes("http");
                document.getElementById("fotoAlumnoModal").src =
                    foto
                        ? (esCloud ? foto : "<?= base_url('uploads/usuarios/') ?>" + foto)
                        : "https://ui-avatars.com/api/?background=ff9e64&color=000&name=" + encodeURIComponent(data.usuario?.nombre ?? "Alumno");

                // 🧾 Datos básicos
                document.getElementById("nombreAlumnoModal").textContent =
                    `${data.usuario?.nombre ?? ""} ${data.usuario?.apellido_paterno ?? ""}`;
                document.getElementById("matriculaAlumnoModal").textContent = `Matrícula: ${data.usuario?.matricula ?? "-"}`;
                document.getElementById("correoAlumnoModal").textContent = data.usuario?.email ?? "-";

                // 🎓 Académicos
                document.getElementById("carreraAlumnoModal").textContent = data.academico?.carrera ?? "-";
                document.getElementById("grupoAlumnoModal").textContent = data.academico?.grupo ?? "-";
                document.getElementById("cicloAlumnoModal").textContent = data.academico?.semestre ?? "-";
                document.getElementById("turnoAlumnoModal").textContent = data.academico?.turno ?? "-";

                // 📋 Personales
                document.getElementById("curpAlumnoModal").textContent = data.detalles?.curp ?? "-";
                document.getElementById("fechaAlumnoModal").textContent = data.detalles?.fecha_nacimiento ?? "-";
                document.getElementById("telefonoAlumnoModal").textContent = data.detalles?.telefono ?? "-";

                modal.classList.remove("hidden");
            } catch (err) {
                alert("❌ Error inesperado: " + err.message);
                console.error(err);
            }
        });
    });

    // ❌ Cerrar modal
    document.addEventListener("click", e => {
        if (e.target.classList.contains("close") || e.target.classList.contains("cerrar-modal")) {
            document.getElementById("modalAlumno").classList.add("hidden");
        }
    });

    /* ============================================================
       🔍 FILTRADO Y ORDENADO DE ALUMNOS EN VIVO
    ============================================================ */
    const inputBuscar = document.getElementById("buscarAlumno");
    const selectOrden = document.getElementById("ordenarPor");
    const btnReset = document.getElementById("btnResetFiltros");
    const tabla = document.getElementById("tablaAlumnos");
    const filas = Array.from(tabla.querySelectorAll("tbody tr"));

    function filtrarYOrdenar() {
        const texto = inputBuscar.value.toLowerCase().trim();
        const criterio = selectOrden.value;

        // 🔍 Filtrar por texto (nombre completo o matrícula)
        let visibles = filas.filter(fila => {
            const nombreCompleto = fila.cells[1].textContent.toLowerCase(); // Apellidos + nombre
            const matricula = fila.cells[2].textContent.toLowerCase();
            const carrera = fila.cells[3].textContent.toLowerCase();
            return (
                nombreCompleto.includes(texto) ||
                matricula.includes(texto) ||
                carrera.includes(texto)
            );
        });

        // 🔽 Ordenar según el criterio
        visibles.sort((a, b) => {
            if (criterio === "apellido") {
                const aText = a.cells[1].textContent.toLowerCase();
                const bText = b.cells[1].textContent.toLowerCase();
                return aText.localeCompare(bText, "es", { sensitivity: "base" });
            } else {
                const aMat = a.cells[2].textContent.toLowerCase();
                const bMat = b.cells[2].textContent.toLowerCase();
                return aMat.localeCompare(bMat, "es", { sensitivity: "base" });
            }
        });

        // 🧩 Limpiar y volver a renderizar
        const tbody = tabla.querySelector("tbody");
        tbody.innerHTML = "";
        visibles.forEach(f => tbody.appendChild(f));
    }

    function resaltarCoincidencias(fila, texto) {
        if (!texto) return;
        const regex = new RegExp(`(${texto})`, "gi");
        const celda = fila.cells[1];
        celda.innerHTML = celda.textContent.replace(regex, '<mark>$1</mark>');
    }

    inputBuscar.addEventListener("input", () => {
        filtrarYOrdenar();
        const texto = inputBuscar.value.toLowerCase().trim();
        filas.forEach(f => resaltarCoincidencias(f, texto));
    });


    // ⌨️ Evento al escribir
    inputBuscar.addEventListener("input", filtrarYOrdenar);
    // 🔁 Evento al cambiar el tipo de orden
    selectOrden.addEventListener("change", filtrarYOrdenar);
    // 🔄 Reiniciar filtros
    btnReset.addEventListener("click", () => {
        inputBuscar.value = "";
        selectOrden.value = "apellido";
        filas.forEach(f => tabla.querySelector("tbody").appendChild(f));
    });

</script>
<script>
    // ============================================================
    // 🎯 ACTIVAR TAB SEGÚN PARÁMETRO ?tab=
    // ============================================================
    document.addEventListener("DOMContentLoaded", () => {
        const params = new URLSearchParams(window.location.search);
        const tabParam = params.get("tab");
        if (!tabParam) return;

        const targetBtn = document.querySelector(`.tab-btn[data-tab="${tabParam}"]`);
        const targetTab = document.getElementById(tabParam);

        if (targetBtn && targetTab) {
            document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
            document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));

            targetBtn.classList.add("active");
            targetTab.classList.add("active");

            // ⭐ PEGA ESTA PARTE ⭐
            setTimeout(() => {
                targetBtn.click();  // Dispara el evento original que carga el módulo
            }, 80);
        }
    });


</script>

<script>// ============================================================
    // 📘 Cargar dinámicamente el módulo de TAREAS
    // ============================================================
    document.querySelectorAll(".tab-btn").forEach(btn => {
        btn.addEventListener("click", async () => {
            document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
            document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));

            btn.classList.add("active");
            const target = document.getElementById(btn.dataset.tab);
            if (!target) return;

            target.classList.add("active");

            // 🚀 Si es la pestaña de asistencias
            if (btn.dataset.tab === "asistencias") {
                const contenedor = document.getElementById("contenedorAsistencias");
                contenedor.innerHTML = `<p><i class="fas fa-spinner fa-spin"></i> Cargando asistencias...</p>`;
                try {
                    const res = await fetch("<?= base_url('profesor/grupos/asistencias/' . $asignacionId) ?>");
                    const html = await res.text();
                    contenedor.innerHTML = html;
                    window.AsistenciasUI?.inicializar(<?= $asignacionId ?>);
                } catch (error) {
                    contenedor.innerHTML = `<p class="error">❌ Error al cargar asistencias: ${error.message}</p>`;
                }
            }

            // 🚀 Si es la pestaña de tareas
            if (btn.dataset.tab === "tareas") {
                const contenedor = document.getElementById("contenedorTareas");
                contenedor.innerHTML = `<p><i class="fas fa-spinner fa-spin"></i> Cargando tareas...</p>`;
                try {
                    const res = await fetch("<?= base_url('profesor/grupos/tareas/' . $asignacionId) ?>");
                    const html = await res.text();
                    contenedor.innerHTML = html;
                    window.TareasUI?.inicializar(<?= $asignacionId ?>);
                } catch (error) {
                    contenedor.innerHTML = `<p class="error">❌ Error al cargar tareas: ${error.message}</p>`;
                }
            }

            // 🚀 Si es la pestaña de proyectos
            if (btn.dataset.tab === "proyectos") {
                const contenedor = document.getElementById("proyectos");
                contenedor.innerHTML = `<p><i class="fas fa-spinner fa-spin"></i> Cargando proyectos...</p>`;
                try {
                    const res = await fetch("<?= base_url('profesor/grupos/proyectos/' . $asignacionId) ?>");
                    const html = await res.text();
                    contenedor.innerHTML = html;
                    window.ProyectosUI?.inicializar(<?= $asignacionId ?>);
                } catch (error) {
                    contenedor.innerHTML = `<p class="error">❌ Error al cargar proyectos: ${error.message}</p>`;
                }
            }

            // 🚀 Si es la pestaña de exámenes
            if (btn.dataset.tab === "examenes") {
                const contenedor = document.getElementById("examenes");
                contenedor.innerHTML = `<p><i class="fas fa-spinner fa-spin"></i> Cargando exámenes...</p>`;
                try {
                    const res = await fetch("<?= base_url('profesor/grupos/examenes/' . $asignacionId) ?>");
                    const html = await res.text();
                    contenedor.innerHTML = html;

                    // ⚡ Espera un breve instante para asegurar que el DOM del examen está cargado
                    setTimeout(() => {
                        if (window.ExamenesUI) {
                            window.ExamenesUI.inicializar(<?= $asignacionId ?>);
                        } else {
                            console.error("⚠️ ExamenesUI no está definido, revisa si el script se está cargando.");
                        }
                    }, 100);
                } catch (error) {
                    contenedor.innerHTML = `<p class="error">❌ Error al cargar exámenes: ${error.message}</p>`;
                }
            }

            // Pestaña Calificaciones
            /*if (btn.dataset.tab === "calificaciones") {
                const contenedor = document.getElementById("calificaciones");
                contenedor.innerHTML = `<p><i class="fas fa-spinner fa-spin"></i> Cargando calificaciones...</p>`;
                try {
                    const res = await fetch("<?= base_url('profesor/grupos/calificaciones/' . $asignacionId) ?>");
            const html = await res.text();
            contenedor.innerHTML = html;

            // ⚡ Espera un breve instante para asegurar que el DOM del examen está cargado
            setTimeout(() => {
                if (window.CalificacionesUI) {
                    window.CalificacionesUI.inicializar(<?= $asignacionId ?>);
                } else {
                    console.error("⚠️CalificacionesUI no está definido, revisa si el script se está cargando.");
                }
            }, 100);
        } catch (error) {
            contenedor.innerHTML = `<p class="error">❌ Error al cargar calificaciones: ${error.message}</p>`;
        }
    }*/
        });
    });
</script>
<?= $this->endSection() ?>