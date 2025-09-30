document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById("sidebarToggle");
  const sidebar = document.getElementById("sidebar");
  const content = document.querySelector(".content-dark");

  if (!toggleBtn || !sidebar || !content) return;

  // Estado inicial: sidebar colapsado
  let isCollapsed = true;

  // 🔹 Aplica el estado inicial
  sidebar.classList.add("collapsed");
  content.classList.add("collapsed");
  toggleBtn.classList.remove("active"); // inicia como ☰

  toggleBtn.addEventListener("click", () => {
    isCollapsed = !isCollapsed;

    // 🔸 Alterna clases según el estado
    sidebar.classList.toggle("collapsed", isCollapsed);
    content.classList.toggle("collapsed", isCollapsed);

    // 🔸 Cambia animación del botón
    toggleBtn.classList.toggle("active", !isCollapsed);
  });
});
