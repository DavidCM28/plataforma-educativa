/* ======================================================
   🔔 SISTEMA DE ALERTAS Y CONFIRMACIONES NATIVAS
   ====================================================== */

/* 🔸 Mostrar alerta tipo toast */
function mostrarAlerta(mensaje, tipo = "info", duracion = 3000) {
  const contenedor = document.getElementById("alertContainer");
  if (!contenedor) return;

  const alerta = document.createElement("div");
  alerta.className = `alert ${tipo}`;
  const iconos = {
    success: "fas fa-check-circle",
    error: "fas fa-times-circle",
    warning: "fas fa-exclamation-triangle",
    info: "fas fa-info-circle",
  };

  alerta.innerHTML = `<i class="${
    iconos[tipo] || iconos.info
  }"></i> ${mensaje}`;
  contenedor.appendChild(alerta);

  // Desaparición automática
  setTimeout(() => {
    alerta.style.opacity = "0";
    alerta.style.transform = "translateX(30px)";
    setTimeout(() => alerta.remove(), 300);
  }, duracion);
}

/* 🔸 Modal de confirmación (actualizado y compatible con CSS) */
function mostrarConfirmacion(titulo, mensaje, onAceptar, onCancelar) {
  const modal = document.getElementById("confirmModal");
  if (!modal) return console.warn("No se encontró #confirmModal");

  // Mostrar contenido
  document.getElementById("confirmTitle").innerHTML = titulo;
  document.getElementById("confirmMessage").innerHTML = mensaje;
  modal.classList.remove("hidden");

  const btnAceptar = document.getElementById("confirmAceptar");
  const btnCancelar = document.getElementById("confirmCancelar");

  // Cierre modal
  const cerrar = () => modal.classList.add("hidden");

  btnAceptar.onclick = () => {
    cerrar();
    if (onAceptar) onAceptar();
  };

  btnCancelar.onclick = () => {
    cerrar();
    if (onCancelar) onCancelar();
  };

  // Cerrar con clic fuera del modal
  modal.onclick = (e) => {
    if (e.target === modal) cerrar();
  };
}

/* ======================================================
   🚪 Cerrar sesión de usuario
   ====================================================== */
function cerrarSesion() {
  mostrarConfirmacion(
    "Cerrar sesión",
    "¿Deseas cerrar tu sesión actual?",
    () => {
      mostrarAlerta("Cerrando sesión...", "info");
      setTimeout(() => {
        localStorage.removeItem("usuario");
        localStorage.removeItem("privateKey");
        mostrarAlerta("Sesión cerrada correctamente", "success");
        setTimeout(() => (window.location.href = "login.html"), 800);
      }, 500);
    }
  );
}

/* ======================================================
   📤 Envío AJAX de formularios dentro de modales
   ====================================================== */
async function ajaxSubmitForm(form) {
  const action = form.getAttribute("action") || window.location.href;
  const method = (form.getAttribute("method") || "POST").toUpperCase();
  const btn = form.querySelector("[type='submit']");
  const formData = new FormData(form);
  btn?.setAttribute("disabled", "disabled");

  try {
    const res = await fetch(action, {
      method,
      body: formData,
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });

    const ct = res.headers.get("Content-Type") || "";
    if (ct.includes("application/json")) {
      const json = await res.json();

      if (!res.ok || json.ok === false) {
        mostrarAlerta(json.message || "Error al guardar", "error");
        return;
      }

      mostrarAlerta(json.message || "Guardado correctamente", "success");
      form.reset();
      cerrarModalActivo();
      return;
    }

    if (res.ok) {
      mostrarAlerta("Guardado correctamente", "success");
      form.reset();
      cerrarModalActivo();
    } else {
      mostrarAlerta("Error al guardar datos", "error");
    }
  } catch (err) {
    console.error(err);
    mostrarAlerta("Error de red o servidor", "error");
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
   🗑️ Eliminar elementos con confirmación
   ====================================================== */
document.addEventListener("click", (e) => {
  const btn = e.target.closest("[data-delete-url]");
  if (!btn) return;

  e.preventDefault();
  e.stopPropagation();

  const url = btn.getAttribute("data-delete-url");
  const method = (btn.getAttribute("data-method") || "DELETE").toUpperCase();
  const tipo = btn.dataset.type || "elemento";

  mostrarConfirmacion(
    `¿Eliminar ${tipo}?`,
    "No podrás revertir esta acción.",
    async () => {
      btn.disabled = true;

      try {
        const res = await fetch(url, {
          method,
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN":
              document.querySelector('meta[name="csrf-token"]')?.content || "",
          },
        });

        if (res.ok) {
          let item =
            btn.closest("tr, li, .item, .card, [data-id]") || btn.parentElement;

          if (item) {
            item.style.transition = "all 0.3s ease";
            item.style.opacity = "0";
            item.style.transform = "translateX(-100%)";
            setTimeout(() => {
              item.remove();
              mostrarAlerta(`${tipo} eliminado correctamente`, "success");
            }, 300);
          } else {
            mostrarAlerta(`${tipo} eliminado`, "success");
            setTimeout(() => window.location.reload(), 800);
          }
        } else {
          mostrarAlerta("No se pudo eliminar el elemento", "error");
        }
      } catch (err) {
        console.error(err);
        mostrarAlerta("Error de red o servidor", "error");
      } finally {
        btn.disabled = false;
      }
    }
  );
});

/* ======================================================
   🧯 Cierre de modales residuales
   ====================================================== */
function cerrarModalActivo() {
  document.querySelectorAll(".modal.show").forEach((m) => {
    m.classList.remove("show");
    m.style.display = "none";
  });
  document.querySelectorAll(".modal-backdrop").forEach((b) => b.remove());
  document.body.classList.remove("modal-open");
  document.body.style.removeProperty("overflow");
}

const modalObserver = new MutationObserver(() => {
  if (!document.querySelector(".modal.show")) {
    document.querySelectorAll(".modal-backdrop").forEach((b) => b.remove());
    document.body.classList.remove("modal-open");
  }
});
modalObserver.observe(document.body, { childList: true, subtree: true });
