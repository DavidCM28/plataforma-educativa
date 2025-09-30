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
        <div class="nav-profile">
            <img src="<?= base_url('assets/img/user-default.jpg') ?>" alt="Perfil" id="profileAvatar">

            <!-- 🔽 Menú desplegable -->
            <div class="profile-menu" id="profileMenu">
                <div class="profile-info">
                    <img src="<?= base_url('assets/img/user-default.jpg') ?>" alt="Perfil">
                    <div>
                        <strong><?= esc(session('nombre')) ?></strong><br>
                        <small><?= esc(session('rol')) ?></small>
                    </div>
                </div>
                <hr>
                <ul>
                    <li><a href="#"><i class="fas fa-user"></i> Mi Perfil</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Configuración</a></li>
                    <li><a href="<?= base_url('logout') ?>"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>

    </div>
</header>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="<?= base_url('assets/js/header.js') ?>"></script>