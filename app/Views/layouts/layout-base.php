<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Panel de Control') ?></title>

    <!-- Estilos globales -->
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Puedes agregar CSS propio del módulo aquí -->
    <?php if (isset($custom_css)): ?>
        <link rel="stylesheet" href="<?= base_url($custom_css) ?>">
    <?php endif; ?>
</head>

<body>

    <!-- Header y Sidebar -->
    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <!-- Contenido principal -->
    <main class="content">

        <!-- 🔽 Sección editable por vista -->
        <section class="modulo-content">
            <h1><?= esc($title ?? 'Título del módulo') ?></h1>
            <p>Aquí va el contenido principal del módulo.</p>
        </section>
        <!-- 🔼 Fin de la sección editable -->

    </main>

    <!-- Script para el sidebar -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toggleBtn = document.getElementById("sidebarToggle");
            const sidebar = document.getElementById("sidebar");
            const content = document.querySelector(".content");

            if (toggleBtn && sidebar && content) {
                toggleBtn.addEventListener("click", () => {
                    sidebar.classList.toggle("collapsed");
                    content.classList.toggle("collapsed");
                });
            }
        });
    </script>

    <!-- JS propio del módulo -->
    <?php if (isset($custom_js)): ?>
        <script src="<?= base_url($custom_js) ?>"></script>
    <?php endif; ?>

</body>

</html>