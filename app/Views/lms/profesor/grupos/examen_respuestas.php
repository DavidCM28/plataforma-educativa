<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/profesores/examenes.css') ?>">

<section class="examen-respuestas">
    <header class="tareas-header">
        <h3><i class="fas fa-users"></i> Respuestas del examen: <?= esc($examen['titulo']) ?></h3>
        <button onclick="history.back()" class="btn-sec"><i class="fas fa-arrow-left"></i> Volver</button>
    </header>

    <table class="tabla-lista">
        <thead>
            <tr>
                <th>Alumno</th>
                <th>Matrícula</th>
                <th>Estado</th>
                <th>Calificación</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alumnos as $a): ?>
                <tr>
                    <td><?= esc($a['nombre'] . ' ' . $a['apellido_paterno']) ?></td>
                    <td><?= esc($a['matricula']) ?></td>
                    <td>
                        <span class="estado estado-<?= esc($a['estado']) ?>">
                            <?= ucfirst(str_replace('_', ' ', $a['estado'])) ?>
                        </span>
                    </td>
                    <td><?= $a['calificacion'] !== null ? $a['calificacion'] : '—' ?></td>
                    <td>
                        <?php if ($a['respuesta_id']): ?>
                            <a href="<?= base_url("profesor/grupos/examenes/detalle-respuesta/{$examen['id']}/{$a['alumno_id']}") ?>"
                                class="btn-main btn-sm">Ver respuestas</a>
                        <?php else: ?>
                            <span style="color:#888">Sin registro</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<style>
    .tabla-lista {
        width: 100%;
        border-collapse: collapse;
    }

    .tabla-lista th,
    .tabla-lista td {
        border-bottom: 1px solid #333;
        padding: 8px 10px;
        text-align: left;
    }

    .estado {
        padding: 4px 8px;
        border-radius: 6px;
        color: #fff;
        font-size: 0.9em;
    }

    .estado-no_iniciado {
        background: #555;
    }

    .estado-en_progreso {
        background: #ffb84c;
        color: #000;
    }

    .estado-finalizado {
        background: #4caf50;
    }
</style>

<?= $this->endSection() ?>