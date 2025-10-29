window.ProyectosUI = {
  inicializar(asignacionId) {
    const lista = document.getElementById("listaProyectos");
    const modal = document.getElementById("modalProyecto");
    const form = document.getElementById("formProyecto");
    const btnNuevo = document.getElementById("btnNuevoProyecto");
    const inputArchivos = document.getElementById("archivoProyecto");
    const previewArchivos = document.getElementById("previewArchivosProyecto");

    if (!lista || !modal || !form) {
      console.warn(
        "⚠️ No se encontraron elementos del módulo de proyectos todavía."
      );
      return;
    }

    // ============================================================
    // 🚀 Inicialización
    // ============================================================
    cargarProyectos();

    // ============================================================
    // 📋 Cargar lista de proyectos
    // ============================================================
    async function cargarProyectos() {
      lista.innerHTML = `<p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando proyectos...</p>`;

      try {
        const res = await fetch(
          `${window.base_url}profesor/grupos/listar-proyectos/${asignacionId}`
        );
        const proyectos = await res.json();

        if (!Array.isArray(proyectos) || proyectos.length === 0) {
          lista.innerHTML = `<p class="placeholder">No hay proyectos registrados.</p>`;
          return;
        }

        renderProyectos(proyectos);
      } catch (err) {
        lista.innerHTML = `<p class="error">❌ Error al cargar proyectos: ${err.message}</p>`;
      }
    }

    // ============================================================
    // 🧱 Renderizar tarjetas de proyectos
    // ============================================================
    function renderProyectos(proyectos) {
      lista.innerHTML = "";

      proyectos.forEach((p) => {
        const card = document.createElement("div");
        card.className = "tarea-card proyecto-card";

        const fecha = p.fecha_entrega
          ? new Date(p.fecha_entrega).toLocaleString()
          : "Sin fecha";

        let archivosHTML = "";
        if (p.archivos?.length) {
          archivosHTML =
            `<div class="tarea-archivos">` +
            p.archivos
              .map(
                (a) => `
              <div class="archivo-item">
                <i class="fas fa-paperclip"></i> ${a.archivo}
                <button class="btn-del-archivo" data-id="${a.id}" title="Eliminar archivo">
                  <i class="fas fa-times"></i>
                </button>
              </div>`
              )
              .join("") +
            `</div>`;
        }

        card.innerHTML = `
          <div class="tarea-info">
            <h4><i class="fas fa-rocket"></i> ${p.titulo}</h4>
            <p>${p.descripcion || "Sin descripción."}</p>
            <div class="tarea-meta">
              <span><i class="fas fa-clock"></i> Entrega: ${fecha}</span>
            </div>
            ${archivosHTML}
          </div>
          <div class="tarea-acciones">
            <button class="btn-editar" data-id="${
              p.id
            }" title="Editar"><i class="fas fa-edit"></i></button>
            <button class="btn-eliminar" data-id="${
              p.id
            }" title="Eliminar"><i class="fas fa-trash"></i></button>
          </div>
        `;

        lista.appendChild(card);
      });
    }

    // ============================================================
    // ➕ Crear nuevo proyecto
    // ============================================================
    btnNuevo.addEventListener("click", () => abrirModal());

    // ============================================================
    // ✏️ Abrir modal (crear / editar)
    // ============================================================
    async function abrirModal(proyectoId = null) {
      modal.classList.remove("hidden");
      form.reset();
      previewArchivos.innerHTML = "";
      document.getElementById("tituloModalProyecto").innerHTML = proyectoId
        ? "<i class='fas fa-edit'></i> Editar proyecto"
        : "<i class='fas fa-file-alt'></i> Nuevo proyecto";
      document.getElementById("proyectoId").value = proyectoId || "";

      if (proyectoId) {
        try {
          const res = await fetch(
            `${window.base_url}profesor/grupos/detalle-proyecto/${proyectoId}`
          );
          const data = await res.json();

          if (data.error) return mostrarAlerta(data.error, "error");

          document.getElementById("tituloProyecto").value = data.titulo;
          document.getElementById("descripcionProyecto").value =
            data.descripcion || "";
          if (data.fecha_entrega)
            document.getElementById("fechaEntregaProyecto").value =
              data.fecha_entrega.replace(" ", "T");

          // Archivos existentes
          if (data.archivos?.length) {
            data.archivos.forEach((a) => {
              const item = document.createElement("div");
              item.className = "archivo-item";
              item.innerHTML = `
                <i class="fas fa-paperclip"></i>
                <span>${a.archivo}</span>
                <button class="btn-del-archivo" data-id="${a.id}">
                  <i class="fas fa-times"></i>
                </button>`;
              previewArchivos.appendChild(item);
            });
          }
        } catch (err) {
          mostrarAlerta("Error al cargar proyecto: " + err.message, "error");
        }
      }
    }

    // ============================================================
    // ❌ Cerrar modal
    // ============================================================
    modal
      .querySelectorAll(".close, .cerrar-modal")
      .forEach((b) =>
        b.addEventListener("click", () => modal.classList.add("hidden"))
      );

    // ============================================================
    // 💾 Guardar proyecto
    // ============================================================
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const formData = new FormData(form);

      try {
        const res = await fetch(
          `${window.base_url}profesor/grupos/guardar-proyecto`,
          {
            method: "POST",
            body: formData,
          }
        );
        const data = await res.json();

        if (data.success) {
          mostrarAlerta(data.mensaje, "success");
          modal.classList.add("hidden");
          cargarProyectos();
        } else {
          mostrarAlerta(data.error || "Error al guardar proyecto", "error");
        }
      } catch (err) {
        mostrarAlerta("Error: " + err.message, "error");
      }
    });

    // ============================================================
    // 🗑️ Eventos globales (editar / eliminar / archivos)
    // ============================================================
    document.addEventListener("click", async (e) => {
      const btnEditar = e.target.closest(".btn-editar");
      const btnEliminar = e.target.closest(".btn-eliminar");
      const btnArchivo = e.target.closest(".btn-del-archivo");

      // Editar proyecto
      if (btnEditar) abrirModal(btnEditar.dataset.id);

      // Eliminar proyecto
      if (btnEliminar) {
        if (!confirm("¿Eliminar este proyecto y sus archivos?")) return;
        const id = btnEliminar.dataset.id;
        try {
          const res = await fetch(
            `${window.base_url}profesor/grupos/eliminar-proyecto/${id}`,
            { method: "DELETE" }
          );
          const data = await res.json();
          if (data.success) {
            mostrarAlerta(data.mensaje, "success");
            cargarProyectos();
          } else {
            mostrarAlerta(data.error || "Error al eliminar proyecto", "error");
          }
        } catch (err) {
          mostrarAlerta("Error: " + err.message, "error");
        }
      }

      // Eliminar archivo individual
      if (btnArchivo) {
        if (!confirm("¿Eliminar este archivo?")) return;
        const idArchivo = btnArchivo.dataset.id;
        try {
          const res = await fetch(
            `${window.base_url}profesor/grupos/eliminar-archivo-proyecto/${idArchivo}`,
            {
              method: "DELETE",
            }
          );
          const data = await res.json();
          if (data.success) {
            mostrarAlerta(data.mensaje, "info");
            btnArchivo.closest(".archivo-item").remove();
          } else {
            mostrarAlerta(data.error || "Error al eliminar archivo", "error");
          }
        } catch (err) {
          mostrarAlerta("Error: " + err.message, "error");
        }
      }
    });

    // ============================================================
    // 📂 Previsualización de archivos seleccionados (nuevos)
    // ============================================================
    if (inputArchivos && previewArchivos) {
      inputArchivos.addEventListener("change", () => {
        previewArchivos.innerHTML = "";

        const files = Array.from(inputArchivos.files);
        if (files.length === 0) {
          previewArchivos.innerHTML = `<p style="color:var(--text-muted);font-size:0.9rem;">Ningún archivo seleccionado.</p>`;
          return;
        }

        files.forEach((file) => {
          const item = document.createElement("div");
          item.className = "archivo-item";
          const sizeKB = (file.size / 1024).toFixed(1);
          item.innerHTML = `<i class="fas fa-paperclip"></i><span>${file.name}</span><small>(${sizeKB} KB)</small>`;
          previewArchivos.appendChild(item);
        });
      });
    }
  },
};
