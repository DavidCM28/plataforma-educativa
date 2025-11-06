window.TareasEntregasUI = {
  async init() {
    const section = document.querySelector(".entregas-section");
    if (!section) return;

    const tareaId = section.dataset.tarea;
    this.lista = document.getElementById("listaEntregas");

    try {
      const res = await fetch(
        `${window.base_url}profesor/grupos/tareas/entregas-lista/${tareaId}`
      );
      const data = await res.json();

      if (data.error) {
        this.lista.innerHTML = `<p class="error">${data.error}</p>`;
        return;
      }

      this.tarea = data.tarea; // guardamos info de la tarea
      this.render(data.alumnos);
    } catch (err) {
      this.lista.innerHTML = `<p class="error">Error: ${err.message}</p>`;
    }
  },

  render(alumnos) {
    const entregados = alumnos.filter((a) => a.estado === "entregado");
    const pendientes = alumnos.filter((a) => a.estado === "pendiente");

    const rango = "Rango: 0 – 100 (equivale al porcentaje obtenido)";

    this.lista.innerHTML = `
      <p class="rango-info"><i class="fas fa-info-circle"></i> ${rango}</p>

      <div class="grupo-lista">
        <h4><i class="fas fa-check-circle"></i> Entregados (${
          entregados.length
        })</h4>
        ${
          entregados.length
            ? entregados.map((a) => this.cardEntregado(a)).join("")
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

    document.querySelectorAll(".input-calif").forEach((input) => {
      input.addEventListener("input", (e) => this.actualizarPuntos(e));
    });

    document
      .querySelectorAll(".btn-guardar-eval")
      .forEach((btn) =>
        btn.addEventListener("click", (e) => this.guardarCalificacion(e))
      );
  },

  cardEntregado(a) {
    const e = a.entrega;
    const cal = e.calificacion ?? "";
    const maxPts = this.tarea.porcentaje_tarea || 0;
    const puntosEq = cal ? ((cal / 100) * maxPts).toFixed(2) : "0.00";

    return `
      <div class="entrega-row">
        <div class="col-info">
          <strong>${a.nombre} ${a.apellido_paterno ?? ""} ${
      a.apellido_materno ?? ""
    }</strong>
          <a href="${window.base_url}uploads/entregas/${
      e.archivo
    }" target="_blank">
            <i class="fas fa-file"></i> ${e.archivo}
          </a>
          <small><i class="fas fa-clock"></i> ${new Date(
            e.fecha_entrega
          ).toLocaleString()}</small>
        </div>

        <div class="col-eval">
          <input type="number" min="0" max="100" value="${cal}" id="calif-${
      e.id
    }" class="input-calif" title="Ingresa un valor entre 0 y 100">
          <span class="pts" id="pts-${e.id}">${puntosEq} / ${maxPts} pts</span>
          <textarea id="retro-${e.id}" placeholder="Retroalimentación...">${
      e.retroalimentacion ?? ""
    }</textarea>
          <button class="btn-guardar-eval" data-id="${
            e.id
          }" title="Guardar calificación">
            <i class="fas fa-save"></i>
          </button>
        </div>
      </div>`;
  },

  cardPendiente(a) {
    return `
      <div class="entrega-row pendiente">
        <div class="col-info">
          <strong>${a.nombre} ${a.apellido_paterno ?? ""} ${
      a.apellido_materno ?? ""
    }</strong>
          <span class="status"><i class="fas fa-hourglass-half"></i> Aún no entrega</span>
        </div>
      </div>`;
  },

  actualizarPuntos(e) {
    const id = e.target.id.split("-")[1];
    const val = parseFloat(e.target.value || 0);
    const maxPts = this.tarea.porcentaje_tarea || 0;
    const puntosEq = ((val / 100) * maxPts).toFixed(2);
    document.getElementById(
      `pts-${id}`
    ).textContent = `${puntosEq} / ${maxPts} pts`;
  },

  async guardarCalificacion(e) {
    const id = e.currentTarget.dataset.id;
    const calificacion = document.getElementById(`calif-${id}`).value;
    const retro = document.getElementById(`retro-${id}`).value;

    const datos = new FormData();
    datos.append("calificacion", calificacion);
    datos.append("retroalimentacion", retro);

    try {
      const res = await fetch(
        `${window.base_url}profesor/grupos/tareas/calificar/${id}`,
        { method: "POST", body: datos }
      );
      const data = await res.json();
      if (data.success) mostrarAlerta(data.mensaje, "success");
      else mostrarAlerta(data.error || "Error al guardar", "error");
    } catch {
      mostrarAlerta("Error de conexión", "error");
    }
  },
};

document.addEventListener("DOMContentLoaded", () => {
  const section = document.querySelector(".entregas-section");
  if (section) window.TareasEntregasUI.init();
});
