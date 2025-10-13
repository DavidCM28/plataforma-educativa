<aside class="sidebar-dark" id="sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="<?= base_url('dashboard') ?>">
                <i class="fas fa-home"></i><span>Inicio</span>
            </a>
        </li>

        <?php $rol = session('rol'); ?>

        <!-- =========================
             👑 SUPERUSUARIO
        ========================== -->
        <?php if ($rol === 'Superusuario'): ?>
            <!-- 🧱 GESTIÓN DEL SISTEMA -->
            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-tools"></i>
                    <span>Gestión del Sistema</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= base_url('admin/usuarios') ?>"><i
                                class="fas fa-users-cog"></i><span>Usuarios</span></a></li>
                    <li><a href="<?= base_url('admin/roles') ?>"><i class="fas fa-user-shield"></i><span>Roles y
                                Permisos</span></a></li>
                    <li><a href="<?= base_url('usuarios-detalles') ?>"><i class="fas fa-id-card"></i><span>Datos
                                Personales</span></a></li>
                </ul>
            </li>

            <!-- 🎓 ESTRUCTURA ACADÉMICA -->
            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-university"></i>
                    <span>Estructura Académica</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= base_url('admin/carreras') ?>"><i
                                class="fas fa-building-columns"></i><span>Carreras</span></a></li>
                    <li><a href="<?= base_url('admin/materias') ?>"><i class="fas fa-book"></i><span>Materias</span></a>
                    </li>
                    <li><a href="<?= base_url('admin/planes-estudio') ?>"><i class="fas fa-scroll"></i><span>Planes de
                                Estudio</span></a></li>
                    <li><a href="<?= base_url('admin/grupos') ?>"><i class="fas fa-layer-group"></i><span>Grupos</span></a>
                    </li>
                    <li><a href="<?= base_url('admin/asignaciones') ?>"><i
                                class="fas fa-clipboard-list"></i><span>Asignaciones</span></a></li>
                </ul>
            </li>

            <!-- ⚙️ CONFIGURACIÓN ACADÉMICA -->
            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-cogs"></i>
                    <span>Configuración Académica</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= base_url('admin/ciclos') ?>"><i class="fas fa-calendar-alt"></i><span>Ciclos
                                Académicos</span></a></li>
                    <li><a href="<?= base_url('admin/criterios') ?>"><i class="fas fa-percent"></i><span>Criterios de
                                Evaluación</span></a></li>
                </ul>
            </li>

            <!-- 📊 MONITOREO -->
            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-chart-line"></i>
                    <span>Monitoreo y Reportes</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= base_url('admin/reportes') ?>"><i class="fas fa-chart-bar"></i><span>Reportes
                                Generales</span></a></li>
                </ul>
            </li>
        <?php endif; ?>


        <!-- =========================
             🏫 ESCOLARES
        ========================== -->
        <?php if ($rol === 'Escolares'): ?>
            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-user-graduate"></i>
                    <span>Gestión Académica</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= base_url('escolares/alumnos') ?>"><i class="fas fa-users"></i><span>Alumnos</span></a>
                    </li>
                    <li><a href="<?= base_url('escolares/profesores') ?>"><i
                                class="fas fa-chalkboard-teacher"></i><span>Profesores</span></a></li>
                    <li><a href="<?= base_url('escolares/inscripciones') ?>"><i
                                class="fas fa-clipboard-check"></i><span>Inscripciones</span></a></li>
                    <li><a href="<?= base_url('escolares/calificaciones') ?>"><i
                                class="fas fa-star"></i><span>Calificaciones</span></a></li>
                </ul>
            </li>

            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-folder-open"></i>
                    <span>Documentación</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= base_url('escolares/reportes') ?>"><i
                                class="fas fa-file-alt"></i><span>Reportes</span></a></li>
                    <li><a href="<?= base_url('escolares/constancias') ?>"><i
                                class="fas fa-file-signature"></i><span>Constancias</span></a></li>
                </ul>
            </li>
        <?php endif; ?>


        <!-- =========================
             👨‍🏫 PROFESOR
        ========================== -->
        <?php if ($rol === 'Profesor'): ?>
            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-book"></i>
                    <span>Mis Materias</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= base_url('profesor/materias') ?>"><i
                                class="fas fa-book-open"></i><span>Listado</span></a></li>
                    <li><a href="<?= base_url('profesor/grupos') ?>"><i class="fas fa-users"></i><span>Grupos
                                Asignados</span></a></li>
                </ul>
            </li>

            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-edit"></i>
                    <span>Evaluación</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= base_url('profesor/calificar') ?>"><i
                                class="fas fa-check"></i><span>Calificar</span></a></li>
                    <li><a href="<?= base_url('profesor/criterios') ?>"><i
                                class="fas fa-percent"></i><span>Criterios</span></a></li>
                </ul>
            </li>

            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reportes</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= base_url('profesor/reportes') ?>"><i class="fas fa-chart-line"></i><span>Mi
                                Rendimiento</span></a></li>
                </ul>
            </li>
        <?php endif; ?>


        <!-- =========================
             🎓 ALUMNO
        ========================== -->
        <?php if ($rol === 'Alumno'): ?>
            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-book-reader"></i>
                    <span>Mis Materias</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= base_url('alumno/materias') ?>"><i class="fas fa-book"></i><span>Ver
                                Materias</span></a></li>
                    <li><a href="<?= base_url('alumno/calificaciones') ?>"><i
                                class="fas fa-star"></i><span>Calificaciones</span></a></li>
                </ul>
            </li>

            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Asistencias</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= base_url('alumno/asistencias') ?>"><i
                                class="fas fa-user-check"></i><span>Consultar</span></a></li>
                </ul>
            </li>

            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-bullhorn"></i>
                    <span>Comunicados</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= base_url('alumno/anuncios') ?>"><i class="fas fa-bell"></i><span>Anuncios</span></a>
                    </li>
                    <li><a href="<?= base_url('alumno/soporte') ?>"><i class="fas fa-headset"></i><span>Soporte</span></a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>
    </ul>
</aside>