<aside class="sidebar-dark" id="sidebar">

    <ul class="sidebar-menu">
        <li><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i><span>Inicio</span></a></li>

        <?php $rol = session('rol'); ?>
        <?php if ($rol === 'Superusuario'): ?>
            <li><a href="<?= base_url('admin/usuarios') ?>"><i class="fas fa-users-cog"></i><span>Usuarios</span></a></li>
            <li><a href="<?= base_url('usuarios-detalles') ?>"><i class="fas fa-id-card"></i><span>Datos
                        Personales</span></a></li>
            <li><a href="#"><i class="fas fa-book"></i><span>Materias</span></a></li>
            <li><a href="#"><i class="fas fa-university"></i><span>Carreras</span></a></li>
            <li><a href="#"><i class="fas fa-layer-group"></i><span>Grupos</span></a></li>
        <?php elseif ($rol === 'Escolares'): ?>
            <li><a href="#"><i class="fas fa-user-graduate"></i><span>Alumnos</span></a></li>
            <li><a href="#"><i class="fas fa-clipboard-list"></i><span>Asignaciones</span></a></li>
        <?php elseif ($rol === 'Profesor'): ?>
            <li><a href="#"><i class="fas fa-book-open"></i><span>Mis Grupos</span></a></li>
            <li><a href="#"><i class="fas fa-tasks"></i><span>Tareas</span></a></li>
        <?php elseif ($rol === 'Alumno'): ?>
            <li><a href="#"><i class="fas fa-graduation-cap"></i><span>Mis Materias</span></a></li>
            <li><a href="#"><i class="fas fa-file-alt"></i><span>Calificaciones</span></a></li>
        <?php endif; ?>
    </ul>
</aside>