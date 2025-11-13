<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/profesores/examenes.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/profesores/tareas.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/alert.css') ?>">
<script src="<?= base_url('assets/js/alert.js') ?>"></script>

<!-- 🔔 Contenedor global de alertas -->
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
<section class="editor-examen">
    <div class="editor-header">
        <button class="btn-sec" onclick="history.back()"><i class="fas fa-arrow-left"></i> Regresar</button>
        <h2 id="tituloEditor">
            <i class="fas fa-file-alt"></i>
            <?= isset($examen) ? esc($examen['titulo']) : 'Nuevo examen' ?>
            <?php if (!empty($criterioPorcentaje)): ?>
                <small style="opacity:.7;font-size:14px;">(<?= $criterioPorcentaje ?>%)</small>
            <?php endif; ?>
        </h2>
    </div>

    <form id="formExamen" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= esc($examen['id'] ?? '') ?>">
        <input type="hidden" name="asignacion_id" value="<?= esc($asignacionId) ?>">

        <div class="editor-form">
            <div class="campo">
                <label>Título</label>
                <input type="text" name="titulo" value="<?= esc($examen['titulo'] ?? '') ?>" required>
            </div>

            <div class="campo">
                <label>Descripción</label>
                <textarea name="descripcion" rows="2"><?= esc($examen['descripcion'] ?? '') ?></textarea>
            </div>

            <div class="campo">
                <label>Instrucciones</label>
                <textarea name="instrucciones" rows="2"><?= esc($examen['instrucciones'] ?? '') ?></textarea>
            </div>

            <div class="campo">
                <label>Parcial</label>
                <select name="parcial_num">
                    <?php
                    $numParciales = 3; // valor por defecto
                    if (isset($asignacionId)) {
                        $db = \Config\Database::connect();
                        $res = $db->table('grupo_materia_profesor')
                            ->select('ciclos_academicos.num_parciales')
                            ->join('ciclos_academicos', 'ciclos_academicos.id = grupo_materia_profesor.ciclo_id')
                            ->where('grupo_materia_profesor.id', $asignacionId)
                            ->get()
                            ->getRowArray();
                        $numParciales = $res['num_parciales'] ?? 3;
                    }
                    for ($i = 1; $i <= $numParciales; $i++):
                        ?>
                        <option value="<?= $i ?>" <?= isset($examen['parcial_num']) && $examen['parcial_num'] == $i ? 'selected' : '' ?>>
                            Parcial <?= $i ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="grid">
                <div>
                    <label for="tiempo_minutos">
                        Tiempo máximo permitido
                        <small style="color:var(--text-light);display:block;font-size:0.85em;">
                            (en minutos, comienza a contar cuando el alumno inicia el examen)
                        </small>
                    </label>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <input type="number" id="tiempo_minutos" name="tiempo_minutos" placeholder="Ej. 40" min="0"
                            value="<?= esc($examen['tiempo_minutos'] ?? '') ?>" style="width:100px;">
                        <span style="font-weight:600;">minutos</span>
                    </div>
                </div>
                <p id="previewTiempo" style="font-size:0.9em;color:var(--text-light);margin-top:4px;">
    Sin límite de tiempo.
</p>


                <div>
                    <label>Intentos máximos</label>
                    <input type="number" name="intentos_maximos" min="1"
                        value="<?= esc($examen['intentos_maximos'] ?? '') ?>">
                </div>
                <div>
                    <label>Publicación</label>
                    <input type="datetime-local" name="fecha_publicacion"
                        value="<?= !empty($examen['fecha_publicacion']) ? date('Y-m-d\TH:i', strtotime($examen['fecha_publicacion'])) : '' ?>">
                </div>
                <div>
                    <label>Cierre</label>
                    <input type="datetime-local" name="fecha_cierre"
                        value="<?= !empty($examen['fecha_cierre']) ? date('Y-m-d\TH:i', strtotime($examen['fecha_cierre'])) : '' ?>">
                </div>
            </div>
        </div>

        <hr>

        <div class="editor-preguntas">
            <div class="preguntas-header">
                <h3><i class="fas fa-list-ol"></i> Preguntas</h3>
            </div>
            <p id="totalPuntos"
   style="font-weight:600;color:var(--text);margin-top:4px;">
   Puntos totales: 0 / 100
