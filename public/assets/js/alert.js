/* ======================================================
   🔔 SweetAlert2 Global - Versión Final
   ====================================================== */
const baseSwal = Swal.mixin({
  background: "#1e1f25",
  color: "#f9f9fb",
  heightAuto: false,
  allowOutsideClick: true,
  allowEscapeKey: true,
  customClass: { popup: "swal-custom" },
});

/* ======================================================
   🧹 Limpieza global de SweetAlerts
   ====================================================== */
function forceCloseSwal() {
  try { Swal.close(); } catch (e) {}
  document.querySelectorAll(".swal2-container").forEach((c) => c.remove());
  document.body.classList.remove("swal2-shown");
  document.body.removeAttribute("style");
}

// Cerrar al hacer clic en botones del alert
document.addEventListener("click", (ev) => {
  if (ev.target.closest(".swal2-confirm, .swal2-cancel, .swal2-close")) {
    forceCloseSwal();
  }
}, true);

/* ======================================================
   🧰 Helper de alertas base
   ====================================================== */
function showAlert({ icon, title, html }) {
  forceCloseSwal();
  const p = Swal.fire({
    icon, title, html,
    showConfirmButton: true,
    confirmButtonText: "Entendido",
    confirmButtonColor: "#ff9e64",
    timer: 5000,
    timerProgressBar: true,
    background: "#1e1f25",
    color: "#f9f9fb",
    heightAuto: false,
    willClose: forceCloseSwal,
  });
  p.then(forceCloseSwal);
  setTimeout(forceCloseSwal, 5200);
}

/* ======================================================
   ✅ API global
   ====================================================== */
window.Swal.fireSuccess = (msg = "Operación completada", title = "Éxito") =>
  showAlert({ icon: "success", title, html: `<i class="fa fa-check-circle"></i> ${msg}` });

window.Swal.fireInfo = (msg = "Acción completada", title = "Información") =>
  showAlert({ icon: "info", title, html: `<i class="fa fa-info-circle"></i> ${msg}` });

window.Swal.fireWarning = (msg = "Revisa la información", title = "Aviso") =>
  showAlert({ icon: "warning", title, html: `<i class="fa fa-exclamation-triangle"></i> ${msg}` });

window.Swal.fireError = (msg = "Ocurrió un error", title = "Error") =>
  showAlert({ icon: "error", title, html: `<i class="fa fa-times-circle"></i> ${msg}` });

window.Swal.fireConfirm = async (title = "¿Estás seguro?", text = "No podrás revertirlo") => {
  forceCloseSwal();
  return await Swal.fire({
    title, text, icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#ff9e64",
    cancelButtonColor: "#666",
    confirmButtonText: "Sí, continuar",
    cancelButtonText: "Cancelar",
    background: "#1e1f25",
    color: "#f9f9fb",
    heightAuto: false,
    willClose: forceCloseSwal,
  });
};

/* ======================================================
   🧼 Cierre y limpieza de modales
   ====================================================== */
function closeAllModals() {
  const hasBs = typeof bootstrap !== "undefined" && bootstrap?.Modal;
  document.querySelectorAll(".modal.show, .modal[aria-modal='true']").forEach((m) => {
    try {
      if (hasBs) bootstrap.Modal.getOrCreateInstance(m).hide();
      else { m.classList.remove("show"); m.setAttribute("aria-hidden", "true"); m.style.display = "none"; }
    } catch (e) {}
  });
  document.querySelectorAll(".modal-backdrop").forEach((b) => b.remove());
  document.body.classList.remove("modal-open", "swal2-shown");
  document.body.style.removeProperty("overflow");
}

/* ======================================================
   🚀 Formularios AJAX dentro de modales
   ====================================================== */
