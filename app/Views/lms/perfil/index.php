<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/perfil.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <main class="perfil-container">

        <div class="perfil-card">
            <div class="perfil-foto">
                <img src="<?= $usuario['foto']
                    ? base_url('uploads/perfiles/' . $usuario['foto'])
                    : base_url('assets/img/user-default.jpg') ?>" alt="Foto de perfil">

                <form action="<?= base_url('perfil/actualizarFoto') ?>" method="post" enctype="multipart/form-data">
                    <label for="foto" class="btn-foto">Cambiar Foto</label>
                    <input type="file" name="foto" id="foto" accept="image/*" hidden>
                </form>
            </div>

            <div class="perfil-datos">
                <h3>Datos Generales</h3>
                <div class="campo">
                    <label>Nombre:</label>
                    <span><?= esc($usuario['nombre']) ?></span>
                </div>
                <div class="campo">
                    <label>Apellido Paterno:</label>
                    <span><?= esc($usuario['apellido_paterno']) ?></span>
                </div>
                <div class="campo">
                    <label>Apellido Materno:</label>
                    <span><?= esc($usuario['apellido_materno']) ?></span>
                </div>
                <div class="campo">
                    <label>Email:</label>
                    <span><?= esc($usuario['email']) ?></span>
                </div>
                <div class="campo">
                    <label><?= $usuario['matricula'] ? 'Matrícula:' : 'Número de empleado:' ?></label>
                    <span><?= esc($usuario['matricula'] ?? $usuario['num_empleado']) ?></span>
                </div>

                <div class="campo">
                    <label>Rol:</label>
                    <span><?= esc(session('rol')) ?></span>
                </div>
            </div>

            <div class="perfil-password">
                <h3>Cambiar Contraseña</h3>
                <form action="<?= base_url('perfil/actualizarPassword') ?>" method="post">
                    <input type="password" name="password" placeholder="Nueva contraseña" required>
                    <input type="password" name="confirmar" placeholder="Confirmar contraseña" required>
                    <button type="submit" class="btn-guardar">Guardar</button>
                </form>
            </div>

            <div class="perfil-extra">
                <h3>Información Personal (Próximamente)</h3>
                <p class="texto-muted">Esta sección estará disponible para actualizar tus datos personales.</p>
            </div>
        </div>
    </main>
</body>

</html>