</p>


            <div id="contenedorPreguntas" class="preguntas-lista">
                <?php if (!empty($examen['preguntas'])): ?>
                    <?php foreach ($examen['preguntas'] as $idx => $p): ?>
                        <div class="tarea-card pregunta-card" data-id="<?= $p['id'] ?>">
                            <div class="preg-header">
                                <strong>Pregunta <?= $idx + 1 ?></strong>
                                <div>
                                    <button type="button" class="btn-sec eliminar-pregunta">Eliminar</button>
                                </div>
                            </div>

                            <label>Tipo de pregunta</label>
                            <select class="preg-tipo">
                                <option value="opcion" <?= $p['tipo'] === 'opcion' ? 'selected' : '' ?>>Opción múltiple</option>
                                <option value="abierta" <?= $p['tipo'] === 'abierta' ? 'selected' : '' ?>>Respuesta abierta
                                </option>
                            </select>

                            <label>Enunciado</label>
                            <textarea class="preg-texto" rows="2"><?= esc($p['pregunta']) ?></textarea>

                            <div class="puntos-wrap" style="display:flex;gap:10px;align-items:center;margin-top:6px;">
                                <label style="flex:1;">Valor (puntos)</label>
                                <input type="number" class="preg-puntos" value="<?= esc($p['puntos']) ?>" min="0" step="0.5"
                                    style="width:100px;">
                                <label class="chk-line" style="display:flex;align-items:center;gap:6px;">
                                    <input type="checkbox" class="preg-extra" <?= !empty($p['es_extra']) ? 'checked' : '' ?>>
                                    <span>Puntos extra</span>
                                </label>
                            </div>

                            <label>Imagen (opcional)</label>
                            <input type="file" name="pregunta_imagen_<?= $idx ?>">
                            <?php if (!empty($p['imagen'])): ?>
                                <div class="preg-imagen-actual">
                                    <img src="<?= base_url('uploads/examenes/' . $p['imagen']) ?>" alt="Imagen pregunta"
                                        style="max-width:200px;border-radius:6px;margin-top:6px;">
                                </div>
                            <?php endif; ?>

                            <div class="opciones-wrap" style="<?= $p['tipo'] === 'abierta' ? 'display:none' : '' ?>">
                                <div class="op-header">
                                    <strong>Opciones</strong>
                                    <button type="button" class="btn-sec add-opcion">Agregar opción</button>
                                </div>
                                <div class="lista-opciones">
                                    <?php if (!empty($p['opciones'])): ?>
                                        <?php foreach ($p['opciones'] as $op): ?>
                                            <div class="opcion-card">
                                                <label class="chk-line">
                                                    <input type="checkbox" class="op-correcta" <?= $op['es_correcta'] ? 'checked' : '' ?>>
                                                    <span>Correcta</span>
                                                </label>
                                                <input type="text" class="op-texto" value="<?= esc($op['texto']) ?>">
                                                <button type="button" class="btn-sec eliminar-opcion">Eliminar</button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </form>
</section>
<div class="floating-actions">
    <button id="btnAgregarPregunta" title="Agregar pregunta">
        <i class="fas fa-plus"></i>
    </button>
    <button id="btnGuardarExamen" title="Guardar examen">
        <i class="fas fa-save"></i>
    </button>
</div>

<script>
    window.base_url = "<?= rtrim(site_url(), '/') ?>/";
</script>
<script src="<?= base_url('assets/js/alert.js') ?>"></script>
<script src="<?= base_url('assets/js/profesores/examen_editor.js') ?>"></script>


<?= $this->endSection() ?>