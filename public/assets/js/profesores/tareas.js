window.TareasUI = {
  inicializar(asignacionId) {
    const lista = document.getElementById("listaTareas");
    const modal = document.getElementById("modalTarea");
    const form = document.getElementById("formTarea");
    const btnNueva = document.getElementById("btnNuevaTarea");
    const inputArchivos = document.getElementById("archivoTarea");
    const previewArchivos = document.getElementById("previewArchivos");

    if (!lista || !modal || !form) {
      console.warn(
        "⚠️ No se encontraron elementos del módulo de tareas todavía."
      );
      return;
    }

    // ============================================================
    // 🚀 Inicialización
    // ============================================================
    cargarTareas();

    // ============================================================
    // 📋 Cargar lista de tareas
    // ============================================================
    async function cargarTareas() {
      lista.innerHTML = `<p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando tareas...</p>`;

      try {
        const res = await fetch(
          `${window.base_url}profesor/grupos/listar-tareas/${asignacionId}`
        );
        const tareas = await res.json();

        if (!Array.isArray(tareas) || tareas.length === 0) {
          lista.innerHTML = `<p class="placeholder">No hay tareas registradas.</p>`;
          return;
        }

        renderTareas(tareas);
      } catch (err) {
        lista.innerHTML = `<p class="error">❌ Error al cargar tareas: ${err.message}</p>`;
      }
    }

    // ============================================================
    // 🧱 Renderizar tarjetas de tareas
    // ============================================================
    function renderTareas(tareas) {
      lista.innerHTML = "";

      tareas.forEach((t) => {
        const card = document.createElement("div");
        card.className = "tarea-card";

        const fecha = t.fecha_entrega
          ? new Date(t.fecha_entrega).toLocaleString()
          : "Sin fecha";

        let archivosHTML = "";
        if (t.archivos?.length) {
          archivosHTML =
            `<div class="tarea-archivos">` +
            t.archivos
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
            <h4>${t.titulo}</h4>
            <p>${t.descripcion || "Sin descripción."}</p>
            <div class="tarea-meta">
              <span><i class="fas fa-clock"></i> Entrega: ${fecha}</span>
            </div>
            ${archivosHTML}
          </div>
          <div class="tarea-acciones">
  <button class="btn-ver-entregas" data-id="${t.id}" title="Ver entregas">
    <i class="fas fa-folder-open"></i>
  </button>
  <button class="btn-editar" data-id="${t.id}" title="Editar tarea">
    <i class="fas fa-edit"></i>
  </button>
  <button class="btn-eliminar" data-id="${t.id}" title="Eliminar tarea">
    <i class="fas fa-trash"></i>
  </button>
</div>

        `;

        lista.appendChild(card);
      });
    }

    // ============================================================
    // ➕ Crear nueva tarea
    // ============================================================
    btnNueva.addEventListener("click", () => abrirModal());

    // ============================================================
    // ✏️ Abrir modal (crear / editar)
    // ============================================================
    async function abrirModal(tareaId = null) {
      modal.classList.remove("hidden");
      form.reset();
      previewArchivos.innerHTML = "";
      document.getElementById("tituloModalTarea").innerHTML = tareaId
        ? "✏️ Editar tarea"
        : "➕ Nueva tarea";
      document.getElementById("tareaId").value = tareaId || "";

      if (tareaId) {
        try {
          const res = await fetch(
            `${window.base_url}profesor/grupos/detalle-tarea/${tareaId}`
          );
          const data = await res.json();

          if (data.error) return mostrarAlerta(data.error, "error");

          document.getElementById("tituloTarea").value = data.titulo;
          document.getElementById("descripcionTarea").value =
            data.descripcion || "";
          if (data.fecha_entrega)
            document.getElementById("fechaEntrega").value =
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
          mostrarAlerta("Error al cargar tarea: " + err.message, "error");
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
    // 💾 Guardar tarea
    // ============================================================
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const formData = new FormData(form);

      try {
        const res = await fetch(
          `${window.base_url}profesor/grupos/guardar-tarea`,
          {
            method: "POST",
            body: formData,
          }
        );
        const data = await res.json();

        if (data.success) {
          mostrarAlerta(data.mensaje, "success");
          modal.classList.add("hidden");
          cargarTareas();
        } else {
          mostrarAlerta(data.error || "Error al guardar tarea", "error");
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
      const btnVerEntregas = e.target.closest(".btn-ver-entregas");

      // Editar tarea
      if (btnEditar) abrirModal(btnEditar.dataset.id);

      // ============================================================
      // 📂 Ver entregas de una tarea
      // ============================================================
      if (btnVerEntregas) {
        const id = btnVerEntregas.dataset.id;
        const contenedor = document.getElementById("contenedorTareas");
        contenedor.innerHTML = `<p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando entregas...</p>`;

        try {
          const res = await fetch(
            `${window.base_url}profesor/grupos/tareas/entregas/${id}`
          );
          const html = await res.text();
          contenedor.innerHTML = html;

          // Inicializar lógica de entregas
          window.TareasEntregasUI?.init();
        } catch (err) {
          contenedor.innerHTML = `<p class="error">❌ Error al cargar entregas: ${err.message}</p>`;
        }
        return;
      }

      // Eliminar tarea completa
      if (btnEliminar) {
        if (!confirm("¿Eliminar esta tarea y sus archivos?")) return;
        const id = btnEliminar.dataset.id;

        try {
          const res = await fetch(
            `${window.base_url}profesor/grupos/eliminar-tarea/${id}`,
            { method: "DELETE" }
          );
          const data = await res.json();
          if (data.success) {
            mostrarAlerta(data.mensaje, "success");
            cargarTareas();
          } else {
            mostrarAlerta(data.error || "Error al eliminar tarea", "error");
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
            `${window.base_url}profesor/grupos/eliminar-archivo-tarea/${idArchivo}`,
            { method: "DELETE" }
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
