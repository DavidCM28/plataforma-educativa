<link rel="stylesheet" href="<?= base_url('assets/css/alumnos/examenes.css') ?>">

<section class="examenes-section">
    <h3><i class="fas fa-book"></i> Exámenes disponibles</h3>

    <?php if (empty($examenes)): ?>
        <p class="placeholder">
            <i class="fas fa-info-circle"></i> No hay exámenes publicados actualmente.
        </p>
    <?php else: ?>
        <div class="examenes-grid">
            <?php foreach ($examenes as $ex): ?>
                <?php
                $estado = '📘 Disponible';
                $ahora = date('Y-m-d H:i:s');
                if (!empty($ex['fecha_cierre']) && $ex['fecha_cierre'] < $ahora) {
                    $estado = '🔒 Cerrado';
                } elseif (!empty($ex['fecha_publicacion']) && $ex['fecha_publicacion'] > $ahora) {
                    $estado = '⏳ Próximo';
                }
                ?>
                <div class="examen-card">
                    <div class="examen-header">
                        <h4><?= esc($ex['titulo']) ?></h4>
                        <span class="estado"><?= $estado ?></span>
                    </div>
                    <p class="descripcion"><?= esc($ex['descripcion']) ?></p>
                    <ul class="info">
                        <li><i class="far fa-clock"></i>
                            <?= $ex['tiempo_minutos'] ? $ex['tiempo_minutos'] . ' min' : 'Sin límite' ?></li>
                        <li><i class="fas fa-percentage"></i> <?= esc($ex['puntos_totales']) ?> pts</li>
                        <li><i class="fas fa-layer-group"></i> Parcial <?= esc($ex['parcial_num']) ?></li>
                    </ul>
                    <div class="acciones">
                        <?php if ($estado === '📘 Disponible'): ?>
                            <button class="btn-main iniciar-examen" data-id="<?= $ex['id'] ?>">
                                <i class="fas fa-play"></i> Comenzar examen
                            </button>
                        <?php else: ?>
                            <button class="btn-sec" disabled><?= $estado ?></button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
    document.querySelectorAll(".iniciar-examen").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.dataset.id;

            // 🚀 Redirigir al modo examen completo
            window.location.href = `${window.base_url || "<?= base_url() ?>"}alumno/examenes/resolver/${id}`;
        });
    });
</script>