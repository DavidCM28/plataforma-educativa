<table class="alumnos-tabla">
    <thead>
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Matrícula</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($alumnos)): ?>
            <?php foreach ($alumnos as $i => $alumno): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($alumno['nombre'] . ' ' . $alumno['apellido_paterno'] . ' ' . $alumno['apellido_materno']) ?>
                    </td>
                    <td><?= esc($alumno['matricula']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" style="text-align:center;">No hay alumnos registrados en este grupo.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>