<?= $this->extend('lms/dashboard-plataforma') ?>
<?= $this->section('contenidoDashboard') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/chat-list.css') ?>">

<div class="chat-wrapper">

    <!-- ====================== -->
    <!-- 🟦 PANEL IZQUIERDO -->
    <!-- ====================== -->
    <aside class="chat-sidebar">
        <h2><i class="fas fa-comments"></i> Chats</h2>

        <!-- Buscador -->
        <div class="chat-search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="userSearch" placeholder="Buscar o iniciar chat...">
        </div>

        <div id="searchResults" class="search-results"></div>

        <!-- Lista de chats existentes -->
        <div class="chat-list">
            <?php if (!empty($chats)): ?>
                <?php foreach ($chats as $c): ?>
                    <div class="chat-card" data-chat="<?= $c['chat_id'] ?>">
                        <div class="chat-avatar">
                            <img src="<?= base_url('assets/img/user-default.jpg') ?>">
                        </div>

                        <div class="chat-info">
                            <strong>Usuario <?= $c['con'] ?></strong>
                            <small><?= esc($c['ultimo']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>

    <!-- ====================== -->
    <!-- 🟧 PANEL DERECHO -->
    <!-- ====================== -->
    <section class="chat-main">
        <p class="chat-placeholder">
            Selecciona un chat para comenzar
        </p>
    </section>

</div>

<script>
    // ==========================
    // LISTA DE CHATS
    // ==========================
    document.querySelectorAll(".chat-card").forEach(card => {
        card.addEventListener("click", () => {
            const chatId = card.dataset.chat;
            window.location.href = `${window.base_url}mensajes/${chatId}`;
        });
    });

    // ==========================
    // BUSCADOR DE USUARIOS
    // ==========================
    const input = document.getElementById("userSearch");
    const results = document.getElementById("searchResults");
    let timeout = null;

    input.addEventListener("input", () => {
        clearTimeout(timeout);

        const q = input.value.trim();
        if (q.length < 2) {
            results.innerHTML = "";
            return;
        }

        timeout = setTimeout(() => buscar(q), 250);
    });

    async function buscar(q) {
        const res = await fetch(`${window.base_url}api/usuarios/buscar?q=${q}`);
        const data = await res.json();

        results.innerHTML = "";

        data.data.forEach(u => {
            const foto = u.foto && u.foto.trim() !== ""
                ? u.foto
                : window.base_url + "assets/img/user-default.jpg";

            const item = document.createElement("div");
            item.classList.add("search-item");
            item.innerHTML = `
                <img src="${foto}">
                <div>
                    <strong>${u.nombre} ${u.apellido_paterno} ${u.apellido_materno}</strong>
                </div>
            `;

            item.addEventListener("click", () => iniciarChat(u.id));
            results.appendChild(item);
        });
    }

    async function iniciarChat(uid) {
        const fd = new FormData();
        fd.append("usuario1", window.usuario_id);
        fd.append("usuario2", uid);

        const res = await fetch(`${window.base_url}api/chat/crearPrivado`, {
            method: "POST",
            body: fd
        });
        const data = await res.json();

        if (data.chat_id) {
            window.location.href = `${window.base_url}mensajes/${data.chat_id}`;
        }
    }
</script>

<?= $this->endSection() ?>