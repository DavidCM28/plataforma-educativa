document.addEventListener("DOMContentLoaded", () => {
  console.warn("HEADER usuario_id:", window.usuario_id);
  async function cargarNoLeidos() {
    try {
      const res = await fetch(`${window.base_url}api/chat/unreadCount`);
      const data = await res.json();

      const badge = document.getElementById("chatBadge");
      const total = data.total ?? 0;

      if (total > 0) {
        badge.textContent = total;
        badge.style.display = "inline-block";
      } else {
        badge.textContent = "";
        badge.style.display = "none";
      }
    } catch (e) {
      console.error("Error al obtener no leídos:", e);
    }
  }

  cargarNoLeidos();

  // === Sidebar ===
  const sidebar = document.getElementById("sidebar");
  const content = document.querySelector(".content-dark");
  const toggleBtn = document.getElementById("sidebarToggle");

  if (sidebar && content) {
    // 🔹 Colapsado por defecto
    sidebar.classList.add("collapsed");
    content.classList.add("collapsed");

    // 🔹 Alternar estado
    if (toggleBtn) {
      toggleBtn.addEventListener("click", (e) => {
        e.preventDefault();
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
      });
    }
  }

  // === Menú de perfil ===
  const avatar = document.getElementById("profileAvatar");
  const menu = document.getElementById("profileMenu");

  if (avatar && menu) {
    avatar.addEventListener("click", (e) => {
      e.stopPropagation();
      menu.classList.toggle("active");
    });

    document.addEventListener("click", (e) => {
      if (!menu.contains(e.target) && e.target !== avatar) {
        menu.classList.remove("active");
      }
    });
  }

  // === (Opcional) Recordar estado del sidebar ===
  const saved = localStorage.getItem("sidebar-collapsed");
  if (saved === "true") sidebar.classList.add("collapsed");

  toggleBtn?.addEventListener("click", () => {
    const isCollapsed = sidebar.classList.contains("collapsed");
    localStorage.setItem("sidebar-collapsed", isCollapsed);
  });

  // === Confirmar cierre de sesión (SweetAlert2 con tema del dashboard) ===
  const logoutLink = document.querySelector('.profile-menu a[href*="logout"]');
  if (logoutLink) {
    logoutLink.addEventListener("click", function (e) {
      e.preventDefault(); // Evita salir directamente

      Swal.fire({
        title: "¿Deseas cerrar sesión?",
        text: "Se cerrará tu sesión actual y deberás volver a iniciar sesión.",
        icon: "warning",
        background: "#181a20", // Fondo oscuro como dashboard
        color: "#f9f9fb",
        confirmButtonText: "Sí, cerrar sesión",
        cancelButtonText: "Cancelar",
        showCancelButton: true,
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
          popup: "rounded-xl shadow-lg border border-[rgba(255,158,100,0.2)]",
          title: "text-lg font-semibold text-[var(--accent)]",
          htmlContainer: "text-[var(--text-muted)]",
          confirmButton:
            "px-4 py-2 rounded-lg bg-[var(--primary)] hover:bg-[var(--accent)] font-semibold text-black transition-all duration-200",
          cancelButton:
            "px-4 py-2 rounded-lg bg-[rgba(255,255,255,0.1)] text-[var(--text)] hover:bg-[rgba(255,255,255,0.2)] font-semibold transition-all duration-200",
        },
      }).then((result) => {
        if (result.isConfirmed) {
          // Cierra el menú si estaba abierto
          const profileMenu = document.getElementById("profileMenu");
          profileMenu?.classList.remove("active");

          // Redirige al logout
          window.location.href = logoutLink.href;
        }
      });
    });
  }

  const chatToggle = document.getElementById("chatToggle");
  const chatDropdown = document.getElementById("chatDropdown");

  chatToggle.addEventListener("click", (e) => {
    e.stopPropagation();
    chatDropdown.classList.toggle("active");

    if (chatDropdown.classList.contains("active")) {
      cargarChatsRecientes();
    }
  });

  document.addEventListener("click", (e) => {
    if (!chatDropdown.contains(e.target) && e.target !== chatToggle) {
      chatDropdown.classList.remove("active");
    }
  });

  async function cargarChatsRecientes() {
    try {
      const res = await fetch(`${window.base_url}api/chat/recientes`);
      const data = await res.json();

      const cont = document.getElementById("chatList");
      cont.innerHTML = "";

      if (!data.data || data.data.length === 0) {
        cont.innerHTML = `<p class="placeholder">No hay mensajes recientes</p>`;
        return;
      }

      data.data.forEach((chat) => {
        const usuario = chat.con;
        const foto =
          usuario.foto && usuario.foto !== ""
            ? usuario.foto
            : window.base_url + "assets/img/user-default.jpg";

        // Determinar preview según tipo
        let preview = chat.ultimo_mensaje;

        if (chat.tipo_mensaje === "archivo") {
          const filename = chat.ultimo_mensaje || "Archivo";
          preview =
            filename.endsWith(".jpg") ||
            filename.endsWith(".png") ||
            filename.endsWith(".jpeg")
              ? "📷 Imagen"
              : "📎 " + filename;
        }

        const item = document.createElement("div");
        item.classList.add("chat-item");
        item.dataset.chat = chat.chat_id;

        item.innerHTML = `
                <img src="${foto}">
                <div class="chat-info">
                    <strong>${usuario.nombre} ${usuario.apellido_paterno}</strong>
                    <small>${preview}</small>
                </div>
            `;

        // Abrir el chat completo
        item.addEventListener("click", () => {
          window.location.href = `${window.base_url}api/chat/mensajes/${chat.chat_id}`;
        });

        cont.appendChild(item);
      });
    } catch (error) {
      console.error("Error cargando chats:", error);
    }
  }

  // ================================
  // 🔊 SONIDO DE NOTIFICACIÓN
  // ================================
  const sonidoNotif = new Audio(
    `${window.base_url}assets/sounds/notificacion.mp3`
  );
  sonidoNotif.volume = 0.5; // opcional, ajusta volumen

  // ================================
  //       SOCKET.IO — HEADER
  // ================================

  const socket = io("http://localhost:3001");

  // Registrar usuario en socket
  if (window.usuario_id) {
    socket.emit("registrarUsuario", window.usuario_id);
  }

  function incrementarBadge() {
    const badge = document.getElementById("chatBadge");

    // animación (esto sí queda)
    badge.style.transform = "scale(1.3)";
    setTimeout(() => (badge.style.transform = "scale(1)"), 200);

    // recargar del servidor
    cargarNoLeidos();

    badge.style.display = "inline-block";
  }

  socket.on("mensajeRecibido", (msg) => {
    console.log("🔥 HEADER RECIBE:", msg);

    const noEsElChatAbierto =
      !window.chat_abierto || window.chat_abierto != msg.chat_id;

    if (noEsElChatAbierto) {
      incrementarBadge();
      cargarChatsRecientes();

      // 🔊 reproducir sonido
      try {
        sonidoNotif.play();
      } catch (e) {
        console.warn("No se pudo reproducir el sonido:", e);
      }

      return;
    }

    // Si estoy dentro del chat, no suena ni incrementa
  });
});
