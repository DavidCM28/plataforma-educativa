/* ============================================================
   📰 MÓDULO DE PUBLICACIONES (tipo Teams)
============================================================ */
document.addEventListener("DOMContentLoaded", () => {
  const base_url = window.base_url || "";
  const feed = document.getElementById("feedPublicaciones");
  const formPublicacion = document.getElementById("formPublicacion");
  const asignacionId = feed?.dataset?.asignacion || "";

  async function cargarPublicaciones() {
    if (!feed || !asignacionId) return;
    feed.innerHTML = `<p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando publicaciones...</p>`;
    try {
      const res = await fetch(
        `${base_url}profesor/grupos/publicaciones/${asignacionId}`
      );
      const data = await res.text();
      feed.innerHTML =
        data || `<p class="placeholder">📭 No hay publicaciones aún.</p>`;
      inicializarBotones();
    } catch (err) {
      feed.innerHTML = `<p class="placeholder error">❌ Error al cargar publicaciones: ${err.message}</p>`;
    }
  }

  // 📩 Crear nueva publicación
  formPublicacion?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(formPublicacion);
    try {
      const res = await fetch(
        `${base_url}profesor/grupos/publicar/${asignacionId}`,
        {
          method: "POST",
          body: formData,
        }
      );
      const data = await res.json();
      if (data.success) {
        mostrarAlerta("📢 Publicación creada correctamente", "success");
        formPublicacion.reset();
        cargarPublicaciones();
      } else {
        mostrarAlerta(data.error || "❌ Error al publicar.", "error");
      }
    } catch (error) {
      mostrarAlerta("❌ Fallo de red: " + error.message, "error");
    }
  });

  // 🧩 Inicializa eventos de edición y eliminación
  // 🧩 Inicializa eventos de edición y eliminación
  function inicializarBotones() {
    /* =======================
     🗑️ ELIMINAR PUBLICACIÓN
  ======================= */
    document.querySelectorAll(".btn-eliminar-publicacion").forEach((btn) => {
      btn.addEventListener("click", () => {
        const id = btn.dataset.id;

        mostrarConfirmacion(
          "Eliminar publicación",
          "¿Deseas eliminar esta publicación? Esta acción no se puede deshacer.",
          async () => {
            try {
              const res = await fetch(
                `${base_url}profesor/grupos/eliminar-publicacion/${id}`,
                { method: "DELETE" }
              );
              const data = await res.json();

              if (data.success) {
                // 🔥 Animación suave de salida
                const card = btn.closest(".publicacion");
                card.style.transition = "all 0.3s ease";
                card.style.opacity = "0";
                card.style.transform = "translateX(-30px)";
                setTimeout(() => {
                  card.remove();
                  mostrarAlerta(
                    "🗑️ Publicación eliminada correctamente",
                    "success"
                  );
                }, 300);
              } else {
                mostrarAlerta(
                  data.error || "❌ No se pudo eliminar la publicación",
                  "error"
                );
              }
            } catch (err) {
              console.error(err);
              mostrarAlerta("❌ Error al eliminar: " + err.message, "error");
            }
          },
          () => {
            mostrarAlerta("Acción cancelada", "info");
          }
        );
      });
    });

    /* =======================
     ✏️ EDITAR PUBLICACIÓN INLINE
  ======================= */
    /* =======================
       ✏️ EDITAR PUBLICACIÓN INLINE
    ======================= */
    document.querySelectorAll(".btn-editar-publicacion").forEach((btn) => {
      btn.addEventListener("click", () => {
        const card = btn.closest(".publicacion");
        const id = btn.dataset.id;
        const contenedor = card.querySelector(".publicacion-contenido");
        const textoDiv = contenedor.querySelector(".contenido-texto");

        if (!textoDiv) return;

        const textoOriginal = textoDiv.innerText.trim();

        // Evitar múltiples ediciones simultáneas
        if (contenedor.querySelector(".form-editar")) return;

        const form = document.createElement("form");
        form.className = "form-editar";
        form.innerHTML = `
          <textarea class="editar-textarea">${textoOriginal}</textarea>
          <div class="editar-acciones">
            <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar</button>
            <button type="button" class="btn-cancelar"><i class="fas fa-times"></i> Cancelar</button>
          </div>
        `;

        // Reemplazar solo el contenido de texto
        textoDiv.replaceWith(form);

        const textarea = form.querySelector(".editar-textarea");
        textarea.focus();

        // Cancelar
        form.querySelector(".btn-cancelar").addEventListener("click", () => {
          form.replaceWith(textoDiv);
        });

        // Guardar
        form.addEventListener("submit", async (e) => {
          e.preventDefault();
          const nuevoTexto = textarea.value.trim();
          if (!nuevoTexto || nuevoTexto === textoOriginal) {
            form.replaceWith(textoDiv);
            return;
          }

          const formData = new FormData();
          formData.append("contenido", nuevoTexto);

          try {
            const res = await fetch(
              `${base_url}profesor/grupos/editar-publicacion/${id}`,
              {
                method: "POST",
                body: formData,
              }
            );
            const data = await res.json();

            if (data.success) {
              mostrarAlerta("✅ Publicación actualizada", "success");
              const nuevoDiv = document.createElement("div");
              nuevoDiv.className = "contenido-texto";
              nuevoDiv.innerHTML = nuevoTexto.replace(/\n/g, "<br>");
              form.replaceWith(nuevoDiv);
            } else {
              mostrarAlerta(data.error || "❌ Error al editar.", "error");
              form.replaceWith(textoDiv);
            }
          } catch (err) {
            mostrarAlerta("❌ Fallo al editar: " + err.message, "error");
            form.replaceWith(textoDiv);
          }
        });
      });
    });
  }

  // 📥 Carga inicial
  if (document.querySelector(".tab-btn.active[data-tab='inicio']")) {
    cargarPublicaciones();
  }

  // 🔁 Recarga al abrir pestaña "Inicio"
  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (btn.dataset.tab === "inicio") cargarPublicaciones();
    });
  });
});
