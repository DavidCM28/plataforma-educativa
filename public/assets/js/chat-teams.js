document.addEventListener("DOMContentLoaded", () => {
  function renderMensajeHTML(msg) {
    let html = "";

    const hasText = msg.mensaje && msg.mensaje.trim() !== "";
    const hasFile = msg.tipo === "archivo" && msg.archivo_url;

    // -------------------------------
    // Si hay archivo adjunto
    // -------------------------------
    if (hasFile) {
      const lower = msg.archivo_url.toLowerCase();

      // Detectar si es imagen
      const esImagen =
        lower.endsWith(".png") ||
        lower.endsWith(".jpg") ||
        lower.endsWith(".jpeg") ||
        lower.endsWith(".gif") ||
        lower.endsWith(".webp");

      if (esImagen) {
        html += `
                <a href="${msg.archivo_url}" target="_blank">
                    <img src="${msg.archivo_url}" class="img-msg">
                </a>
            `;
      } else {
        const nombre = msg.archivo_url.split("/").pop();

        html += `
                <a href="${msg.archivo_url}" target="_blank" class="file-msg">
                    <i class="fas fa-paperclip"></i> ${nombre}
                </a>
            `;
      }
    }

    if (msg.emisor_id == window.usuario_id) {
      if (msg.estado == 1) {
        html += `<span class="msg-check visto">✓✓</span>`;
      } else {
        html += `<span class="msg-check enviado">✓</span>`;
      }
    }

    // -------------------------------
    // Si viene texto junto al archivo
    // -------------------------------
    if (hasText) {
      html += `
            <p class="text-msg">${msg.mensaje}</p>
        `;
    }

    // Fecha
    html += `<small>${msg.fecha}</small>`;

    return html;
  }

  // =======================
  // SOCKET.IO
  // =======================
  const socket = io("http://localhost:3001");
  const inputMsg = document.getElementById("msgText");

  if (window.usuario_id) {
    socket.emit("registrarUsuario", window.usuario_id);
  }

  if (window.chat_abierto) {
    socket.emit("joinChat", window.chat_abierto);
    socket.emit("marcarLeido", {
      chatId: window.chat_abierto,
      lector_id: window.usuario_id,
    });
  }

  const estado = document.getElementById("estadoUsuario");
  const otroId = window.otro_usuario_id; // tienes que pasarlo desde PHP

  socket.on("usuarioOnline", (id) => {
    if (id == otroId) {
      estado.textContent = "● En línea";
      estado.style.color = "#2ecc71";
    }
  });

  socket.on("usuarioOffline", (id) => {
    if (id == otroId) {
      estado.textContent = "● Desconectado";
      estado.style.color = "#e74c3c";
    }
  });

  async function verificarOnline() {
    const res = await fetch("http://localhost:3001/online");
    const online = await res.json();

    if (online.includes(window.otro_usuario_id)) {
      estado.textContent = "● En línea";
      estado.style.color = "#2ecc71";
    } else {
      estado.textContent = "● Desconectado";
      estado.style.color = "#e74c3c";
    }
  }

  verificarOnline();

  // =======================
  // AUTOSCROLL AL ABRIR CHAT
  // =======================
  function scrollBottom() {
    const box = document.getElementById("chatMessages");
    if (box) box.scrollTop = box.scrollHeight;
  }
  scrollBottom();

  // =======================
  // BUSCADOR DE USUARIOS
  // =======================
  const input = document.getElementById("userSearch");
  const results = document.getElementById("searchResults");
  let timer;

  input?.addEventListener("input", () => {
    clearTimeout(timer);

    const q = input.value.trim();
    if (q.length < 2) {
      results.innerHTML = "";
      return;
    }

    timer = setTimeout(() => buscar(q), 300);
  });

  async function buscar(q) {
    const res = await fetch(`${window.base_url}api/usuarios/buscar?q=${q}`);
    const data = await res.json();

    results.innerHTML = "";

    data.data.forEach((u) => {
      const foto =
        u.foto && u.foto !== ""
          ? u.foto
          : window.base_url + "assets/img/user-default.jpg";

      const d = document.createElement("div");
      d.classList.add("search-item");
      d.innerHTML = `
                <img src="${foto}">
                <div>
                    <strong>${u.nombre} ${u.apellido_paterno}</strong>
                </div>
            `;
      d.addEventListener("click", () => iniciarChat(u.id));
      results.appendChild(d);
    });
  }

  async function iniciarChat(uid) {
    const fd = new FormData();
    fd.append("usuario1", window.usuario_id);
    fd.append("usuario2", uid);

    const res = await fetch(`${window.base_url}api/chat/crearPrivado`, {
      method: "POST",
      body: fd,
    });

    const data = await res.json();
    if (data.chat_id) {
      window.location.href = `${window.base_url}api/chat/mensajes/${data.chat_id}`;
    }
  }

  let archivoPendiente = null;

  inputMsg?.addEventListener("input", () => {
    socket.emit("typing", {
      chatId: window.chat_abierto,
      userId: window.usuario_id,
    });

    clearTimeout(window.typingTimeout);
    window.typingTimeout = setTimeout(() => {
      socket.emit("stopTyping", {
        chatId: window.chat_abierto,
        userId: window.usuario_id,
      });
    }, 800);
  });

  const typingBox = document.getElementById("typingIndicator");

  socket.on("usuarioTyping", (uid) => {
    if (uid != window.usuario_id) {
      typingBox.style.display = "block";
    }
  });

  socket.on("usuarioStopTyping", (uid) => {
    if (uid != window.usuario_id) {
      typingBox.style.display = "none";
    }
  });
  socket.on("mensajesLeidos", (data) => {
    if (data.chatId != window.chat_abierto) return;

    // marcar como leído todos mis mensajes
    document.querySelectorAll(".msg.me .msg-check").forEach((chk) => {
      chk.classList.remove("enviado");
      chk.classList.add("visto");
      chk.textContent = "✓✓";
    });
  });

  // =======================
  // ENVIAR MENSAJE
  // =======================
  const btn = document.getElementById("btnSend");

  btn?.addEventListener("click", enviar);

  inputMsg?.addEventListener("keypress", (e) => {
    if (e.key === "Enter") enviar();
  });

  async function enviar() {
    const msg = inputMsg.value.trim();

    // === CASO 1 Y 2: archivo pendiente ===
    if (archivoPendiente) {
      const fd = new FormData();
      fd.append("chat_id", window.chat_abierto);
      fd.append("archivo", archivoPendiente);
      fd.append("mensaje", msg); // puede venir vacío o no

      const res = await fetch(`${window.base_url}api/chat/enviarArchivo`, {
        method: "POST",
        body: fd,
      });

      const data = await res.json();

      if (data.status === "ok") {
        socket.emit("nuevoMensaje", {
          chat_id: data.msg.chat_id,
          emisor_id: data.msg.emisor_id,
          mensaje: data.msg.mensaje,
          tipo: data.msg.tipo,
          archivo_url: data.msg.archivo_url,
          fecha: data.msg.fecha,
          participantes: data.msg.participantes,
        });
      }

      // limpiar
      archivoPendiente = null;
      fileInput.value = "";
      inputMsg.value = "";
      document.getElementById("filePreview").style.display = "none";
      return;
    }

    // === CASO 3: enviar texto normal ===
    if (!msg) return;

    const fd = new FormData();
    fd.append("chat_id", window.chat_abierto);
    fd.append("mensaje", msg);

    const res = await fetch(`${window.base_url}api/chat/enviar`, {
      method: "POST",
      body: fd,
    });

    const data = await res.json();

    if (data.status === "ok") {
      socket.emit("nuevoMensaje", {
        chat_id: data.msg.chat_id,
        emisor_id: data.msg.emisor_id,
        mensaje: data.msg.mensaje,
        tipo: data.msg.tipo,
        archivo_url: data.msg.archivo_url,
        fecha: data.msg.fecha,
        participantes: data.msg.participantes,
      });

      inputMsg.value = "";
    }
  }

  // =======================
  // RECIBIR MENSAJE
  // =======================
  socket.on("mensajeRecibido", (msg) => {
    if (msg.chat_id != window.chat_abierto) return;

    // SI ES MENSAJE DEL OTRO → MANDO MARCAR COMO LEÍDO
    if (msg.emisor_id != window.usuario_id) {
      socket.emit("marcarLeido", {
        chatId: window.chat_abierto,
        lector_id: window.usuario_id,
      });
    }

    const cont = document.getElementById("chatMessages");
    const div = document.createElement("div");
    div.classList.add("msg");
    div.classList.add(msg.emisor_id == window.usuario_id ? "me" : "other");
    div.innerHTML = renderMensajeHTML(msg);
    cont.appendChild(div);
    cont.scrollTop = cont.scrollHeight;
  });

  // =====================================
  //  ADJUNTAR ARCHIVO
  // =====================================
  const btnFile = document.getElementById("btnFile");
  const fileInput = document.getElementById("fileInput");

  btnFile?.addEventListener("click", () => fileInput.click());

  fileInput?.addEventListener("change", () => {
    if (!fileInput.files.length) return;

    archivoPendiente = fileInput.files[0];

    // Mostrar preview
    const preview = document.getElementById("filePreview");
    const nameDiv = document.getElementById("filePreviewName");

    nameDiv.innerHTML = `<i class="fas fa-paperclip"></i> ${archivoPendiente.name}`;
    preview.style.display = "flex";
  });

  document.getElementById("btnCancelFile")?.addEventListener("click", () => {
    archivoPendiente = null;
    fileInput.value = "";
    document.getElementById("filePreview").style.display = "none";
  });

  document
    .getElementById("btnSendFile")
    ?.addEventListener("click", async () => {
      if (!archivoPendiente) return;

      const fd = new FormData();
      fd.append("chat_id", window.chat_abierto);
      fd.append("archivo", archivoPendiente);

      const res = await fetch(`${window.base_url}api/chat/enviarArchivo`, {
        method: "POST",
        body: fd,
      });

      const data = await res.json();

      if (data.status === "ok") {
        socket.emit("nuevoMensaje", {
          chat_id: data.msg.chat_id,
          emisor_id: data.msg.emisor_id,
          mensaje: data.msg.mensaje,
          tipo: data.msg.tipo,
          archivo_url: data.msg.archivo_url,
          fecha: data.msg.fecha,
          participantes: data.msg.participantes,
        });
      }

      archivoPendiente = null;
      fileInput.value = "";
      document.getElementById("filePreview").style.display = "none";
    });
});
