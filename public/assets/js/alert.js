/* ======================================================
   🔔 SweetAlert2 Mixins Globales - Plataforma Educativa
   ====================================================== */

// 💡 Configuración base de SweetAlert
const baseSwal = Swal.mixin({
  background: "#1e1f25",
  color: "#f9f9fb",
  timerProgressBar: true,
  heightAuto: false,
  showConfirmButton: false,
  didOpen: () => {
    Swal.showLoading();
    const container = Swal.getContainer();
    container.style.pointerEvents = "none"; // permitir clics fuera
  },
  willClose: () => {
    document.body.classList.remove("swal2-shown");
    document.querySelectorAll(".swal2-container").forEach((el) => el.remove());
  },
});

// ✅ Éxito
window.Swal.fireSuccess = (msg = "Operación completada", title = "Éxito") => {
  baseSwal.fire({
    icon: "success",
    title: title,
    html: `<i class="fa fa-check-circle"></i> ${msg}`,
    timer: 2000,
  });
};

// ⚠️ Advertencia / Info
window.Swal.fireInfo = (msg = "Acción completada", title = "Información") => {
  baseSwal.fire({
    icon: "info",
    title: title,
    html: `<i class="fa fa-info-circle"></i> ${msg}`,
    timer: 2000,
  });
};

// ❌ Error
window.Swal.fireError = (msg = "Ocurrió un error", title = "Error") => {
  baseSwal.fire({
    icon: "error",
    title: title,
    html: `<i class="fa fa-times-circle"></i> ${msg}`,
    timer: 2200,
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
