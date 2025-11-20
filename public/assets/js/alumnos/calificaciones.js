document.addEventListener("DOMContentLoaded", () => {
  const baseURL = window.base_url;

  const vistaTabla = document.getElementById("vista-tabla-general");
  const vistaCriterios = document.getElementById("vista-criterios");

  const tbodyCiclo = document.getElementById("tbodyCiclo");
  const theadCiclo = document.getElementById("theadCiclo");

  const tbodyCriterios = document.getElementById("tbodyCriterios");
  const critMateriaNombre = document.getElementById("critMateriaNombre");
  const critParcialNumero = document.getElementById("critParcialNumero");
  const critParcialFinal = document.getElementById("critParcialFinal");

  const btnVolver = document.getElementById("btnVolverTabla");

  // =======================================================
  // 1️⃣ Cargar tabla general
  // =======================================================
  async function cargarTablaCiclo() {
    const resp = await fetch(`${baseURL}alumno/calificaciones/tabla-ciclo`);
    const data = await resp.json();

    renderTablaGeneral(data);
  }

  // Render dinámico de tabla general
  function renderTablaGeneral(data) {
    // Crear encabezados dinámicos de parciales
    let head = `<tr><th>Materia</th>`;
    data.parciales.forEach((p) => {
      head += `<th>Parcial ${p.numero}</th>`;
    });
    head += `<th>Final</th></tr>`;

    theadCiclo.innerHTML = head;

    // Crear filas
    let rows = "";

    data.materias.forEach((mat) => {
      let row = `<tr><td>${mat.materia}</td>`;

      data.parciales.forEach((p) => {
        const cal = mat.parciales[p.numero] ?? "—";

        row += `
                    <td class="td-parcial" 
                        data-asignacion="${mat.asignacion_id}" 
                        data-parcial="${p.numero}">
                        ${cal}
                    </td>`;
      });

      row += `<td>${mat.final ?? "—"}</td></tr>`;

      rows += row;
    });

    tbodyCiclo.innerHTML = rows;

    // Activar clics en cada parcial
    document.querySelectorAll(".td-parcial").forEach((td) => {
      td.addEventListener("click", () => {
        const asignacionId = td.dataset.asignacion;
        const parcialNum = td.dataset.parcial;

        cargarCriterios(asignacionId, parcialNum);
      });
    });
  }

  // =======================================================
  // 2️⃣ Cargar criterios al dar clic en un parcial
  // =======================================================
  async function cargarCriterios(asignacionId, parcialNum) {
    vistaTabla.classList.add("hidden");
    vistaCriterios.classList.remove("hidden");

    const resp = await fetch(
      `${baseURL}alumno/calificaciones/criterios/${asignacionId}/${parcialNum}`
    );
    const data = await resp.json();

    critMateriaNombre.textContent = data.materia;
    critParcialNumero.textContent = parcialNum;
    critParcialFinal.textContent = data.final;

    let html = "";
    data.items.forEach((i) => {
      html += `
<tr>
    <td>${i.criterio}</td>
    <td>${i.porcentaje ?? "—"}%</td>
    <td>${i.calificacion}</td>
    <td>${i.porcentaje_obtenido ?? "—"}%</td>
</tr>`;
    });

    tbodyCriterios.innerHTML = html;
  }

  // =======================================================
  // 3️⃣ Botón volver
  // =======================================================
  btnVolver.addEventListener("click", () => {
    vistaCriterios.classList.add("hidden");
    vistaTabla.classList.remove("hidden");
  });

  // Inicializar
  cargarTablaCiclo();
});
