// ============================================================
// 🚀 MÓDULO DE PROYECTOS — ALUMNO (con filtros y actualización)
// ============================================================
window.ProyectosAlumnoUI = {
  proyectos: [],
  asignacionId: null,
  filtroActual: "pendiente",

  inicializar(asignacionId) {
    this.asignacionId = asignacionId;
    this.filtroActual = "pendiente";
    this.cargarProyectos();

    // ✅ Activar filtro por defecto
    const btnPendiente = document.querySelector(
      '.filtro-btn[data-filtro="pendiente"]'
    );
    if (btnPendiente) {
      document
        .querySelectorAll(".filtro-btn")
        .forEach((b) => b.classList.remove("activo"));
      btnPendiente.classList.add("activo");
    }

    // 🎯 Listeners de filtros
    document.querySelectorAll(".filtro-btn").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        document
          .querySelectorAll(".filtro-btn")
          .forEach((b) => b.classList.remove("activo"));
        e.target.classList.add("activo");
        this.filtroActual = e.target.dataset.filtro;
        this.mostrarProyectos();
      });
    });
  },

  // ============================================================
  // 📦 Cargar proyectos desde el backend
  // ============================================================
  async cargarProyectos() {
    const lista = document.getElementById("listaProyectos");
    lista.innerHTML = `<li class="sin-proyectos">Cargando proyectos...</li>`;

    try {
      const res = await fetch(
        `${window.base_url}alumno/proyectos/listar/${this.asignacionId}`
      );
      this.proyectos = await res.json();
      this.mostrarProyectos();
    } catch {
      lista.innerHTML = `<li class="sin-proyectos">Error al cargar los proyectos.</li>`;
    }
  },

  // ============================================================
  // 🎨 Mostrar proyectos con filtro
  // ============================================================
  mostrarProyectos() {
    const lista = document.getElementById("listaProyectos");
    lista.innerHTML = "";

    const ahora = new Date();

    // 🔄 Recalcular vencidos
    this.proyectos.forEach((p) => {
      if (
        p.estado === "pendiente" &&
        p.fecha_entrega &&
        new Date(p.fecha_entrega) < ahora
      ) {
        p.estado = "vencido";
      }
    });

    // 📅 Ordenar por fecha
    const proyectosOrdenados = [...this.proyectos].sort((a, b) => {
      const fa = a.fecha_entrega ? new Date(a.fecha_entrega) : new Date(0);
      const fb = b.fecha_entrega ? new Date(b.fecha_entrega) : new Date(0);
      return fa - fb;
    });

    // 🎯 Filtro activo
    let proyectosFiltrados = [];

    switch (this.filtroActual) {
      case "pendiente":
        proyectosFiltrados = proyectosOrdenados.filter(
          (p) => p.estado === "pendiente" || p.estado === "vencido"
        );
        break;

      case "entregado":
        proyectosFiltrados = proyectosOrdenados.filter(
          (p) => p.estado === "entregado" || p.estado === "tarde"
        );
        break;

      case "vencido":
        proyectosFiltrados = proyectosOrdenados.filter((p) => {
          if (p.estado === "entregado" || p.estado === "tarde") return false;
          if (!p.fecha_entrega) return false;
          return new Date(p.fecha_entrega) < ahora;
        });
        break;

      case "todas":
      default:
        proyectosFiltrados = proyectosOrdenados;
        break;
    }

    // 🕳️ Sin resultados
    if (proyectosFiltrados.length === 0) {
      lista.innerHTML =
        this.filtroActual === "pendiente"
          ? `<li class="sin-proyectos alentador">
              🎯 No tienes proyectos pendientes.<br>
              <small>Disfruta el progreso de tu semestre.</small>
            </li>`
          : `<li class="sin-proyectos">No hay proyectos en este filtro.</li>`;
      return;
    }

    // 🧾 Render de proyectos
    proyectosFiltrados.forEach((p) => {
      const li = document.createElement("li");
      li.className = "tarea-item proyecto-item";
      li.dataset.id = p.id;

      const fecha = p.fecha_entrega
        ? new Date(p.fecha_entrega).toLocaleDateString("es-MX", {
            day: "2-digit",
            month: "short",
            year: "numeric",
          })
        : "Sin fecha límite";

      li.innerHTML = `
        <div class="info">
          <strong>${p.titulo}</strong>
          <p>Entrega: ${fecha}</p>
        </div>
        <span class="estado ${p.estado}">
          ${p.estado.charAt(0).toUpperCase() + p.estado.slice(1)}
        </span>
      `;

      li.onclick = () => this.cargarDetalle(p.id);
      lista.appendChild(li);
    });
  },

  // ============================================================
  // 📂 Generador de bloques de archivos
  // ============================================================
  generarBloqueArchivos(titulo, archivos, tipo = "profesor") {
    const colorClase =
      tipo === "profesor" ? "archivos-profesor" : "archivos-entrega";
    const basePath =
      tipo === "profesor"
        ? "uploads/proyectos/"
        : "uploads/proyectos_entregas/";

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

  // ============================================================
  // 📘 Detalle de un proyecto
  // ============================================================
  async cargarDetalle(id) {
    const panel = document.getElementById("panelDetalleProyecto");
    panel.innerHTML = `<div class="cargando"><i class="fas fa-spinner fa-spin"></i> Cargando proyecto...</div>`;

    const res = await fetch(`${window.base_url}alumno/proyectos/detalle/${id}`);
    const p = await res.json();

    const miEntrega = p.mi_entrega || {};
    const estado = miEntrega.estado || "pendiente";
    const entregada = estado !== "pendiente";

    const archivosProfesor = this.generarBloqueArchivos(
      "Archivos del profesor",
      p.archivos,
      "profesor"
    );

    let contenidoEntrega = "";

    // ============================================================
    // 🟢 Si ya entregó el proyecto
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
        <button id="btnDeshacerProyecto" class="btn-deshacer" data-id="${p.id}">
          <i class="fas fa-undo"></i> Deshacer entrega
        </button>
      `;
    } else {
      // ============================================================
      // 🟠 Si no ha entregado
      // ============================================================
      contenidoEntrega = `
        <form id="formEntregaProyecto" enctype="multipart/form-data" class="entrega-form">
          <input type="hidden" name="proyecto_id" value="${p.id}">

          <div class="selector-archivos">
            <input type="file" id="inputArchivosProyecto" multiple style="display:none">
            <button type="button" id="btnAgregarArchivoProyecto" class="btn-sec">
              <i class="fas fa-plus"></i> Agregar archivos
            </button>
          </div>

          <ul id="listaArchivosProyecto" class="lista-archivos"></ul>

          <button type="submit" class="btn-enviar">
            <i class="fas fa-paper-plane"></i> Entregar proyecto
          </button>
        </form>
      `;
    }

    // ============================================================
    // 🏁 Banner de calificación superior
    // ============================================================
    let bannerHTML = "";
    if (entregada) {
      const cal = miEntrega.calificacion;
      const retro =
        miEntrega.retroalimentacion || "Sin comentarios del profesor.";

      let claseColor = "gris";
      if (estado === "tarde") claseColor = "rojo";
      else if (cal >= 80) claseColor = "verde";
      else if (cal < 80 && cal != null) claseColor = "amarillo";

      bannerHTML = `
      <div class="banner-calificacion ${claseColor}">
        <div class="info">
          <i class="fas fa-star"></i>
          <div>
            <h4>${
              cal != null
                ? `Calificación: ${cal}/100`
                : "Pendiente de calificación"
            }</h4>
            <p>${retro}</p>
          </div>
        </div>
      </div>`;
    } else {
      bannerHTML = `
      <div class="banner-calificacion gris">
        <div class="info">
          <i class="fas fa-hourglass-half"></i>
          <div>
            <h4>Proyecto no entregado</h4>
            <p>Entrega tus archivos antes de la fecha límite para recibir calificación.</p>
          </div>
        </div>
      </div>`;
    }

    // ============================================================
    // 📄 Render final
    // ============================================================
    panel.innerHTML = `
      ${bannerHTML}
      <div class="detalle-proyecto">
        <h2>${p.titulo}</h2>
        <p class="descripcion">${p.descripcion || "Sin descripción."}</p>
        <p class="fecha"><i class="fas fa-calendar-alt"></i> Fecha de entrega:
          <b>${
            p.fecha_entrega
              ? new Date(p.fecha_entrega).toLocaleString()
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
      document
        .getElementById("btnDeshacerProyecto")
        .addEventListener("click", () => this.deshacerEntrega(p.id));
    } else {
      const form = document.getElementById("formEntregaProyecto");
      const input = document.getElementById("inputArchivosProyecto");
      const btnAgregar = document.getElementById("btnAgregarArchivoProyecto");
      const listaArchivos = document.getElementById("listaArchivosProyecto");

      let archivosSeleccionados = [];

      btnAgregar.addEventListener("click", () => input.click());

      input.addEventListener("change", () => {
        const nuevos = Array.from(input.files);
        archivosSeleccionados = [...archivosSeleccionados, ...nuevos];
        mostrarListaArchivos();
        input.value = "";
      });

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

        listaArchivos.querySelectorAll(".eliminar-archivo").forEach((b) =>
          b.addEventListener("click", (e) => {
            const idx = e.currentTarget.dataset.index;
            archivosSeleccionados.splice(idx, 1);
            mostrarListaArchivos();
          })
        );
      }

      form.addEventListener("submit", (e) => {
        e.preventDefault();
        const datos = new FormData(form);
        archivosSeleccionados.forEach((f) => datos.append("archivos[]", f));
        ProyectosAlumnoUI.enviarArchivos(datos, p.id);
      });
    }
  },

  // ============================================================
  // 📤 Enviar archivos del proyecto
  // ============================================================
  async enviarArchivos(datos, proyectoId) {
    const btn = document.querySelector(".btn-enviar");
    btn.disabled = true;

    try {
      const res = await fetch(`${window.base_url}alumno/proyectos/entregar`, {
        method: "POST",
        body: datos,
      });
      const resp = await res.json();

      if (resp.error) return mostrarAlerta(resp.error, "error");

      mostrarAlerta(resp.mensaje, "success");

      const proyecto = this.proyectos.find((p) => p.id == proyectoId);
      if (proyecto) proyecto.estado = resp.estado || "entregado";

      this.mostrarProyectos();
      this.cargarDetalle(proyectoId);
    } catch {
      mostrarAlerta("Error al enviar el proyecto.", "error");
    } finally {
      btn.disabled = false;
    }
  },

  // ============================================================
  // 🔄 Deshacer entrega
  // ============================================================
  async deshacerEntrega(proyectoId) {
    mostrarConfirmacion(
      "Deshacer entrega",
      "¿Eliminar tus archivos enviados? Podrás volver a entregar después.",
      async () => {
        try {
          const res = await fetch(
            `${window.base_url}alumno/proyectos/deshacer/${proyectoId}`,
            { method: "DELETE" }
          );
          const data = await res.json();
          if (data.success) {
            mostrarAlerta(data.mensaje, "success");

            const proyecto = this.proyectos.find((p) => p.id == proyectoId);
            if (proyecto) proyecto.estado = "pendiente";
            this.mostrarProyectos();
            this.cargarDetalle(proyectoId);
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
};
