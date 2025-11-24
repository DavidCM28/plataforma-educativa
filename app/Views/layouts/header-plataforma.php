<link rel="stylesheet" href="<?= base_url('assets/css/header.css') ?>">
<header class="navbar-dark">
    <div class="navbar-left">
        <button id="sidebarToggle" class="btn-toggle">
            <span></span>
        </button>

        <span class="navbar-title">🎓 Plataforma Educativa</span>
    </div>

    <div class="navbar-right">

        <!-- 💬 Ícono de mensajes -->
        <div class="nav-icon" id="chatToggle">
            <i class="fas fa-comments"></i>
            <span class="badge" id="chatBadge">0</span>
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

        <!-- 📬 Mini bandeja -->
        <div class="chat-dropdown" id="chatDropdown">

            <div class="chat-list" id="chatList">
                <p class="placeholder">No hay mensajes recientes</p>
            </div>

            <div class="chat-footer">
                <a href="<?= base_url('api/chat/mensajes') ?>">Ver todos los mensajes →</a>
            </div>
        </div>

    </div>
</header>

<script>
    window.base_url = "<?= rtrim(site_url(), '/') ?>/";
    window.usuario_id = "<?= session('id') ?>";
</script>


<script src="http://localhost:3001/socket.io/socket.io.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="<?= base_url('assets/js/header.js') ?>"></script>