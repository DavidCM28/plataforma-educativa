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
    const filtroParcial = document.getElementById("filtroParcial");
    if (filtroParcial) {
      filtroParcial.addEventListener("change", () => cargarTareas());
    }

    // ============================================================
    // 📋 Cargar lista de tareas
    // ============================================================
    async function cargarTareas() {
      lista.innerHTML = `<p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando tareas...</p>`;
      const parcialSeleccionado =
        document.getElementById("filtroParcial")?.value || "";

      try {
        const res = await fetch(
          `${window.base_url}profesor/grupos/listar-tareas/${asignacionId}`
        );
        let tareas = await res.json();

        if (parcialSeleccionado) {
          tareas = tareas.filter(
            (t) => String(t.parcial_numero) === parcialSeleccionado
          );
        }

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

      const tabla = document.createElement("table");
      tabla.className = "tabla-tareas";
      tabla.innerHTML = `
    <thead>
      <tr>
        <th>Título</th>
        <th>Descripción</th>
        <th>Entrega</th>
        <th>Parcial</th>
        <th>Criterio</th>
        <th>% Tarea</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  `;

      const tbody = tabla.querySelector("tbody");

      tareas.forEach((t) => {
        const fila = document.createElement("tr");
        const fecha = t.fecha_entrega
          ? new Date(t.fecha_entrega).toLocaleDateString("es-MX", {
              day: "2-digit",
              month: "short",
              year: "numeric",
            })
          : "—";
        const criterioNombre = t.criterio_nombre || t.criterio || "—";

        fila.innerHTML = `
      <td>${t.titulo}</td>
      <td>${t.descripcion || "Sin descripción"}</td>
      <td>${fecha}</td>
      <td>${t.parcial_numero || "—"}</td>
      <td>${criterioNombre}</td>
      <td>${t.porcentaje_tarea ? t.porcentaje_tarea + "%" : "0%"}</td>
      <td class="acciones">
        <button class="btn-ver-entregas" data-id="${t.id}" title="Ver entregas">
          <i class="fas fa-folder-open"></i>
        </button>
        <button class="btn-editar" data-id="${t.id}" title="Editar">
          <i class="fas fa-edit"></i>
        </button>
        <button class="btn-eliminar" data-id="${t.id}" title="Eliminar">
          <i class="fas fa-trash"></i>
        </button>
      </td>
    `;
        tbody.appendChild(fila);
      });

      lista.appendChild(tabla);
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
      // 🔄 Limpiar info de criterio
      const infoCriterio = document.getElementById("infoCriterio");
      const spanPorcentaje = document.getElementById("porcentajeCriterio");
      const spanRestante = document.getElementById("porcentajeRestante");
      const inputPorcentaje = document.getElementById("porcentajeTarea");

      infoCriterio.style.display = "none";
      spanPorcentaje.textContent = "0%";
      spanRestante.textContent = "--%";
      inputPorcentaje.value = "";
      inputPorcentaje.max = "100";
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

          // ✅ Establecer parcial seleccionado
          if (data.parcial_numero) {
            document.getElementById("parcialNumero").value =
              data.parcial_numero;
          }

          // 👇 NUEVO: establecer criterio seleccionado
          if (data.criterio_id) {
            document.getElementById("criterioId").value = data.criterio_id;

            // Forzamos que se dispare el evento "change"
            // para mostrar el porcentaje total del criterio
            const event = new Event("change");
            document.getElementById("criterioId").dispatchEvent(event);
          }

          // 👇 NUEVO: cargar porcentaje de tarea (si existe)
          if (data.porcentaje_tarea) {
            document.getElementById("porcentajeTarea").value =
              data.porcentaje_tarea;
            document.getElementById("infoCriterio").style.display = "block";
          }

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
    // 🎯 Mostrar porcentaje de criterio al seleccionarlo
    // ============================================================
    const selectCriterio = document.getElementById("criterioId");
    const selectParcial = document.getElementById("parcialNumero");
    const infoCriterio = document.getElementById("infoCriterio");
    const spanPorcentaje = document.getElementById("porcentajeCriterio");
    const spanRestante = document.getElementById("porcentajeRestante");
    const inputPorcentaje = document.getElementById("porcentajeTarea");

    if (selectCriterio && selectParcial) {
      selectCriterio.addEventListener("change", async () => {
        const criterioId = selectCriterio.value;
        const parcialNum = selectParcial.value;
        const tareaId = document.getElementById("tareaId").value;
        const asignacionId = document.querySelector(
          "[name='asignacion_id']"
        ).value;

        if (!criterioId || !parcialNum) {
          infoCriterio.style.display = "none";
          return;
        }

        try {
          // 1️⃣ Consultar porcentaje total del criterio
          const res = await fetch(
            `${window.base_url}profesor/grupos/criterio-porcentaje?criterio_id=${criterioId}&parcial_num=${parcialNum}`
          );
          const data = await res.json();
          const total = data.porcentaje || 0;
          spanPorcentaje.textContent = total + "%";

          // 2️⃣ Consultar cuánto ya se ha usado
          const res2 = await fetch(
            `${window.base_url}profesor/grupos/criterio-usado?criterio_id=${criterioId}&parcial_num=${parcialNum}&asignacion_id=${asignacionId}&tarea_id=${tareaId}`
          );
          const data2 = await res2.json();
          const usado = data2.usado || 0;

          // 3️⃣ Calcular restante
          const restante = Math.max(total - usado, 0);
          spanRestante.textContent = restante + "% disponibles";
          inputPorcentaje.max = restante; // limitar campo
          infoCriterio.style.display = "block";
        } catch (err) {
          console.error("❌ Error al consultar criterio:", err);
          infoCriterio.style.display = "none";
        }
      });
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

      const parcial = document.getElementById("parcialNumero").value;
      if (!parcial) {
        mostrarAlerta("Selecciona un parcial.", "error");
        return;
      }

      const maxPermitido = parseFloat(inputPorcentaje.max || 0);
      const valorIngresado = parseFloat(inputPorcentaje.value || 0);

      if (valorIngresado > maxPermitido) {
        mostrarAlerta(
          `No puedes asignar más del ${maxPermitido}% restante a este criterio.`,
          "error"
        );
        return;
      }

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
