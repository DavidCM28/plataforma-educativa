<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'UTSC - Universidad Tecnológica') ?></title>

    <!-- Estilos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tus estilos -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">

    <!-- SweetAlert2 para alertas personalizadas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="<?= base_url() ?>" class="logo">
                <div class="logo-img">
                    <img src="<?= base_url('assets/img/logo.jpg') ?>" alt="UTSC Logo">
                </div>
                <span id="schoolName">UTSC</span>
            </a>
            
            <ul class="nav-links">
                <li><a href="<?= base_url() ?>">Inicio</a></li>
                <li><a href="#programas">Programas</a></li>
                <li><a href="#admisiones">Admisiones</a></li>
                <li><a href="#campus">Campus</a></li>
                <li><a href="<?= base_url('contacto') ?>">Contacto</a></li>
            </ul>

            <!-- 🔥 Acciones: aquí reponemos el toggle -->
            <div class="nav-actions">
                <button class="theme-toggle" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="#" class="btn">Aplicar ahora</a>
                <button class="menu-toggle" id="menuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>
