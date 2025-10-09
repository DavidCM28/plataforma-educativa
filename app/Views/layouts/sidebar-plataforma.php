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
    <aside class="sidebar-dark">
    <nav class="sidebar-menu">
        <!-- Menú principal -->
        <li>
            <a href="<?= base_url('dashboard/alumno') ?>">
                <i class="fas fa-home"></i>
                <span>Inicio</span>
            </a>
        </li>
        
        <div class="menu-section">ACADÉMICO</div>
        
        <li>
            <a href="#">
                <i class="fas fa-book"></i>
                <span>Mis Materias</span>
            </a>
        </li>
        
        <li>
            <a href="#">
                <i class="fas fa-tasks"></i>
                <span>Tareas</span>
            </a>
        </li>
        
        <li>
            <a href="#">
                <i class="fas fa-chart-line"></i>
                <span>Calificaciones</span>
            </a>
        </li>
        
        <li>
            <a href="#">
                <i class="fas fa-calendar-alt"></i>
                <span>Horario</span>
            </a>
        </li>
        
        <div class="menu-section">RECURSOS</div>
        
        <li>
            <a href="#">
                <i class="fas fa-folder"></i>
                <span>Materiales</span>
            </a>
        </li>
        
        <li>
            <a href="#">
                <i class="fas fa-users"></i>
                <span>Compañeros</span>
            </a>
        </li>
        
        <li>
            <a href="#">
                <i class="fas fa-comments"></i>
                <span>Foros</span>
            </a>
        </li>
        
        <div class="menu-section">CUENTA</div>
        
        <li>
            <a href="#">
                <i class="fas fa-user-cog"></i>
                <span>Perfil</span>
            </a>
        </li>
        
        <li>
            <a href="<?= base_url('auth/logout') ?>" class="logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </li>
    </nav>
</aside>a
</aside>