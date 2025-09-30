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

    <main class="content">
        <section class="dashboard-welcome">
            <h1>👋 Bienvenido, <?= esc(session('nombre')) ?>!</h1>
            <p>Este es tu panel principal. Desde aquí puedes acceder a las secciones disponibles según tu rol:
                <strong><?= esc(session('rol')) ?></strong>.
            </p>
        </section>

        <section class="acciones-inicio">
            <p>Selecciona una opción del menú lateral para comenzar.</p>
        </section>
    </main>

    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
</body>

</html>