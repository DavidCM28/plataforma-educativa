// ============================================================
// 📘 MÓDULO DE TAREAS — ALUMNO (con filtros y actualización)
// ============================================================
window.TareasAlumnoUI = {
  tareas: [],
  asignacionId: null,
  filtroActual: "pendiente", // ✅ Cambiar a 'pendiente' por defecto

  inicializar(asignacionId) {
    this.asignacionId = asignacionId;
    this.filtroActual = "pendiente"; // 🔹 Asegura que arranque así
    this.cargarTareas();

    // ✅ Marca el botón de "Pendientes" como activo al iniciar
    const btnPendiente = document.querySelector(
      '.filtro-btn[data-filtro="pendiente"]'
    );
    if (btnPendiente) {
      document
        .querySelectorAll(".filtro-btn")
        .forEach((b) => b.classList.remove("activo"));
      btnPendiente.classList.add("activo");
    }

    // 🎯 Listeners de los filtros
    document.querySelectorAll(".filtro-btn").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        document
          .querySelectorAll(".filtro-btn")
          .forEach((b) => b.classList.remove("activo"));
        e.target.classList.add("activo");
        this.filtroActual = e.target.dataset.filtro;
        this.mostrarTareas();
      });
    });
  },

  async cargarTareas() {
    const lista = document.getElementById("listaTareas");
    lista.innerHTML = `<li class="sin-tareas">Cargando tareas...</li>`;

    try {
      const res = await fetch(
        `${window.base_url}alumno/tareas/listar/${this.asignacionId}`
      );
      this.tareas = await res.json();
      this.mostrarTareas();
    } catch {
      lista.innerHTML = `<li class="sin-tareas">Error al cargar las tareas.</li>`;
    }
  },

  mostrarTareas() {
    const lista = document.getElementById("listaTareas");
    lista.innerHTML = "";

    const ahora = new Date();

    // 🔄 Recalcular estado vencido solo para tareas pendientes
    this.tareas.forEach((t) => {
      if (
        t.estado === "pendiente" &&
        t.fecha_entrega &&
        new Date(t.fecha_entrega) < ahora
      ) {
        t.estado = "vencida";
      }
    });

    // 📅 Ordenar por fecha de entrega (más cercana primero)
    const tareasOrdenadas = [...this.tareas].sort((a, b) => {
      const fechaA = a.fecha_entrega ? new Date(a.fecha_entrega) : new Date(0);
      const fechaB = b.fecha_entrega ? new Date(b.fecha_entrega) : new Date(0);
      return fechaA - fechaB;
    });

    // 🎯 Aplicar filtro según selección
    let tareasFiltradas = [];

    switch (this.filtroActual) {
      case "pendiente":
        // Mostrar pendientes normales o vencidas (no entregadas)
        tareasFiltradas = tareasOrdenadas.filter(
          (t) => t.estado === "pendiente" || t.estado === "vencida"
        );
        break;

      case "entregada":
        // Mostrar entregadas o tarde
        tareasFiltradas = tareasOrdenadas.filter(
          (t) => t.estado === "entregada" || t.estado === "tarde"
        );
        break;

      case "vencida":
        // ✅ Solo tareas no entregadas cuya fecha/hora límite ya pasó
        tareasFiltradas = tareasOrdenadas.filter((t) => {
          if (t.estado === "entregada" || t.estado === "tarde") return false;
          if (!t.fecha_entrega) return false;
          const fechaEntrega = new Date(t.fecha_entrega);
          return fechaEntrega.getTime() < ahora.getTime();
        });
        break;

      case "todas":
      default:
        tareasFiltradas = tareasOrdenadas;
        break;
    }

    // 🕳️ Si no hay tareas en el filtro
    if (tareasFiltradas.length === 0) {
      if (this.filtroActual === "pendiente") {
        lista.innerHTML = `
        <li class="sin-tareas alentador">
          🎉 No tienes tareas pendientes ni vencidas.<br>
          <small>Disfruta tu tiempo libre o repasa tus materias.</small>
        </li>`;
      } else {
        lista.innerHTML = `<li class="sin-tareas">No hay tareas en este filtro.</li>`;
      }
      return;
    }

    // 🧾 Render de tareas
    tareasFiltradas.forEach((t) => {
      const li = document.createElement("li");
      li.className = "tarea-item";
      li.dataset.id = t.id;

      const fecha = t.fecha_entrega
        ? new Date(t.fecha_entrega).toLocaleDateString("es-MX", {
            day: "2-digit",
            month: "short",
            year: "numeric",
          })
        : "Sin fecha límite";

      li.innerHTML = `
      <div class="info">
        <strong>${t.titulo}</strong>
        <p>Entrega: ${fecha}</p>
      </div>
      <span class="estado ${t.estado}">
        ${t.estado.charAt(0).toUpperCase() + t.estado.slice(1)}
      </span>
    `;

      li.onclick = () => this.cargarDetalle(t.id);
      lista.appendChild(li);
    });
  },

  generarBloqueArchivos(titulo, archivos, tipo = "profesor") {
    const colorClase =
      tipo === "profesor" ? "archivos-profesor" : "archivos-entrega";
    const basePath =
      tipo === "profesor" ? "uploads/tareas/" : "uploads/entregas/";

    if (!archivos || archivos.length === 0) {
      return `
        <div class="${colorClase}">
          <h5><i class="fas fa-paperclip"></i> ${titulo}</h5>
          <p class="sin-archivos">Sin archivos adjuntos.</p>
        </div>`;
    }

    return `
      <div class="${colorClase}">
        <h5><i class="fas fa-paperclip"></i> ${titulo}</h5>
        ${archivos
          .map(
            (a) => `
          <div class="archivo-item">
            <div><i class="fas fa-file"></i>
              <a href="${window.base_url}${basePath}${
              a.archivo || a
            }" target="_blank">${a.archivo || a}</a>
            </div>
            <i class="fas fa-external-link-alt"></i>
          </div>`
          )
          .join("")}
      </div>`;
  },

  async cargarDetalle(id) {
    const panel = document.getElementById("panelDetalle");
    panel.innerHTML = `<div class="cargando"><i class="fas fa-spinner fa-spin"></i> Cargando tarea...</div>`;

    // 🔹 Obtener detalle de la tarea
    const res = await fetch(`${window.base_url}alumno/tareas/detalle/${id}`);
    const t = await res.json();

    const miEntrega = t.mi_entrega || {};
    const estado = miEntrega.estado || "pendiente";
    const entregada = estado !== "pendiente";

    const archivosProfesor = this.generarBloqueArchivos(
      "Archivos del profesor",
      t.archivos,
      "profesor"
    );

    let contenidoEntrega = "";

    // ============================================================
    // 🟢 Si ya entregó la tarea
    // ============================================================
    if (entregada) {
      const archivosAlumno = this.generarBloqueArchivos(
        "Archivos enviados",
        miEntrega.archivos,
        "alumno"
      );

      contenidoEntrega = `
      <div class="entrega-info">
        <div>
          <p><i class="fas fa-clock"></i> <b>Fecha de entrega:</b> ${new Date(
            miEntrega.fecha_entrega
          ).toLocaleString()}</p>
        </div>
        <span class="estado ${estado}">
          ${estado.charAt(0).toUpperCase() + estado.slice(1)}
        </span>
      </div>
      ${archivosAlumno}

${
  miEntrega.calificacion || miEntrega.retroalimentacion
    ? `
  <div class="evaluacion-profesor">
    <h5><i class="fas fa-star"></i> Evaluación del profesor</h5>
    ${
      miEntrega.calificacion
        ? `<p><b>Calificación:</b> ${miEntrega.calificacion}/100</p>`
        : `<p><b>Calificación:</b> Pendiente</p>`
    }
    ${
      miEntrega.retroalimentacion
        ? `<p><b>Retroalimentación:</b> ${miEntrega.retroalimentacion}</p>`
        : ``
    }
  </div>`
    : `<div class="evaluacion-profesor pendiente">
      <h5><i class="fas fa-hourglass-half"></i> Evaluación pendiente</h5>
      <p>Tu profesor aún no ha calificado esta tarea.</p>
    </div>`
}

<button id="btnDeshacer" class="btn-deshacer" data-id="${t.id}">
  <i class="fas fa-undo"></i> Deshacer entrega
</button>
`;
    }

    // ============================================================
    // 🟠 Si aún no ha entregado
    // ============================================================
    else {
      contenidoEntrega = `
      <form id="formEntrega" enctype="multipart/form-data" class="entrega-form">
        <input type="hidden" name="tarea_id" value="${t.id}">

        <div class="selector-archivos">
          <input type="file" id="inputArchivos" multiple style="display:none">
          <button type="button" id="btnAgregarArchivo" class="btn-sec">
            <i class="fas fa-plus"></i> Agregar archivos
          </button>
        </div>

        <ul id="listaArchivosSeleccionados" class="lista-archivos"></ul>

        <button type="submit" class="btn-enviar">
          <i class="fas fa-paper-plane"></i> Entregar tarea
        </button>
      </form>
    `;
    }

    // ============================================================
    // 📄 Render final
    // ============================================================
    panel.innerHTML = `
    <div class="detalle-tarea">
      <h2>${t.titulo}</h2>
      <p class="descripcion">${t.descripcion || "Sin descripción."}</p>
      <p class="fecha"><i class="fas fa-calendar-alt"></i> Fecha de entrega: 
        <b>${
          t.fecha_entrega
            ? new Date(t.fecha_entrega).toLocaleString()
            : "No definida"
        }</b></p>
      ${archivosProfesor}
      <div class="entrega-alumno">
        <h4><i class="fas fa-user-check"></i> Mi entrega</h4>
        ${contenidoEntrega}
      </div>
    </div>
  `;

    // ============================================================
    // 🎯 Eventos dinámicos
    // ============================================================
    if (entregada) {
      // Deshacer entrega
      document
        .getElementById("btnDeshacer")
        .addEventListener("click", () => this.deshacerEntrega(t.id));
    } else {
      // ---- Sistema para acumular archivos antes de enviar ----
      const form = document.getElementById("formEntrega");
      const input = document.getElementById("inputArchivos");
      const btnAgregar = document.getElementById("btnAgregarArchivo");
      const listaArchivos = document.getElementById(
        "listaArchivosSeleccionados"
      );

      let archivosSeleccionados = [];

      // Abrir selector
      btnAgregar.addEventListener("click", () => input.click());

      // Agregar archivos sin perder los anteriores
      input.addEventListener("change", () => {
        const nuevos = Array.from(input.files);
        archivosSeleccionados = [...archivosSeleccionados, ...nuevos];
        mostrarListaArchivos();
        input.value = ""; // limpiar para permitir volver a elegir
      });

      // Mostrar la lista de archivos
      function mostrarListaArchivos() {
        listaArchivos.innerHTML = "";
        archivosSeleccionados.forEach((a, i) => {
          const li = document.createElement("li");
          const tamaño = (a.size / 1024).toFixed(1) + " KB";
          li.innerHTML = `
          <div><i class="fas fa-file"></i> ${a.name} <span style="color:#aaa;">(${tamaño})</span></div>
          <button class="eliminar-archivo" data-index="${i}">
            <i class="fas fa-times"></i>
          </button>
        `;
          listaArchivos.appendChild(li);
        });

        // Eliminar archivos individualmente
        listaArchivos.querySelectorAll(".eliminar-archivo").forEach((b) =>
          b.addEventListener("click", (e) => {
            const idx = e.currentTarget.dataset.index;
            archivosSeleccionados.splice(idx, 1);
            mostrarListaArchivos();
          })
        );
      }

      // Enviar todos los archivos seleccionados
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        const datos = new FormData(form);
        archivosSeleccionados.forEach((f) => datos.append("archivos[]", f));
        TareasAlumnoUI.enviarArchivos(datos, t.id);
      });
    }
  },
  async entregarTarea(e) {
    e.preventDefault();
    const form = e.target;
    const datos = new FormData(form);
    const tareaId = form.querySelector("[name='tarea_id']").value;
    const btn = form.querySelector("button");
    btn.disabled = true;

    try {
      const res = await fetch(`${window.base_url}alumno/tareas/entregar`, {
        method: "POST",
        body: datos,
      });
      const resp = await res.json();
      if (resp.error) return mostrarAlerta(resp.error, "error");

      mostrarAlerta(resp.mensaje, "success");

      // actualizar estado local
      const tarea = TareasAlumnoUI.tareas.find((t) => t.id == tareaId);
      if (tarea) tarea.estado = resp.estado || "entregada";

      TareasAlumnoUI.mostrarTareas();
      TareasAlumnoUI.cargarDetalle(
        tareaId,
        document.getElementById("panelDetalle")
      );
    } catch {
      mostrarAlerta("Error al enviar la tarea.", "error");
    } finally {
      btn.disabled = false;
    }
  },

  async deshacerEntrega(tareaId) {
    mostrarConfirmacion(
      "Deshacer entrega",
      "¿Eliminar tus archivos enviados? Podrás volver a entregar después.",
      async () => {
        try {
          const res = await fetch(
            `${window.base_url}alumno/tareas/deshacer/${tareaId}`,
            { method: "DELETE" }
          );
          const data = await res.json();
          if (data.success) {
            mostrarAlerta(data.mensaje, "success");

            // 🔄 Actualizar lista sin recargar
            const tarea = this.tareas.find((t) => t.id == tareaId);
            if (tarea) tarea.estado = "pendiente";
            this.mostrarTareas();

            this.cargarDetalle(tareaId);
          } else {
            mostrarAlerta(
              data.error || "No se pudo deshacer la entrega.",
              "error"
            );
          }
        } catch {
          mostrarAlerta("Error al conectar con el servidor.", "error");
        }
      }
    );
  },
  async enviarArchivos(datos, tareaId) {
    const btn = document.querySelector(".btn-enviar");
    btn.disabled = true;

    try {
      const res = await fetch(`${window.base_url}alumno/tareas/entregar`, {
        method: "POST",
        body: datos,
      });
      const resp = await res.json();

      if (resp.error) return mostrarAlerta(resp.error, "error");

      mostrarAlerta(resp.mensaje, "success");

      // 🔁 Actualizar estado local y recargar vista
      const tarea = this.tareas.find((t) => t.id == tareaId);
      if (tarea) tarea.estado = resp.estado || "entregada";

      this.mostrarTareas();
      this.cargarDetalle(tareaId);
    } catch {
      mostrarAlerta("Error al enviar la tarea.", "error");
    } finally {
      btn.disabled = false;
    }
  },
};
