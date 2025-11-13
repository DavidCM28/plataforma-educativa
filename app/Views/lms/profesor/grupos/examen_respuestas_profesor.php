<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/profesores/examen_respuestas_profesor.css') ?>">
<script>
    window.examenId = <?= $examen['id'] ?>;
    window.base = "<?= base_url() ?>";
</script>

<section class="examen-respuestas">
    <h2 class="titulo">
        <i class="fas fa-users"></i> Respuestas del examen: <?= esc($examen['titulo']) ?>
    </h2>

    <button id="btnVolverExamenes" class="btn-volver">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </button>


    <div class="lista-alumnos">
        <?php foreach ($alumnos as $al): ?>

            <?php
            // === ESTADO DEL ALUMNO ===
            $estado = $al['respuesta']['estado'] ?? 'no_iniciado';

            $textoEstado = match ($estado) {
                'finalizado' => 'Finalizado',
                'en_progreso' => 'En proceso',
                default => 'No iniciado'
            };
            ?>

            <div class="alumno-card">

                <div class="col nombre-col">
                    <h4><?= esc($al['nombre'] . ' ' . $al['apellido_paterno']) ?></h4>
                    <p class="mat"><?= esc($al['matricula']) ?></p>
                </div>

                <div class="col estado-col">
                    <div class="estado-box estado-<?= $estado ?>">
                        <?= $textoEstado ?>
                    </div>
                </div>

                <?php if (!empty($al['respuesta'])): ?>
                    <a class="btn-ver col boton-col"
                        href="<?= base_url("profesor/grupos/examenes/detalle-respuesta/{$examen['id']}/{$al['alumno_id']}") ?>">
                        Revisar
                    </a>
                <?php endif; ?>

            </div>

        <?php endforeach; ?>
    </div>

</section>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const btn = document.getElementById("btnVolverExamenes");

        if (btn) {
            btn.addEventListener("click", () => {
                window.location.href =
                    `${window.base}profesor/grupos/ver/<?= $asignacionId ?>?tab=examenes`;

            });
        }
    });

</script>

<?= $this->endSection() ?>