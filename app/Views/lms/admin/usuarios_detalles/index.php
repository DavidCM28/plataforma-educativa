<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>

    <!-- 🎨 Estilos principales -->
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/detalle_usuario.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- ⚙️ Plugins -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- 🧠 Base URL para JS -->
    <meta name="base-url" content="<?= base_url() ?>">

    <!-- 🌍 AutocompleteJS de Geoapify -->
    <link rel="stylesheet" href="https://unpkg.com/@geoapify/autocomplete-js@1.7.1/dist/autocomplete.min.css" />
    <script src="https://unpkg.com/@geoapify/autocomplete-js@1.7.1/dist/autocomplete.min.js" defer></script>
</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <main class="content-dark">
        <div class="crud-container">
            <h2><i class="fas fa-id-card"></i> Gestión de Datos Personales</h2>
            <p>Busca a un usuario para agregar o editar su información detallada.</p>

            <!-- 🔍 Barra de búsqueda dinámica -->
            <div class="form-group" style="position: relative;">
                <label for="inputBuscarUsuario"><strong>Buscar usuario:</strong></label>
                <input type="text" id="inputBuscarUsuario" class="form-control"
                    placeholder="Escribe un nombre o apellido...">
                <ul id="listaSugerencias" class="sugerencias-lista"></ul>
            </div>
            <hr>

            <!-- 📋 Formulario dinámico -->
            <div id="formularioDetalles" class="fade-in"></div>
        </div>
    </main>

    <!-- 🔧 Scripts principales -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- 📁 Script personalizado -->
    <script src="<?= base_url('assets/js/admin/detalles_usuario.js') ?>" defer></script>
    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>

</body>

</html>