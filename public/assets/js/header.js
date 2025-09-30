// assets/js/global/header.js
document.addEventListener("DOMContentLoaded", () => {
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
});
