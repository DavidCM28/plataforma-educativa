<!-- app/Views/layouts/sidebar-plataforma.php -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Menú</h3>
    </div>
    <ul class="sidebar-menu">
        <li><a href="#"><i class="fas fa-home"></i> <span>Inicio</span></a></li>

        <?php if (session('rol') === 'admin'): ?>
            <li><a href="#"><i class="fas fa-users"></i> <span>Usuarios</span></a></li>
            <li><a href="#"><i class="fas fa-cogs"></i> <span>Configuración</span></a></li>
        <?php elseif (session('rol') === 'profesor'): ?>
            <li><a href="#"><i class="fas fa-book"></i> <span>Mis Cursos</span></a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> <span>Actividades</span></a></li>
        <?php elseif (session('rol') === 'alumno'): ?>
            <li><a href="#"><i class="fas fa-graduation-cap"></i> <span>Mis Clases</span></a></li>
            <li><a href="#"><i class="fas fa-file-alt"></i> <span>Calificaciones</span></a></li>
        <?php endif; ?>

        <li><a href="#"><i class="fas fa-sign-out-alt"></i> <span>Cerrar sesión</span></a></li>
    </ul>
</aside>