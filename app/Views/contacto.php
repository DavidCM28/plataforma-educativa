<?= $this->include('layouts/header') ?>

<?php if (session()->getFlashdata('mensaje')): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: '<?= session()->getFlashdata('mensaje') ?>',
        confirmButtonColor: '#ff9e64'
    });
</script>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        html: `
            <?php foreach(session()->getFlashdata('errors') as $error): ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        `,
        confirmButtonColor: '#ff4757'
    });
</script>
<?php endif; ?>


<section class="contacto container py-5">
    <h2 class="section-title">Contáctanos</h2>

    <form action="<?= site_url('contacto/guardar') ?>" method="post">
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?= old('nombre') ?>" required>
        </div>

        <div class="form-group">
            <label>Correo</label>
            <input type="email" name="correo" class="form-control" value="<?= old('correo') ?>" required>
        </div>

        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control" value="<?= old('telefono') ?>">
        </div>

        <div class="form-group">
            <label>Mensaje</label>
            <textarea name="mensaje" class="form-control" rows="4"><?= old('mensaje') ?></textarea>
        </div>

        <button class="btn btn-primary">Enviar</button>
    </form>
</section>

<?= $this->include('layouts/footer') ?>
