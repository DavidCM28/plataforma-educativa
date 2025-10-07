<?= $this->include('layouts/header') ?>

<body>
    <link rel="stylesheet" href="<?= base_url('assets/css/login.css') ?>">
    <div class="soft-background">
        <div class="floating-shapes">
            <div class="soft-blob blob-1"></div>
            <div class="soft-blob blob-2"></div>
            <div class="soft-blob blob-3"></div>
            <div class="soft-blob blob-4"></div>
        </div>
    </div>

    <div class="login-container">
        <div class="soft-card">
            <div class="comfort-header">
                <div class="gentle-logo">
                    <div class="logo-circle">
                        <div class="comfort-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                                <path d="M16 2C8.3 2 2 8.3 2 16s6.3 14 14 14 14-6.3 14-14S23.7 2 16 2z" fill="none"
                                    stroke="currentColor" stroke-width="1.5" />
                                <path d="M12 16a4 4 0 108 0" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" />
                                <circle cx="12" cy="12" r="1.5" fill="currentColor" />
                                <circle cx="20" cy="12" r="1.5" fill="currentColor" />
                            </svg>
                        </div>
                        <div class="gentle-glow"></div>
                    </div>
                </div>
                <h1 class="comfort-title">¡Bienvenido, Usuario!</h1>
                <p class="gentle-subtitle">Accede a la plataforma</p>
            </div>
            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger" style="margin-bottom: 15px;">
                    <?= session('error') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->has('mensaje')): ?>
                <div class="alert alert-success" style="margin-bottom: 15px;">
                    <?= session('mensaje') ?>
                </div>
            <?php endif; ?>


            <form class="comfort-form" id="loginForm" method="post" action="<?= base_url('/auth/login') ?>" novalidate>
                <div class="soft-field">
                    <div class="field-container">
                        <input type="text" id="usuario" name="usuario" required autocomplete="off">
                        <label for="matricula">Matrícula o No. de Empleado</label>
                        <div class="field-accent"></div>
                    </div>
                    <span class="gentle-error" id="matriculaError"></span>
                </div>

                <div class="soft-field">
                    <div class="field-container">
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <label for="password">Contraseña</label>

                        <button type="button" class="gentle-toggle" id="passwordToggle"
                            aria-label="Mostrar/ocultar contraseña">
                            <div class="toggle-icon">
                                <!-- Ojo abierto -->
                                <svg class="eye-open" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M10 3C5 3 1.5 7 1 10c.5 3 4 7 9 7s8.5-4 9-7c-.5-3-4-7-9-7z"
                                        stroke="currentColor" stroke-width="1.5" fill="none" />
                                    <circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.5"
                                        fill="none" />
                                </svg>

                                <!-- Ojo cerrado -->
                                <svg class="eye-closed" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path
                                        d="M2 2l16 16M6 6c1-1 2.5-2 4-2 5 0 8.5 4 9 7-.2.9-1 2-1.5 2.5M6 6c-.7.7-1.5 1.6-2 2.5.5 1 2.5 3 5 3 1 0 2-.4 2.5-1"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                        </button>
                    </div>
                    <span class="gentle-error" id="passwordError"></span>
                </div>

                <button type="submit" class="comfort-button">
                    <span class="button-text">Acceder</span>
                </button>
            </form>

        </div>
    </div>

    <!-- ✅ Inyectamos el baseUrl dinámico -->
    <script>const baseUrl = "<?= base_url() ?>";</script>
    <script src="<?= base_url('assets/js/login.js') ?>"></script>

    <?= $this->include('layouts/footer') ?>