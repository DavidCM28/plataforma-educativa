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
    const filtroParcial = document.getElementById("filtroParcialProyecto");
    if (filtroParcial) {
      filtroParcial.addEventListener("change", () => cargarProyectos());
    }

    // ============================================================
    // 📋 Cargar lista de proyectos
    // ============================================================
    async function cargarProyectos() {
      lista.innerHTML = `<p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando proyectos...</p>`;
      const parcialSeleccionado =
        document.getElementById("filtroParcialProyecto")?.value || "";

      try {
        const res = await fetch(
          `${window.base_url}profesor/grupos/listar-proyectos/${asignacionId}`
        );
        let proyectos = await res.json();

        if (parcialSeleccionado) {
          proyectos = proyectos.filter(
            (p) => String(p.parcial_numero) === parcialSeleccionado
          );
        }

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
    // 🧱 Renderizar tabla de proyectos
    // ============================================================
    function renderProyectos(proyectos) {
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
            <th>% Proyecto</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      `;

      const tbody = tabla.querySelector("tbody");

      proyectos.forEach((p) => {
        const fila = document.createElement("tr");
        const fecha = p.fecha_entrega
          ? new Date(p.fecha_entrega).toLocaleDateString("es-MX", {
              day: "2-digit",
              month: "short",
              year: "numeric",
            })
          : "—";
        const criterioNombre = p.criterio_nombre || p.criterio || "—";

        fila.innerHTML = `
  <td>${p.titulo}</td>
  <td>${p.descripcion || "Sin descripción"}</td>
  <td>${fecha}</td>
  <td>${p.parcial_numero || "—"}</td>
  <td>${criterioNombre}</td>
  <td>${p.porcentaje_proyecto ? p.porcentaje_proyecto + "%" : "0%"}</td>
  <td class="acciones">
    <button class="btn-ver-entregas" data-id="${p.id}" title="Ver entregas">
      <i class="fas fa-folder-open"></i>
    </button>
    <button class="btn-editar" data-id="${p.id}" title="Editar">
      <i class="fas fa-edit"></i>
    </button>
    <button class="btn-eliminar" data-id="${p.id}" title="Eliminar">
      <i class="fas fa-trash"></i>
    </button>
  </td>
`;

        tbody.appendChild(fila);
      });

      lista.appendChild(tabla);
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

      const infoCriterio = document.getElementById("infoCriterioProyecto");
      const spanPorcentaje = document.getElementById(
        "porcentajeCriterioProyecto"
      );
      const spanRestante = document.getElementById(
        "porcentajeRestanteProyecto"
      );
      const inputPorcentaje = document.getElementById("porcentajeProyecto");

      infoCriterio.style.display = "none";
      spanPorcentaje.textContent = "0%";
      spanRestante.textContent = "--%";
      inputPorcentaje.value = "";
      inputPorcentaje.max = "100";
      previewArchivos.innerHTML = "";
      document.getElementById("tituloModalProyecto").innerHTML = proyectoId
        ? "✏️ Editar proyecto"
        : "➕ Nuevo proyecto";
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

          if (data.parcial_numero)
            document.getElementById("parcialNumeroProyecto").value =
              data.parcial_numero;

          if (data.criterio_id) {
            document.getElementById("criterioIdProyecto").value =
              data.criterio_id;
            const event = new Event("change");
            document.getElementById("criterioIdProyecto").dispatchEvent(event);
          }

          if (data.porcentaje_proyecto) {
            document.getElementById("porcentajeProyecto").value =
              data.porcentaje_proyecto;
            infoCriterio.style.display = "block";
          }

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
    // 🎯 Mostrar porcentaje de criterio
    // ============================================================
    const selectCriterio = document.getElementById("criterioIdProyecto");
    const selectParcial = document.getElementById("parcialNumeroProyecto");
    const infoCriterio = document.getElementById("infoCriterioProyecto");
    const spanPorcentaje = document.getElementById(
      "porcentajeCriterioProyecto"
    );
    const spanRestante = document.getElementById("porcentajeRestanteProyecto");
    const inputPorcentaje = document.getElementById("porcentajeProyecto");

    if (selectCriterio && selectParcial) {
      selectCriterio.addEventListener("change", async () => {
        const criterioId = selectCriterio.value;
        const parcialNum = selectParcial.value;
        const proyectoId = document.getElementById("proyectoId").value;
        const asignacionId = document.querySelector(
          "[name='asignacion_id']"
        ).value;

        if (!criterioId || !parcialNum) {
          infoCriterio.style.display = "none";
          return;
        }

        try {
          // 1️⃣ Porcentaje total
          const res = await fetch(
            `${window.base_url}profesor/grupos/criterio-porcentaje?criterio_id=${criterioId}&parcial_num=${parcialNum}`
          );
          const data = await res.json();
          const total = data.porcentaje || 0;
          spanPorcentaje.textContent = total + "%";

          // 2️⃣ Porcentaje usado
          const res2 = await fetch(
            `${window.base_url}profesor/grupos/criterio-usado-proyecto?criterio_id=${criterioId}&parcial_num=${parcialNum}&asignacion_id=${asignacionId}&proyecto_id=${proyectoId}`
          );
          const data2 = await res2.json();
          const usado = data2.usado || 0;

          // 3️⃣ Restante
          const restante = Math.max(total - usado, 0);
          spanRestante.textContent = restante + "% disponibles";
          inputPorcentaje.max = restante;
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
    // 💾 Guardar proyecto
    // ============================================================
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const maxPermitido = parseFloat(inputPorcentaje.max || 0);
      const valorIngresado = parseFloat(inputPorcentaje.value || 0);

      if (valorIngresado > maxPermitido) {
        mostrarAlerta(
          `No puedes asignar más del ${maxPermitido}% restante.`,
          "error"
        );
        return;
      }

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
      const btnVerEntregas = e.target.closest(".btn-ver-entregas");

      if (btnEditar) abrirModal(btnEditar.dataset.id);

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

      if (btnVerEntregas) {
        const idProyecto = btnVerEntregas.dataset.id;
        const panel = document.getElementById("listaProyectos");
        panel.innerHTML = `<p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando entregas...</p>`;
        try {
          const res = await fetch(
            `${window.base_url}profesor/grupos/proyectos/entregas/${idProyecto}`
          );
          const html = await res.text();
          panel.innerHTML = html;
          // Inicializar lógica de entregas
          window.ProyectosEntregasUI?.init();
        } catch (err) {
          panel.innerHTML = `<p class="error">❌ Error al cargar entregas: ${err.message}</p>`;
        }
        return;
      }

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
    // 📂 Previsualización de archivos
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
