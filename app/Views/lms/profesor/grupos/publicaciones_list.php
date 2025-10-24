<?php
use CodeIgniter\I18n\Time;

$usuarioActual = session('id') ?? session('usuario_id') ?? session('id_usuario');
?>
<link rel="stylesheet" href="<?= base_url('assets/css/profesores/publicaciones.css') ?>">
<?php if (!empty($publicaciones)): ?>
    <?php foreach ($publicaciones as $p): ?>
        <div class="publicacion" data-id="<?= $p['id'] ?>">
            <!-- 👤 CABECERA -->
            <div class="publicacion-header">
                <?php
                $foto = $p['foto'] ?? '';
                $esCloud = str_contains($foto, 'cloudinary.com') || str_contains($foto, 'http');
                $rutaFoto = $foto
                    ? ($esCloud ? $foto : base_url('uploads/usuarios/' . esc($foto)))
                    : 'https://ui-avatars.com/api/?background=ff9e64&color=000&name=' . urlencode($p['nombre'] ?? 'U');
                ?>
                <img src="<?= esc($rutaFoto) ?>" class="publicacion-avatar" alt="Foto usuario">

                <div class="publicacion-datos">
                    <div class="publicacion-nombre">
                        <?= esc($p['nombre'] . ' ' . $p['apellido_paterno'] . ' ' . $p['apellido_materno']) ?>
                    </div>
                    <div class="publicacion-fecha">
                        <?= Time::parse($p['fecha_publicacion'])->toLocalizedString('d MMM yyyy, HH:mm') ?>
                    </div>
                </div>

                <?php if ($usuarioActual == $p['usuario_id']): ?>
                    <div class="publicacion-acciones">
                        <button class="btn-editar-publicacion" data-id="<?= $p['id'] ?>"><i class="fas fa-edit"></i></button>
                        <button class="btn-eliminar-publicacion" data-id="<?= $p['id'] ?>"><i class="fas fa-trash"></i></button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 📝 CONTENIDO -->
            <div class="publicacion-contenido <?= $p['tipo'] === 'aviso' ? 'publicacion-aviso' : '' ?>">
                <?php if ($p['tipo'] === 'aviso'): ?>
                    <div class="banner-aviso">
                        <i class="fas fa-bullhorn"></i>
                        <span>📢 Aviso importante</span>
                    </div>
                <?php endif; ?>

                <div class="contenido-texto">
                    <?= $p['contenido'] ?>
                </div>
            </div>


            <!-- 📎 ARCHIVOS -->
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

            <!-- 💬 COMENTARIOS -->
            <?php
            $comentarios = $archivosModel->obtenerComentarios($p['id']);
            ?>
            <div class="publicacion-comentarios">
                <?php if (!empty($comentarios)): ?>
                    <?php foreach ($comentarios as $c): ?>
                        <div class="comentario" data-id="<?= $c['id'] ?>">
                            <?php
                            $fotoC = $c['foto'] ?? '';
                            $esCloudC = str_contains($fotoC, 'cloudinary.com') || str_contains($fotoC, 'http');
                            $rutaFotoC = $fotoC
                                ? ($esCloudC ? $fotoC : base_url('uploads/usuarios/' . esc($fotoC)))
                                : 'https://ui-avatars.com/api/?background=dfe6e9&color=2d3436&name=' . urlencode($c['nombre'] ?? 'U');
                            ?>
                            <img src="<?= esc($rutaFotoC) ?>" class="comentario-avatar" alt="Foto usuario">

                            <div class="comentario-body">
                                <div class="comentario-header">
                                    <span class="comentario-nombre">
                                        <?= esc($c['nombre'] . ' ' . $c['apellido_paterno'] . ' ' . $c['apellido_materno']) ?>
                                    </span>
                                    <span class="comentario-fecha"><?= date('d/m/Y H:i', strtotime($c['fecha'])) ?></span>
                                    <?php if ($usuarioActual == $c['usuario_id']): ?>
                                        <div class="comentario-acciones">
                                            <button class="btn-editar-comentario" data-id="<?= $c['id'] ?>"><i
                                                    class="fas fa-edit"></i></button>
                                            <button class="btn-eliminar-comentario" data-id="<?= $c['id'] ?>"><i
                                                    class="fas fa-trash"></i></button>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="comentario-texto"><?= nl2br(esc($c['comentario'])) ?></div>
                                <button class="btn-responder" data-publicacion="<?= $p['id'] ?>" data-comentario="<?= $c['id'] ?>">
                                    <i class="fas fa-reply"></i> Responder
                                </button>

                                <!-- Sub-respuestas (pendiente de modelo si usas niveles) -->
                                <div class="respuestas"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="placeholder">Sé el primero en comentar 💬</p>
                <?php endif; ?>

                <!-- ➕ Nuevo comentario -->
                <form class="form-comentario" data-publicacion="<?= $p['id'] ?>">
                    <textarea name="comentario" rows="1" placeholder="Escribe un comentario..."></textarea>
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="placeholder">📭 No hay publicaciones aún.</p>
<?php endif; ?>