async function ajaxSubmitForm(form) {
  const action = form.getAttribute("action") || window.location.href;
  const method = (form.getAttribute("method") || "POST").toUpperCase();
  const btn = form.querySelector("[type='submit']");
  const formData = new FormData(form);
  btn?.setAttribute("disabled", "disabled");

  try {
    const res = await fetch(action, { method, body: formData, headers: { "X-Requested-With": "XMLHttpRequest" } });
    const ct = res.headers.get("Content-Type") || "";

    if (ct.includes("application/json")) {
      const json = await res.json();
      const total = Number(json?.totalPonderacion || 0);
      if (isNaN(total)) json.totalPonderacion = 0;

      if (total >= 100) {
        Swal.fireWarning("Ya alcanzaste el límite de ponderación (100%)");
        return;
      }

      if (!res.ok || json.ok === false) {
        Swal.fireError(json?.message || "No se pudo guardar");
        return;
      }

      Swal.fireSuccess(json?.message || "Guardado correctamente");
      form.reset();
      closeAllModals();
      return;
    }

    if (res.ok) {
      Swal.fireSuccess("Guardado correctamente");
      form.reset();
      closeAllModals();
    } else Swal.fireError("No se pudo guardar");
  } catch (err) {
    console.error(err);
    Swal.fireError("Error de red o servidor");
  } finally {
    btn?.removeAttribute("disabled");
  }
}

document.addEventListener("submit", (e) => {
  const form = e.target.closest("form");
  if (!form) return;
  const inModal = form.closest(".modal");
  const wantsAjax = form.dataset.ajax !== "false";
  if (inModal && wantsAjax) {
    e.preventDefault();
    ajaxSubmitForm(form);
  }
});

/* ======================================================
   🗑️ Eliminar criterios o ponderaciones - VERSIÓN DEBUG
   ====================================================== */
document.addEventListener("click", async (e) => {
  const btn = e.target.closest("[data-delete-url]");
  if (!btn) return;

  console.log("Botón de eliminar clickeado", btn);
  
  e.preventDefault();
  e.stopPropagation();

  const url = btn.getAttribute("data-delete-url");
  const method = (btn.getAttribute("data-method") || "DELETE").toUpperCase();
  const tipo = "ponderación";

  console.log("URL:", url, "Método:", method);

  const ok = await Swal.fireConfirm(`¿Eliminar ${tipo}?`, "No podrás revertirlo");
  if (!ok.isConfirmed) return;

  btn.setAttribute("disabled", "disabled");

  try {
    console.log("Enviando petición DELETE...");
    const res = await fetch(url, {
      method,
      headers: { 
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
    });

    console.log("Respuesta recibida:", res.status, res.statusText);

    if (res.ok) {
      console.log("Eliminación exitosa, buscando elemento para remover...");
      
      // Buscar el elemento contenedor de manera más agresiva
      let item = btn.closest('tr') || 
                 btn.closest('li') || 
                 btn.closest('.ponderacion-item') || 
                 btn.closest('.item') || 
                 btn.closest('.card') || 
                 btn.closest('[data-id]') ||
                 btn.parentElement;

      console.log("Elemento encontrado para eliminar:", item);

      if (item) {
        // Animación de fade out
        item.style.transition = 'all 0.3s ease';
        item.style.opacity = '0';
        item.style.transform = 'translateX(-100%)';
        
        setTimeout(() => {
          item.remove();
          console.log("Elemento removido del DOM");
          Swal.fireSuccess("Ponderación eliminada correctamente");
        }, 300);
      } else {
        console.log("No se encontró el elemento, recargando página...");
        Swal.fireSuccess("Ponderación eliminada correctamente").then(() => {
          window.location.reload();
        });
      }
    } else {
      console.error("Error en la respuesta:", res.status);
      Swal.fireError("No se pudo eliminar la ponderación. Código: " + res.status);
    }
  } catch (err) {
    console.error("Error en fetch:", err);
    Swal.fireError("Error de red o servidor: " + err.message);
  } finally {
    btn.removeAttribute("disabled");
  }
});

/* ======================================================
   🧯 Limpieza de modales residuales
   ====================================================== */
const modalObserver = new MutationObserver(() => {
  const anyOpen = document.querySelector(".modal.show");
  if (!anyOpen) {
    document.querySelectorAll(".modal-backdrop").forEach((b) => b.remove());
    document.body.classList.remove("modal-open");
    document.body.style.removeProperty("overflow");
  }
});
modalObserver.observe(document.body, { childList: true, subtree: true });