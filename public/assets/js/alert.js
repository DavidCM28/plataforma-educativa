/* ======================================================
   🔔 SweetAlert2 Mixins Globales - Plataforma Educativa
   ====================================================== */

const baseSwal = Swal.mixin({
  background: "#1e1f25",
  color: "#f9f9fb",
  heightAuto: false,
  allowOutsideClick: true,
  allowEscapeKey: true,
  customClass: { popup: "swal-custom" },
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

/* ======================================================
   🧼 Limpiezas y utilidades (Swal + Modals)
   ====================================================== */

// Elimina contenedores Swal fantasmas
function cleanupSwalGhosts() {
  document.querySelectorAll(".swal2-container").forEach((c) => {
    if (c.style.display === "none" || c.classList.contains("swal2-hide")) {
      c.remove();
    }
  });
}

// Cierra todos los modals (Bootstrap o genéricos) y limpia backdrops
function closeAllModals() {
  // Bootstrap 5 si está disponible
  const hasBs = typeof bootstrap !== "undefined" && bootstrap?.Modal;
  document.querySelectorAll(".modal.show, .modal[aria-modal='true']").forEach((m) => {
    try {
      if (hasBs) {
        const inst = bootstrap.Modal.getOrCreateInstance(m);
        inst.hide();
      } else {
        // Fallback sin Bootstrap JS
        m.classList.remove("show");
        m.setAttribute("aria-hidden", "true");
        m.style.display = "none";
      }
    } catch (e) {}
  });

  // Backdrops huérfanos
  document.querySelectorAll(".modal-backdrop").forEach((b) => b.remove());

  // Clases/z-index del body
  document.body.classList.remove("modal-open", "swal2-shown");
  document.body.style.removeProperty("padding-right");
  document.body.style.removeProperty("overflow");
}

// Limpieza automática de Swal
document.addEventListener("click", cleanupSwalGhosts);
window.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    Swal.close();
    document.body.classList.remove("swal2-shown");
    document.body.removeAttribute("style");
  }
});
window.addEventListener("click", cleanupSwalGhosts);

/* ======================================================
   🚀 AJAX autosubmit para formularios dentro de modals
   ======================================================

   - Sin tocar tu HTML: todos los <form> dentro de .modal se enviarán por fetch (AJAX)
     a menos que lleven data-ajax="false".
   - Si el backend devuelve JSON con { ok, message, html, refresh }
       * ok=true → éxito: SweetAlert, cierra modal
       * html + (data-refresh="#selector" en el form) → reemplaza ese contenedor con html
   - Si no es JSON pero la respuesta es 2xx → muestra éxito genérico y cierra modal
*/

async function ajaxSubmitForm(form) {
  const action = form.getAttribute("action") || window.location.href;
  const method = (form.getAttribute("method") || "POST").toUpperCase();
  const btn = form.querySelector("[type='submit']");
  const refreshSelector = form.dataset.refresh || form.getAttribute("data-refresh"); // opcional
  const formData = new FormData(form);

  btn?.setAttribute("disabled", "disabled");

  try {
    const res = await fetch(action, { method, body: formData, headers: { "X-Requested-With": "XMLHttpRequest" } });

    const contentType = res.headers.get("Content-Type") || "";
    let payload = null;

    if (contentType.includes("application/json")) {
      payload = await res.json();

      if (!res.ok || payload.ok === false) {
        Swal.fireError(payload?.message || "No se pudo guardar");
        return;
      }

      // Actualización parcial opcional (requiere que tu backend envíe html)
      if (payload.html && refreshSelector) {
        const target = document.querySelector(refreshSelector);
        if (target) target.innerHTML = payload.html;
      }

      Swal.fireSuccess(payload?.message || "Guardado correctamente");
      form.reset();
      closeAllModals();
      return;
    }

    // Si no es JSON, intenta como texto (fragmento HTML)
    const text = await res.text();
    if (res.ok) {
      if (refreshSelector && text.trim()) {
        const target = document.querySelector(refreshSelector);
        if (target) target.innerHTML = text;
      }
      Swal.fireSuccess("Guardado correctamente");
      form.reset();
      closeAllModals();
    } else {
      Swal.fireError("No se pudo guardar");
    }
  } catch (err) {
    console.error(err);
    Swal.fireError("Error de red o servidor");
  } finally {
    btn?.removeAttribute("disabled");
  }
}

// Delegación global: cualquier submit dentro de .modal (salvo data-ajax="false")
document.addEventListener("submit", (e) => {
  const form = e.target.closest("form");
  if (!form) return;

  const inModal = form.closest(".modal");
  const wantsAjax = form.dataset.ajax !== "false"; // por defecto SÍ es AJAX

  if (inModal && wantsAjax) {
    e.preventDefault();
    ajaxSubmitForm(form);
  }
});

/* ======================================================
   🧯 Failsafes visuales (zombies y focus lock)
   ====================================================== */
const modalObserver = new MutationObserver(() => {
  // Si no hay modales abiertos, limpia backdrops y estados
  const anyOpen = document.querySelector(".modal.show");
  if (!anyOpen) {
    document.querySelectorAll(".modal-backdrop").forEach((b) => b.remove());
    document.body.classList.remove("modal-open");
    document.body.style.removeProperty("overflow");
  }
});
modalObserver.observe(document.body, { childList: true, subtree: true });
