<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'UTSC - Universidad Tecnológica') ?></title>

    <!-- Estilos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Tus estilos -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">

    <!-- FONTAWESOME -->
    <script src="https://kit.fontawesome.com/54f8dc33fe.js" crossorigin="anonymous"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Navbar -->
    <nav class="main-navbar">
        <div class="container nav-container">
            <a href="<?= base_url() ?>" class="logo">
                <div class="logo-img">
                    <img src="<?= base_url('assets/img/logo.jpg') ?>" alt="UTSC Logo">
                </div>
                <span id="schoolName">UTSC</span>
            </a>

            <ul class="nav-links">
                <li><a href="<?= base_url() ?>#inicio">Inicio</a></li>
                <li><a href="<?= base_url() ?>#noticias">Noticias</a></li>
                <li><a href="<?= base_url() ?>#nosotros">Nosotros</a></li>

                <!-- Dropdown Académico -->
                <li class="dropdown">
                    <a href="#">Académico <i class="fas fa-chevron-down"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= base_url() ?>#admisiones">Admisiones</a></li>
                        <li><a href="<?= base_url() ?>#oferta-educativa">Oferta Educativa</a></li>
                        <li><a href="<?= base_url() ?>#becas">Becas</a></li>
                    </ul>
                </li>

                <!-- Dropdown Más -->
                <li class="dropdown">
                    <a href="#">Más <i class="fas fa-chevron-down"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= base_url() ?>#directorio">Directorio</a></li>
                        <li><a href="<?= base_url() ?>#faq">FAQ</a></li>
                    </ul>
                </li>

                <li><a href="<?= base_url('contacto') ?>">Contacto</a></li>
                <li><a href="<?= base_url('auth/login') ?>">Iniciar Sesión</a></li>
            </ul>

            <!-- Acciones -->
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


    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const navbarHeight = document.querySelector(".navbar").offsetHeight;
            const baseUrl = "<?= base_url() ?>";

            document.querySelectorAll('.nav-links a').forEach(anchor => {
                anchor.addEventListener("click", function (e) {
                    const href = this.getAttribute("href");

                    // 👉 Caso enlaces internos (#secciones)
                    if (href.startsWith(baseUrl + "#")) {
                        const targetId = href.split("#")[1];
                        const targetElement = document.getElementById(targetId);

                        if (window.location.pathname === "<?= parse_url(base_url(), PHP_URL_PATH) ?>") {
                            // Estamos en home → scroll suave
                            if (targetElement) {
                                e.preventDefault();
                                const elementPosition = targetElement.offsetTop - navbarHeight;

                                window.scrollTo({
                                    top: elementPosition,
                                    behavior: "smooth"
                                });
                            }
                        } else {
                            // No estamos en home → redirige con #
                            window.location.href = href;
                        }
                    }
                });
            });
        });
    </script>