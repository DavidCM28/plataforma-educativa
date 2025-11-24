document.addEventListener("DOMContentLoaded", () => {
  // Obtener datos desde la vista
  const wrapper = document.querySelector(".chat-wrapper");
  const chatId = wrapper.dataset.chatId;
  const userId = wrapper.dataset.user;

  const socket = io("http://localhost:3001");

  // Registrar usuario
  socket.emit("registrarUsuario", userId);

  // Unirse al room del chat
  socket.emit("joinChat", chatId);

  // Para evitar aumentar el badge en este chat
  window.chat_abierto = chatId;

  const msgInput = document.getElementById("msgInput");
  const sendBtn = document.getElementById("sendBtn");
  const chatBody = document.getElementById("chatBody");
  const typingIndicator = document.getElementById("typingIndicator");

  // Scroll al final
  function scrollBottom() {
    chatBody.scrollTop = chatBody.scrollHeight;
  }
  scrollBottom();

  // ================================
  //  ENVIAR MENSAJE
  // ================================
  async function enviarMensaje() {
    const texto = msgInput.value.trim();
    if (!texto) return;

    // Enviar a backend PHP
    const res = await fetch(`${window.base_url}api/chat/enviar`, {
      method: "POST",
      body: new FormData(
        Object.assign(new FormData(), {
          chat_id: chatId,
          mensaje: texto,
          tipo: "texto",
        })
      ),
    });

    msgInput.value = "";

    // Emitir por socket
    const data = {
      chat_id: chatId,
      emisor_id: userId,
      mensaje: texto,
      tipo: "texto",
      archivo_url: null,
      fecha: new Date().toISOString().slice(0, 19).replace("T", " "),
    };

    socket.emit("nuevoMensaje", data);
  }

  sendBtn.addEventListener("click", enviarMensaje);
  msgInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") enviarMensaje();
  });

  // ================================
  //    RECIBIR MENSAJE EN TIEMPO REAL
  // ================================
  socket.on("mensajeRecibido", (data) => {
    if (data.chat_id != chatId) return;

    const div = document.createElement("div");
    div.classList.add("msg", data.emisor_id == userId ? "yo" : "otro");

    div.innerHTML = `
      <div class="burbuja">${data.mensaje}</div>
      <span class="hora">${data.fecha.substring(11, 16)}</span>
    `;

    chatBody.appendChild(div);
    scrollBottom();
  });

  // ================================
  //      INDICADOR "ESCRIBIENDO..."
  // ================================
  let typingTimer;
  msgInput.addEventListener("input", () => {
    socket.emit("typing", { chatId, userId });

    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
      socket.emit("stopTyping", { chatId, userId });
    }, 800);
  });

  socket.on("usuarioTyping", (uid) => {
    if (uid != userId) typingIndicator.classList.remove("hidden");
  });

  socket.on("usuarioStopTyping", (uid) => {
    typingIndicator.classList.add("hidden");
  });
});
