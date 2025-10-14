<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/profesores/grupos.css') ?>">

<section class="grupo-panel">
    <h2><i class="fas fa-users"></i> <?= esc($grupo['materia']) ?> - <?= esc($grupo['grupo']) ?></h2>

    <div class="tabs">
        <button class="tab-btn active" data-tab="alumnos">Alumnos</button>
        <button class="tab-btn" data-tab="tareas">Tareas</button>
        <button class="tab-btn" data-tab="calificaciones">Calificaciones</button>
    </div>

    <div class="tab-content active" id="alumnos">
        <?= view('lms/profesor/grupos/alumnos', ['alumnos' => $alumnos]) ?>
    </div>

    <div class="tab-content" id="tareas">
        <?= view('lms/profesor/grupos/tareas', ['tareas' => $tareas, 'grupo' => $grupo]) ?>
    </div>

    <div class="tab-content" id="calificaciones">
        <?= view('lms/profesor/grupos/calificaciones', ['calificaciones' => $calificaciones]) ?>
    </div>
</section>

<script>
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.tab).classList.add('active');
        });
    });
</script>

<?= $this->endSection() ?>