<aside class="sidebar-dark" id="sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="<?= base_url('dashboard') ?>">
                <i class="fas fa-home"></i><span>Inicio</span>
            </a>
        </li>

        <?php $rol = session('rol'); ?>

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
                    <li><a href="<?= base_url('admin/planes') ?>"><i class="fas fa-scroll"></i><span>Planes de
                                Estudio</span></a></li>
                    <li><a href="<?= base_url('admin/materias') ?>"><i class="fas fa-book"></i><span>Materias</span></a>
                    </li>
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
    </ul>
</aside>