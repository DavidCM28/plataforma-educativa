<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/chat.css') ?>">

<div class="chat-wrapper" data-chat-id="<?= $chatId ?>" data-user="<?= session('id') ?>">

    <!-- HEADER DEL CHAT -->
    <div class="chat-header">
        <div class="chat-user">
            <img src="<?= base_url('assets/img/user-default.jpg') ?>">
            <div>
                <strong>Chat #<?= $chatId ?></strong>
                <small id="typingIndicator" class="typing hidden">Escribiendo...</small>
            </div>
        </div>
    </div>

    <!-- HISTORIAL -->
    <div class="chat-body" id="chatBody">
        <?php foreach ($mensajes as $m): ?>
            <div class="msg <?= $m['emisor_id'] == session('id') ? 'yo' : 'otro' ?>">
                <div class="burbuja">
                    <?= esc($m['mensaje']) ?>
                </div>
                <span class="hora"><?= substr($m['enviado_en'], 11, 5) ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- INPUT -->
    <div class="chat-input">
        <input type="text" id="msgInput" placeholder="Escribe un mensaje..." autocomplete="off">

        <label for="fileInput" class="file-btn">
            <i class="fas fa-paperclip"></i>
        </label>
        <input type="file" id="fileInput" hidden>

        <button id="sendBtn"><i class="fas fa-paper-plane"></i></button>
    </div>

</div>

<script src="http://localhost:3001/socket.io/socket.io.js"></script>
<script src="<?= base_url('assets/js/chat.js') ?>"></script>

<?= $this->endSection() ?>