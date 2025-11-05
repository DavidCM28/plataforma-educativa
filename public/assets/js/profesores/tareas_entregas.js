window.TareasEntregasUI = {
  async init() {
    const section = document.querySelector(".entregas-section");
    if (!section) return; // 👈 Evita el error si la vista aún no existe

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

      this.render(data.alumnos);
    } catch (err) {
      this.lista.innerHTML = `<p class="error">Error: ${err.message}</p>`;
    }
  },

  render(alumnos) {
    const entregados = alumnos.filter((a) => a.estado === "entregado");
    const pendientes = alumnos.filter((a) => a.estado === "pendiente");

    this.lista.innerHTML = `
      <div class="grupo-lista">
        <h4>📥 Entregados (${entregados.length})</h4>
        ${
          entregados.length
            ? entregados.map(this.cardEntregado).join("")
            : "<p>Ninguno</p>"
        }
      </div>
      <div class="grupo-lista">
        <h4>⌛ Pendientes (${pendientes.length})</h4>
        ${
          pendientes.length
            ? pendientes.map(this.cardPendiente).join("")
            : "<p>Ninguno</p>"
        }
      </div>
    `;

    document
      .querySelectorAll(".btn-guardar-eval")
      .forEach((b) =>
        b.addEventListener("click", (e) => this.guardarCalificacion(e))
      );
  },

  cardEntregado(a) {
    const e = a.entrega;
    return `
      <div class="entrega-card">
        <div class="entrega-info">
          <h5>${a.nombre} ${a.apellido_paterno ?? ""} ${
      a.apellido_materno ?? ""
    }</h5>
          <p><i class="fas fa-clock"></i> ${new Date(
            e.fecha_entrega
          ).toLocaleString()}</p>
          <a href="${window.base_url}uploads/entregas/${
      e.archivo
    }" target="_blank">
            <i class="fas fa-file"></i> ${e.archivo}
          </a>
        </div>
        <div class="entrega-eval">
          <label>Calificación</label>
          <input type="number" min="0" max="100" value="${
            e.calificacion ?? ""
          }" id="calif-${e.id}" class="input-calif">

          <label>Retroalimentación</label>
          <textarea id="retro-${e.id}" rows="2">${
      e.retroalimentacion ?? ""
    }</textarea>

          <button class="btn-main btn-guardar-eval" data-id="${e.id}">
            <i class="fas fa-save"></i> Guardar
          </button>
        </div>
      </div>`;
  },

  cardPendiente(a) {
    return `
      <div class="entrega-card pendiente">
        <h5>${a.nombre} ${a.apellido_paterno ?? ""} ${
      a.apellido_materno ?? ""
    }</h5>
        <p><i class="fas fa-hourglass-half"></i> Aún no entrega</p>
      </div>`;
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
        {
          method: "POST",
          body: datos,
        }
      );
      const data = await res.json();
      if (data.success) mostrarAlerta(data.mensaje, "success");
      else mostrarAlerta(data.error || "Error al guardar", "error");
    } catch (err) {
      mostrarAlerta("Error de conexión", "error");
    }
  },
};

// 👇 Previene errores si la vista no tiene .entregas-section
document.addEventListener("DOMContentLoaded", () => {
  const section = document.querySelector(".entregas-section");
  if (section) window.TareasEntregasUI.init();
});
