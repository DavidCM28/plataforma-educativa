/* ======================================================
   🔔 SweetAlert2 Mixins Globales - Plataforma Educativa
   ====================================================== */

const baseSwal = Swal.mixin({
  background: "#1e1f25",
  color: "#f9f9fb",
  heightAuto: false,
  allowOutsideClick: true,
  allowEscapeKey: true,
  customClass: {
    popup: "swal-custom",
  },
});

// ✅ Éxito
window.Swal.fireSuccess = (msg = "Operación completada", title = "Éxito") => {
  Swal.fire({
    icon: "success",
    title: title,
    html: `<i class="fa fa-check-circle"></i> ${msg}`,
    timer: 1800,
    showConfirmButton: false,
    background: "#1e1f25",
    color: "#f9f9fb",
    heightAuto: false,
  });
};

// ⚠️ Info
window.Swal.fireInfo = (msg = "Acción completada", title = "Información") => {
  Swal.fire({
    icon: "info",
    title: title,
    html: `<i class="fa fa-info-circle"></i> ${msg}`,
    timer: 2000,
    showConfirmButton: false,
    background: "#1e1f25",
    color: "#f9f9fb",
    heightAuto: false,
  });
};

// ❌ Error
window.Swal.fireError = (msg = "Ocurrió un error", title = "Error") => {
  Swal.fire({
    icon: "error",
    title: title,
    html: `<i class="fa fa-times-circle"></i> ${msg}`,
    timer: 2200,
    showConfirmButton: false,
    background: "#1e1f25",
    color: "#f9f9fb",
    heightAuto: false,
  });
};

// ⚙️ Confirmación
window.Swal.fireConfirm = async (
  title = "¿Estás seguro?",
  text = "No podrás deshacer esto"
) => {
  return await Swal.fire({
    title,
    text,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#ff9e64",
    cancelButtonColor: "#666",
    confirmButtonText: "Sí, continuar",
    cancelButtonText: "Cancelar",
    background: "#1e1f25",
    color: "#f9f9fb",
    heightAuto: false,
  });
};

// 🧹 Limpieza global automática de contenedores SweetAlert fantasma
document.addEventListener("click", () => {
  document.querySelectorAll(".swal2-container").forEach((c) => {
    if (c.style.display === "none" || c.classList.contains("swal2-hide")) {
      c.remove();
    }
  });
});

// 🔄 Forzar reactivación del body si SweetAlert queda atascado
window.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    Swal.close();
    document.body.classList.remove("swal2-shown");
    document.body.removeAttribute("style");
  }
});

// 🔧 Limpieza automática tras cada alerta
window.addEventListener("click", () => {
  document.querySelectorAll(".swal2-container").forEach((el) => {
    const isHidden =
      el.style.display === "none" || el.classList.contains("swal2-hide");
    if (isHidden) el.remove(); // elimina overlays fantasmas
  });
});
