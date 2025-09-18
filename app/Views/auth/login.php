<?= $this->include('layouts/header') ?>

<section class="login-section">
    <div class="login-box">
        <h2 class="section-title">Iniciar Sesión</h2>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('mensaje')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('mensaje') ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('auth/login') ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="matricula">Matrícula</label>
                <input type="text" name="matricula" id="matricula" 
                       class="form-control" placeholder="Ej: 2025001" 
                       value="<?= old('matricula') ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" 
                       class="form-control" placeholder="Tu contraseña" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Ingresar</button>
            </div>
        </form>
    </div>
</section>

<?= $this->include('layouts/footer') ?>
