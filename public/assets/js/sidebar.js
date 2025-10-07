document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById("sidebarToggle");
  const sidebar = document.getElementById("sidebar");
  const content = document.querySelector(".content-dark");

  if (!toggleBtn || !sidebar || !content) return;

  let isCollapsed = true;
  sidebar.classList.add("collapsed");
  content.classList.add("collapsed");
  toggleBtn.classList.remove("active");

  toggleBtn.addEventListener("click", () => {
    isCollapsed = !isCollapsed;
    sidebar.classList.toggle("collapsed", isCollapsed);
    content.classList.toggle("collapsed", isCollapsed);
    toggleBtn.classList.toggle("active", !isCollapsed);
  });

  // 🔹 Submenús
  document.querySelectorAll(".menu-toggle").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const group = btn.closest(".menu-group");

      // En colapsado → muestra flyout temporal
      if (sidebar.classList.contains("collapsed")) {
        e.preventDefault();
        document
          .querySelectorAll(".menu-group")
          .forEach((g) => g.classList.remove("hovered"));
        group.classList.add("hovered");
      } else {
        group.classList.toggle("active");
      }
    });
  });

  // 🔹 Cierra flyouts si se hace clic fuera
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".menu-group")) {
      document
        .querySelectorAll(".menu-group.hovered")
        .forEach((g) => g.classList.remove("hovered"));
    }
  });
});
