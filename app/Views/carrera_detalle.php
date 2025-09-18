<?= $this->include('layouts/header') ?>

<section class="program-detail py-5">
    <div class="container">

        <!-- Encabezado Carrera -->
        <div class="text-center mb-5">
            <h1><?= esc($carrera['nombre']) ?></h1>
            <p><?= esc($carrera['descripcion']) ?></p>
        </div>

        <!-- Info rápida -->
        <div class="row text-center mb-5">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100 border-0 info-card">
                    <div class="card-body">
                        <i class="fas fa-graduation-cap fa-2x mb-3 text-primary"></i>
                        <h5>Nivel</h5>
                        <p><?= esc($carrera['nivel']) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100 border-0 info-card">
                    <div class="card-body">
                        <i class="fas fa-chalkboard-teacher fa-2x mb-3 text-primary"></i>
                        <h5>Modalidad</h5>
                        <p><?= esc($carrera['modalidad']) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100 border-0 info-card">
                    <div class="card-body">
                        <i class="fas fa-clock fa-2x mb-3 text-primary"></i>
                        <h5>Duración</h5>
                        <p><?= esc($carrera['duracion']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Perfil de Ingreso / Egreso / Campo laboral -->
        <div class="row mb-5">
            <div class="col-md-4">
                <h4><i class="fas fa-user-plus me-2 text-accent"></i> Perfil de Ingreso</h4>
                <p><?= esc($carrera['perfil_ingreso']) ?></p>
            </div>
            <div class="col-md-4">
                <h4><i class="fas fa-user-graduate me-2 text-accent"></i> Perfil de Egreso</h4>
                <p><?= esc($carrera['perfil_egreso']) ?></p>
            </div>
            <div class="col-md-4">
                <h4><i class="fas fa-briefcase me-2 text-accent"></i> Campo Laboral</h4>
                <p><?= esc($carrera['campo_laboral']) ?></p>
            </div>
        </div>

        <!-- Plan de estudios -->
        <h3 class="text-center mb-4">Plan de Estudios</h3>
        <?php if (!empty($materiasPorCiclo)): ?>
            <div class="row">
                <?php foreach ($materiasPorCiclo as $ciclo => $lista): ?>
                    <div class="col-md-4 mb-4">
                        <div class="accordion shadow-sm ciclo-card" id="accordionCiclo<?= $ciclo ?>">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?= $ciclo ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse<?= $ciclo ?>" aria-expanded="false"
                                        aria-controls="collapse<?= $ciclo ?>">
                                        Cuatrimestre <?= $ciclo ?>
                                    </button>
                                </h2>
                                <div id="collapse<?= $ciclo ?>" class="accordion-collapse collapse"
                                    aria-labelledby="heading<?= $ciclo ?>" data-bs-parent="#accordionCiclo<?= $ciclo ?>">
                                    <div class="accordion-body scrollable">
                                        <ul>
                                            <?php foreach ($lista as $materia): ?>
                                                <li class="mb-3">
                                                    <strong><?= esc($materia['nombre']) ?></strong>
                                                    <span class="badge bg-primary"><?= esc($materia['creditos']) ?>
                                                        créditos</span><br>
                                                    <small><?= esc($materia['descripcion']) ?></small>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">No hay materias registradas para esta carrera.</p>
        <?php endif; ?>

        <!-- Botón -->
        <div class="text-center mt-5">
            <a href="<?= base_url() ?>" class="btn btn-primary px-4 py-2">
                <i class="fas fa-arrow-left me-2"></i> Regresar al inicio
            </a>
        </div>

    </div>
</section>

<?= $this->include('layouts/footer') ?>