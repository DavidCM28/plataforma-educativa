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
            <?php
            $asignaciones = session('sidebar_materias') ?? [];
            // Estructura esperada: cada item => ['id', 'materia', 'grupo']
            ?>

            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-book"></i>
                    <span>Mis Materias</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>

                <ul class="submenu">
                    <?php if (!empty($asignaciones)): ?>
                        <?php foreach ($asignaciones as $a): ?>
                            <li>
                                <a href="<?= base_url('profesor/grupos/ver/' . $a['id']) ?>">
                                    <i class="fas fa-book-open"></i>
                                    <span><?= $a['materia'] ?> (<?= $a['grupo'] ?>)</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><span style="color:#999; padding:10px;">Sin materias</span></li>
                    <?php endif; ?>
                </ul>
            <li>
                <a href="<?= base_url('profesor/grupos/calificar') ?>">
                    <i class="fas fa-check"></i><span>Calificar</span>
                </a>
            </li>
            </li>
        <?php endif; ?>


        <!-- =========================
      🎓 ALUMNO
========================== -->
        <?php if ($rol === 'Alumno'): ?>
            <?php
            $materiasAlumno = session('sidebar_materias') ?? [];
            // Estructura esperada: ['asignacion_id', 'materia', 'grupo']
            ?>

            <li class="menu-group">
                <button class="menu-toggle">
                    <i class="fas fa-book-reader"></i>
                    <span>Mis Materias</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </button>

                <ul class="submenu">
                    <?php if (!empty($materiasAlumno)): ?>
                        <?php foreach ($materiasAlumno as $m): ?>
                            <li>
                                <a href="<?= base_url('alumno/materias/ver/' . $m['asignacion_id']) ?>">
                                    <i class="fas fa-book"></i>
                                    <span><?= $m['materia'] ?> (<?= $m['grupo'] ?>)</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><span style="color:#999; padding:10px;">Sin materias</span></li>
                    <?php endif; ?>
                </ul>
            <li>
                <a href="<?= base_url('alumno/calificaciones/historial') ?>">
                    <i class="fas fa-star"></i><span>Historial de Calificaciones</span>
                </a>
            </li>
            </li>
        <?php endif; ?>
    </ul>
</aside>