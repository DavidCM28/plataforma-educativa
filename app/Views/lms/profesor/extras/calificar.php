<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>



<!-- 🛎️ Contenedor global de alertas -->
<div id="alertContainer" class="alert-container"></div>

<!-- ⚠️ Modal de confirmación -->
<div id="confirmModal" class="confirm-modal hidden">
    <div class="confirm-box">
        <h3 id="confirmTitle">Confirmar acción</h3>
        <p id="confirmMessage">¿Estás seguro de continuar?</p>
        <div class="confirm-buttons">
            <button id="confirmCancelar">Cancelar</button>
            <button id="confirmAceptar">Aceptar</button>
        </div>
    </div>
</div>

<div class="calificaciones-container">

    <h1 class="titulo-calificaciones">Calificaciones</h1>

    <!-- SELECT DE ASIGNACION -->
    <div class="selector-asignacion">
        <label for="selectAsignacion">Selecciona una materia-grupo:</label>
        <select id="selectAsignacion">
            <option value="">-- Selecciona una asignación --</option>
            <?php foreach ($asignaciones as $a): ?>
                <option value="<?= $a['id'] ?>" data-ciclo="<?= $a['ciclo_id'] ?>">
                    <?= $a['materia'] ?> (<?= $a['grupo'] ?>) - <?= $a['ciclo'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- SELECT DE PARCIAL -->
    <div id="selectorParcial" class="selector-parcial hidden">
        <label for="selectParcial">Parcial:</label>
        <select id="selectParcial">
            <option value="">-- Selecciona un parcial --</option>
            <option value="1">1er Parcial</option>
            <option value="2">2do Parcial</option>
            <option value="3">3er Parcial</option>
        </select>
    </div>

    <!-- ❗ AQUÍ ESTABA FALTANDO -->
    <div id="contenedorAlumnos" class="contenedor-alumnos">
        <p class="placeholder">Selecciona una asignación para ver los alumnos.</p>
    </div>

    <!-- TABLA -->
    <div id="tablaCalificaciones" class="tabla-calificaciones hidden">
        <p class="placeholder">Selecciona parcial para mostrar criterios.</p>
    </div>


</div>
<script>
    window.base_url = "<?= rtrim(site_url(), '/') ?>/";
</script>

<script src="<?= base_url('assets/js/alert.js') ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">

<script src="<?= base_url('assets/js/profesores/calificaciones.js') ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/css/profesores/calificaciones.css') ?>">

<?= $this->endSection() ?>