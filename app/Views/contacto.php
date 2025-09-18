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

<section id="contacto" class="contact-cta">
    <div class="container">
        <h2 class="section-title-contacto">Contáctanos</h2>
        <p>
            Déjanos tus datos y un asesor académico se pondrá en contacto contigo para resolver todas tus dudas.
        </p>

        <form action="<?= site_url('api/contacto/guardar') ?>" method="post" class="contact-form">
            <?= csrf_field() ?>
            
            <input type="text" name="nombre" placeholder="Nombre completo" value="<?= old('nombre') ?>" required>
            <input type="email" name="correo" placeholder="Correo electrónico" value="<?= old('correo') ?>" required>
            <input type="text" name="telefono" placeholder="Teléfono" value="<?= old('telefono') ?>">
            <input type="text" name="asunto" placeholder="Asunto" required>
            <textarea name="mensaje" placeholder="Tu mensaje" rows="5" required><?= old('mensaje') ?></textarea>
            
            <button type="submit" class="btn">Enviar mensaje</button>
        </form>
    </div>
</section>


<?= $this->include('layouts/footer') ?>
