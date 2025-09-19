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

            <form class="comfort-form" id="loginForm" novalidate>
                <div class="soft-field">
                    <div class="field-container">
                        <input type="text" id="matricula" name="matricula" required autocomplete="email">
                        <label for="email">Matricula</label>
                        <div class="field-accent"></div>
                    </div>
                </div>

                <div class="soft-field">
                    <div class="field-container">
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <label for="password">Contraseña</label>
                        <button type="button" class="gentle-toggle" id="passwordToggle"
                            aria-label="Toggle password visibility">
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
                        <div class="field-accent"></div>
                    </div>
                    <span class="gentle-error" id="passwordError"></span>
                </div>

                <div class="comfort-options">
                    <label class="gentle-checkbox">
                        <input type="checkbox" id="remember" name="remember">
                        <span class="checkbox-soft">
                            <div class="check-circle"></div>
                            <svg class="check-mark" width="12" height="10" viewBox="0 0 12 10" fill="none">
                                <path d="M1 5l3 3 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span class="checkbox-text">Recuerdame</span>
                    </label>
                    <a href="#" class="comfort-link">Olvidaste la contraseña?</a>
                </div>

                <button type="submit" class="comfort-button">
                    <div class="button-background"></div>
                    <span class="button-text">Acceder</span>
                    <div class="button-loader">
                        <div class="gentle-spinner">
                            <div class="spinner-circle"></div>
                        </div>
                    </div>
                    <div class="button-glow"></div>
                </button>
            </form>
        </div>
    </div>

    <script src="../../shared/js/form-utils.js"></script>
    <script src="<?= base_url('assets/js/login.js') ?>"></script>

    <?= $this->include('layouts/footer') ?>