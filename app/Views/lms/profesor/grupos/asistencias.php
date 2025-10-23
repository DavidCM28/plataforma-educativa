<link rel="stylesheet" href="<?= base_url('assets/css/profesores/asistencias.css') ?>">
<script>
    window.asignacionId = <?= json_encode($asignacionId) ?>;
</script>

<div class="asistencia-container">
    <h2><i class="fas fa-calendar-check"></i> Registro de Asistencias</h2>

    <form id="formAsistencia" data-asignacion="<?= esc($asignacionId) ?>">

        <input type="hidden" name="frecuencias" id="frecuenciaSeleccionada"
            value="<?= esc($frecuenciaSeleccionada ?? 1) ?>">

        <!-- 🔸 Contenedor principal (permanece igual) -->
        <div id="asistenciaContenido">

            <!-- 🔹 Contenedor interno dinámico -->
            <div id="asistenciaInner">
                <!-- 🗓️ Selector de fecha -->
                <div class="asistencia-fecha">
                    <label for="fechaAsistencia"><i class="fas fa-clock"></i> Fecha:</label>
                    <input type="date" name="fecha" id="fechaAsistencia" value="<?= esc($fecha) ?>" class="input-fecha">

                    <!-- 🔹 Historial -->
                    <?php if (!empty($fechasRegistradas)): ?>
                        <div class="historial-fechas">
                            <label><i class="fas fa-history"></i> Historial:</label>
                            <select id="selectHistorial" class="select-historial">
                                <option value="">-- Ver fecha anterior --</option>
                                <?php foreach ($fechasRegistradas as $f): ?>
                                    <option value="<?= esc($f['fecha']) ?>" <?= $f['fecha'] === $fecha ? 'selected' : '' ?>>
                                        <?= date('d/m/Y', strtotime($f['fecha'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 📢 Mensaje si aplica -->
                <?php if (!empty($mensaje)): ?>
                    <div class="mensaje-asistencia"><?= esc($mensaje) ?></div>
                <?php endif; ?>

                <!-- 🕒 Selector de frecuencia -->
                <?php if (!empty($frecuencias)): ?>
                    <div class="frecuencias-bar">
                        <label for="selectFrecuencia"><i class="fas fa-clock"></i> Frecuencia:</label>
                        <select id="selectFrecuencia" name="frecuencia" class="select-frecuencia">
                            <?php foreach ($frecuencias as $i => $hora): ?>
                                <option value="<?= $i + 1 ?>" <?= ($frecuenciaSeleccionada ?? 1) == ($i + 1) ? 'selected' : '' ?>>
                                    <?= esc($diaSemana) ?> (<?= esc($hora) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- 🧍 Tabla -->
                <table class="tabla-asistencia">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Estado</th>
                            <th>Justificación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($alumnos)): ?>
                            <tr>
                                <td colspan="3" class="sin-registros">No hay alumnos en este grupo.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($alumnos as $a):
                                $estado = $asistencias[$a['matricula']]['estado'] ?? 'asistencia';
                                $motivo = $asistencias[$a['matricula']]['observaciones'] ?? '';
                                ?>
                                <tr data-mga="<?= esc($a['mga_id']) ?>">
                                    <td><?= esc($a['apellido_paterno'] . ' ' . $a['apellido_materno'] . ' ' . $a['nombre']) ?>
                                    </td>
                                    <td>
                                        <button type="button" class="estado-btn <?= $estado ?>">
                                            <?= $estado === 'falta' ? '❌ Falta' : ($estado === 'justificada' ? '⚪ Justificada' : '✅ Asistencia') ?>
                                        </button>

                                        <input type="hidden" name="asistencias[<?= $a['mga_id'] ?>][estado]"
                                            value="<?= $estado ?>">
                                        <input type="hidden" name="asistencias[<?= $a['mga_id'] ?>][mga_id]"
                                            value="<?= esc($a['mga_id']) ?>">
                                    </td>
                                    <td>
                                        <select name="asistencias[<?= $a['mga_id'] ?>][observaciones]"
                                            class="select-justificacion" <?= $estado === 'justificada' ? '' : 'disabled' ?>>
                                            <option value="">-- Selecciona motivo --</option>
                                            <option value="Comprobante médico" <?= $motivo === 'Comprobante médico' ? 'selected' : '' ?>>Comprobante médico</option>
                                            <option value="Necesidad laboral" <?= $motivo === 'Necesidad laboral' ? 'selected' : '' ?>>Necesidad laboral</option>
                                            <option value="Asuntos académicos" <?= $motivo === 'Asuntos académicos' ? 'selected' : '' ?>>Asuntos académicos</option>
                                            <option value="Problemas personales" <?= $motivo === 'Problemas personales' ? 'selected' : '' ?>>Problemas personales</option>
                                            <option value="Otro" <?= $motivo === 'Otro' ? 'selected' : '' ?>>Otro</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div> <!-- /#asistenciaInner -->

        </div> <!-- /#asistenciaContenido -->

        <div class="acciones">
            <button type="submit" class="btn-main"><i class="fas fa-save"></i> Guardar asistencias</button>
        </div>
    </form>
</div>