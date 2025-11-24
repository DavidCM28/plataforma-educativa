<?php
use CodeIgniter\I18n\Time;

$usuarioActual = session('id') ?? session('usuario_id') ?? session('id_usuario');
?>

<?php if (!empty($publicaciones)): ?>
    <div class="feed-publicaciones-prof">
        <?php foreach ($publicaciones as $p): ?>
            <article class="publicacion" data-id="<?= $p['id'] ?>">
                <!-- 👤 CABECERA -->
                <header class="publicacion-header">
                    <?php
                    $foto = $p['foto'] ?? '';
                    $esCloud = str_contains($foto, 'cloudinary.com') || str_contains($foto, 'http');
                    $rutaFoto = $foto
                        ? ($esCloud ? $foto : base_url('uploads/usuarios/' . esc($foto)))
                        : 'https://ui-avatars.com/api/?background=ff9e64&color=000&name=' . urlencode($p['nombre'] ?? 'U');
                    ?>
                    <img src="<?= esc($rutaFoto) ?>" class="publicacion-avatar" alt="Foto usuario">

                    <div class="publicacion-meta">
                        <span class="publicacion-nombre">
                            <?= esc($p['nombre'] . ' ' . $p['apellido_paterno'] . ' ' . $p['apellido_materno']) ?>
                        </span>
                        <span class="publicacion-fecha">
                            <?= Time::parse($p['fecha_publicacion'])->toLocalizedString('d MMM yyyy, HH:mm') ?>
                        </span>
                    </div>

                    <?php if ($usuarioActual == $p['usuario_id']): ?>
                        <div class="publicacion-acciones">
                            <button class="btn-icono btn-editar-publicacion" title="Editar" data-id="<?= $p['id'] ?>">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn-icono btn-eliminar-publicacion" title="Eliminar" data-id="<?= $p['id'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </header>

                <!-- 📝 SOLO CONTENIDO -->
                <div class="publicacion-body">
                    <div class="publicacion-contenido">
                        <div class="contenido-texto">
                            <?= $p['contenido'] ?>
                        </div>

                        <!-- 📎 ARCHIVOS (opcional, se mantiene) -->
                        <?php
                        $archivosModel = model('App\Models\PublicacionModel');
                        $archivos = $archivosModel->obtenerArchivos($p['id']);
                        ?>
                        <?php if (!empty($archivos)): ?>
                            <div class="publicacion-archivos">
                                <?php foreach ($archivos as $a): ?>
                                    <a href="<?= base_url('uploads/publicaciones/' . esc($a['archivo'])) ?>" target="_blank">
                                        <i class="fas fa-paperclip"></i> <?= esc($a['archivo']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="placeholder">📭 No hay publicaciones aún.</p>
<?php endif; ?>