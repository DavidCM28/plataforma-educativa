<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard principal</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <main class="content-dark">
        <section class="welcome-card">
            <h1>👋 Bienvenido, <?= esc(session('nombre')) ?>!</h1>
        </section>

        <!-- 🧩 Aquí se insertará el contenido del dashboard -->
        <?= $this->renderSection('contenidoDashboard') ?>
    </main>

    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
</body>

</html>