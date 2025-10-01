<header class="navbar-dark">
    <div class="navbar-left">
        <button id="sidebarToggle" class="btn-toggle">
            <span></span>
        </button>

        <span class="navbar-title">🎓 Plataforma Educativa</span>
    </div>

    <div class="navbar-right">
        <div class="nav-icon">
            <i class="fas fa-bell"></i>
            <span class="badge">3</span>
        </div>
        <div class="nav-icon">
            <i class="fas fa-envelope"></i>
            <span class="badge">5</span>
        </div>

        <!-- 👤 Avatar del usuario -->
        <div class="nav-profile">
            <?php
            $fotoPerfil = session('foto') && !empty(session('foto'))
                ? session('foto')
                : base_url('assets/img/user-default.jpg');
            ?>
            <img src="<?= esc($fotoPerfil) ?>" alt="Perfil" id="profileAvatar" class="avatar-header">

            <!-- 🔽 Menú desplegable -->
            <div class="profile-menu" id="profileMenu">
                <div class="profile-info">
                    <img src="<?= esc($fotoPerfil) ?>" alt="Perfil" class="avatar-menu">
                    <div>
                        <strong><?= esc(session('nombre')) ?></strong><br>
                        <small><?= esc(session('rol')) ?></small>
                    </div>
                </div>
                <hr>
                <ul>
                    <li><a href="<?= base_url('perfil') ?>"><i class="fas fa-user"></i> Mi Perfil</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Configuración</a></li>
                    <li><a href="<?= base_url('logout') ?>"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="<?= base_url('assets/js/header.js') ?>"></script>