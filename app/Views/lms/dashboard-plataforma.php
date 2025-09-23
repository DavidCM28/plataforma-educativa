<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <main class="content">
        <h1>Bienvenido, <?= session('usuario') ?></h1>
        <p>Aquí podrás acceder a las herramientas de tu rol.</p>
    </main>

</body>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggleBtn = document.getElementById("sidebarToggle");
        const sidebar = document.getElementById("sidebar");
        const content = document.querySelector(".content");

        toggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("collapsed");
            content.classList.toggle("collapsed");
        });
    });

</script>


</html>