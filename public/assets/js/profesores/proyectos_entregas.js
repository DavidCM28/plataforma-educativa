/* ============================================================
   🚀 MÓDULO DE ENTREGAS — PROYECTOS (PROFESOR)
   Usa las mismas clases CSS de tareas_entregas.css
   ============================================================ */

window.ProyectosEntregasUI = {
  async init() {
    console.log("✅ Script de entregas de proyectos cargado");

    const section = document.querySelector(".entregas-section");
    if (!section) return;

    const proyectoId = section.dataset.proyecto;
    const asignacionId = section.dataset.asignacion;
    this.lista = document.getElementById("listaEntregasProyecto");

    // ============================================================
    // 🔙 Botón volver a la lista de proyectos
    // ============================================================
    const btnVolver = document.getElementById("btnVolverProyectos");
    if (btnVolver) {
      btnVolver.addEventListener("click", async () => {
        const panel = document.getElementById("listaProyectos");
        panel.innerHTML = `<p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando proyectos...</p>`;
        try {
          const res = await fetch(
            `${window.base_url}profesor/grupos/proyectos/${asignacionId}`
          );
          const html = await res.text();
          panel.innerHTML = html;
          // Re-inicializar módulo principal
          if (window.ProyectosUI) window.ProyectosUI.inicializar(asignacionId);
        } catch (err) {
          panel.innerHTML = `<p class="error">❌ Error al volver: ${err.message}</p>`;
        }
      });
    }

    // ============================================================
    // 📦 Cargar lista de entregas desde backend
    // ============================================================
    try {
      const res = await fetch(
        `${window.base_url}profesor/grupos/proyectos/entregas-lista/${proyectoId}`
      );
      const data = await res.json();

      if (data.error) {
        this.lista.innerHTML = `<p class="error">${data.error}</p>`;
        return;
      }

      // 🔸 Guardamos el proyecto completo para conocer su porcentaje
      this.proyecto = data.proyecto;
      this.render(data.alumnos || []);
    } catch (err) {
      this.lista.innerHTML = `<p class="error">Error al cargar: ${err.message}</p>`;
    }
  },

  /* ============================================================
     🧱 Render principal
     ============================================================ */
  render(alumnos) {
    if (!Array.isArray(alumnos) || alumnos.length === 0) {
      this.lista.innerHTML = `<p class="placeholder">No hay alumnos registrados en este grupo.</p>`;
      return;
    }

    const entregados = alumnos.filter((a) => a.estado !== "pendiente");
    const pendientes = alumnos.filter((a) => a.estado === "pendiente");

    const maxPts = parseFloat(this.proyecto?.porcentaje_proyecto || 100);
    const rango = `Rango: 0 – 100 (equivale al porcentaje obtenido del ${maxPts}%)`;

    this.lista.innerHTML = `
      <p class="rango-info"><i class="fas fa-info-circle"></i> ${rango}</p>

      <div class="grupo-lista">
        <h4><i class="fas fa-check-circle"></i> Entregados (${
          entregados.length
        })</h4>
        ${
          entregados.length
            ? entregados.map((a) => this.cardEntregado(a, maxPts)).join("")
            : "<p class='empty'>Ninguno</p>"
        }
      </div>

      <div class="grupo-lista">
        <h4><i class="fas fa-hourglass-half"></i> Pendientes (${
          pendientes.length
        })</h4>
        ${
          pendientes.length
            ? pendientes.map((a) => this.cardPendiente(a)).join("")
            : "<p class='empty'>Ninguno</p>"
        }
      </div>
    `;

    // Activar eventos de calificación
    this.lista.querySelectorAll(".input-calif").forEach((input) => {
      input.addEventListener("input", (e) => this.actualizarPuntos(e, maxPts));
    });

    this.lista.querySelectorAll(".btn-guardar-eval").forEach((btn) => {
      btn.addEventListener("click", (e) => this.guardarCalificacion(e));
    });
  },

  /* ============================================================
   🟢 Tarjeta de alumno con entrega
   ============================================================ */
  cardEntregado(a, maxPts) {
    const cal = a.calificacion ?? "";
    const puntosEq = cal ? ((cal / 100) * maxPts).toFixed(2) : "0.00";

    return `
    <div class="entrega-row">
      <div class="col-info">
        <strong>${a.nombre ?? ""} ${a.apellido_paterno ?? ""} ${
      a.apellido_materno ?? ""
    }</strong>
        <a href="${window.base_url}uploads/proyectos/${
      a.archivo
    }" target="_blank">
          <i class="fas fa-file"></i> ${a.archivo}
        </a>
        <small><i class="fas fa-clock"></i> ${new Date(
          a.fecha_entrega
        ).toLocaleString()}</small>
      </div>

      <div class="col-eval">
        <input type="number" min="0" max="100" value="${cal}" id="calif-${a.id}"
               class="input-calif" title="Ingresa un valor entre 0 y 100">
        <span class="pts" id="pts-${a.id}">${puntosEq} / ${maxPts} pts</span>
        <textarea id="retro-${a.id}" placeholder="Retroalimentación...">${
      a.retroalimentacion ?? ""
    }</textarea>
        <button class="btn-guardar-eval" data-id="${
          a.id
        }" title="Guardar calificación">
          <i class="fas fa-save"></i>
        </button>
      </div>
    </div>`;
  },

  /* ============================================================
   ⏳ Tarjeta de alumno pendiente
   ============================================================ */
  cardPendiente(a) {
    return `
    <div class="entrega-row pendiente">
      <div class="col-info">
        <strong>${a.nombre ?? ""} ${a.apellido_paterno ?? ""} ${
      a.apellido_materno ?? ""
    }</strong>
        <span class="status"><i class="fas fa-hourglass-half"></i> Aún no entrega</span>
      </div>
    </div>`;
  },

  /* ============================================================
     🔢 Actualizar puntos en tiempo real
     ============================================================ */
  actualizarPuntos(e, maxPts) {
    const id = e.target.id.split("-")[1];
    const val = parseFloat(e.target.value || 0);
    const puntosEq = ((val / 100) * maxPts).toFixed(2);
    const span = document.getElementById(`pts-${id}`);
    if (span) span.textContent = `${puntosEq} / ${maxPts} pts`;
  },

  /* ============================================================
     💾 Guardar calificación individual
     ============================================================ */
  async guardarCalificacion(e) {
    const id = e.currentTarget.dataset.id;
    const calificacion = document.getElementById(`calif-${id}`).value;
    const comentarios = document.getElementById(`retro-${id}`).value;

    const datos = new FormData();
    datos.append("calificacion", calificacion);
    datos.append("comentarios", comentarios);

    try {
      const res = await fetch(
        `${window.base_url}profesor/grupos/proyectos/calificar/${id}`,
        { method: "POST", body: datos }
      );
      const data = await res.json();
      if (data.success) mostrarAlerta(data.mensaje, "success");
      else mostrarAlerta(data.error || "Error al guardar", "error");
    } catch (err) {
      mostrarAlerta("Error de conexión: " + err.message, "error");
    }
  },
};

/* ============================================================
   🚀 Inicialización automática (por si se carga sola)
   ============================================================ */
document.addEventListener("DOMContentLoaded", () => {
  const section = document.querySelector(".entregas-section");
  if (section) window.ProyectosEntregasUI.init();
});
