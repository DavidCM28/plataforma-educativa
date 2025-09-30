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
            <p>Rol actual: <strong><?= esc(session('rol')) ?></strong></p>
        </section>

        <section class="cards-grid">
            <div class="card-metric">
                <i class="fas fa-users"></i>
                <h2>Usuarios</h2>
                <p>+24 nuevos</p>
            </div>

            <div class="card-metric">
                <i class="fas fa-graduation-cap"></i>
                <h2>Materias</h2>
                <p>12 activas</p>
            </div>

            <div class="card-metric">
                <i class="fas fa-tasks"></i>
                <h2>Tareas</h2>
                <p>6 pendientes</p>
            </div>
        </section>
    </main>


    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
</body>

</html>