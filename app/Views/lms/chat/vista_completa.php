<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/chat-teams.css') ?>">

<div class="chat-teams">

    <!-- PANEL IZQUIERDO -->
    <div class="chat-sidebar">
        <h3><i class="fas fa-comments"></i> Chats</h3>

        <div class="chat-search">
            <input type="text" id="userSearch" placeholder="Buscar o iniciar chat...">
        </div>

        <div id="searchResults"></div>

        <div class="chat-list">
            <?php foreach ($chats as $c): ?>
                <div class="chat-item <?= ($chatId == $c['chat_id']) ? 'active' : '' ?>" data-chat-id="<?= $c['chat_id'] ?>"
                    onclick="window.location.href='<?= base_url('api/chat/mensajes/' . $c['chat_id']) ?>'">

                    <img src="<?= $c['usuario']['foto'] ?: base_url('assets/img/user-default.jpg') ?>">
                    <div>
                        <strong><?= $c['usuario']['nombre'] . ' ' . $c['usuario']['apellido_paterno'] . ' ' . $c['usuario']['apellido_materno'] ?></strong>
                        <small class="ultimoMsg"><?= esc($c['ultimo']) ?></small>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- PANEL DERECHO -->
    <div class="chat-main">

        <?php if (!$chatId): ?>
            <div class="chat-welcome">
                <h2>Selecciona un chat para comenzar</h2>
            </div>

        <?php else: ?>
            <div class="chat-header">
                <h3>
                    <?= $otroUsuario['nombre'] . ' ' . $otroUsuario['apellido_paterno'] . ' ' . $otroUsuario['apellido_materno'] ?>
                </h3>
                <small id="estadoUsuario">● Verificando...</small>
                <div id="typingIndicator" style="font-size:13px; color:#999; display:none;">
                    está escribiendo...
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php foreach ($mensajes as $m): ?>
                    <div class="msg <?= ($m['emisor_id'] == session('id')) ? 'me' : 'other' ?>">

                        <?php
                        $isFile = $m['tipo'] === 'archivo';
                        $url = $m['archivo_url'];
                        $msgText = $m['mensaje'];
                        $filename = $url ? basename($url) : "";
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        ?>

                        <?php if ($m['emisor_id'] == session('id')): ?>
                            <?php if ($m['estado'] == 1): ?>
                                <span class="msg-check visto">✓✓</span>
                            <?php else: ?>
                                <span class="msg-check enviado">✓</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($isFile): ?>
                            <?php if ($isImage): ?>
                                <a href="<?= $url ?>" target="_blank">
                                    <img src="<?= $url ?>" class="img-msg">
                                </a>
                            <?php else: ?>
                                <a href="<?= $url ?>" target="_blank" class="file-msg">
                                    <i class="fas fa-paperclip"></i> <?= $filename ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (!empty($msgText)): ?>
                            <p class="text-msg"><?= esc($msgText) ?></p>
                        <?php endif; ?>

                        <small><?= esc($m['enviado_en']) ?></small>
                    </div>
                <?php endforeach; ?>

            </div>

            <div class="file-preview" id="filePreview" style="display:none;">
                <div id="filePreviewName"></div>

                <button id="btnCancelFile" class="cancel-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="chat-input">
                <button id="btnFile" class="file-btn">
                    <i class="fas fa-paperclip"></i>
                </button>

                <input type="file" id="fileInput" accept="image/*, .pdf, .docx, .xlsx" hidden>

                <input type="text" id="msgText" placeholder="Escribe un mensaje...">

                <button id="btnSend">Enviar</button>
            </div>

        <?php endif; ?>

    </div>

</div>

<script>
    window.base_url = "<?= base_url() ?>/";
    window.usuario_id = "<?= session('id') ?>";
    window.chat_abierto = "<?= $chatId ?>";
    window.otro_usuario_id = "<?= $otroUsuario['id'] ?? '' ?>";
</script>


<script src="<?= base_url('assets/js/chat-teams.js') ?>"></script>

<?= $this->endSection() ?>