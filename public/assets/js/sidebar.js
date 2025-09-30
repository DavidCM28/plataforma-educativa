(function waitForSidebarToggle() {
  const toggleBtn = document.getElementById("sidebarToggle");
  const sidebar = document.getElementById("sidebar");
  const content = document.querySelector(".content");

  // 🟡 Si aún no existe el botón, intenta otra vez en 100 ms
  if (!toggleBtn || !sidebar || !content) {
    console.warn("⏳ Esperando a que cargue el sidebar o el botón...");
    setTimeout(waitForSidebarToggle, 100);
    return;
  }

  console.log("✅ Sidebar inicializado correctamente");

  // 🔹 Colapsado por defecto
  sidebar.classList.add("collapsed");
  content.classList.add("collapsed");

  // 🔹 Alternar al hacer clic
  toggleBtn.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
    content.classList.toggle("collapsed");
  });
})();
