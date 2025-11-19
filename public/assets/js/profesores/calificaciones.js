document.addEventListener("DOMContentLoaded", () => {
  const selectAsignacion = document.getElementById("selectAsignacion");
  const selectParcial = document.getElementById("selectParcial");
  const selectorParcialBox = document.getElementById("selectorParcial");
  const contenedorAlumnos = document.getElementById("contenedorAlumnos");
  const tablaCalificaciones = document.getElementById("tablaCalificaciones");

  /* ==========================================================
        1. Seleccionar asignación → cargar alumnos
     ========================================================== */
  selectAsignacion.addEventListener("change", async () => {
    const id = selectAsignacion.value;

    selectorParcialBox.classList.add("hidden");
    tablaCalificaciones.classList.add("hidden");
    tablaCalificaciones.innerHTML = "";
    selectParcial.value = "";

    if (!id) {
      contenedorAlumnos.innerHTML = `<p class="placeholder">Selecciona una asignación.</p>`;
      return;
    }

    contenedorAlumnos.innerHTML = `<p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando alumnos...</p>`;

    const res = await fetch(
      `${window.base_url}profesor/grupos/calificaciones/alumnos/${id}`,
      { headers: { "X-Requested-With": "XMLHttpRequest" } }
    );

    const data = await res.json();
    if (!data.alumnos || data.alumnos.length === 0) {
      contenedorAlumnos.innerHTML = `<p class="placeholder">No hay alumnos registrados.</p>`;
      return;
    }

    window.__alumnosCargados = data.alumnos;

    contenedorAlumnos.innerHTML = `<p class="placeholder">Selecciona un parcial para cargar los ítems calificables.</p>`;
    selectorParcialBox.classList.remove("hidden");
  });

  /* ==========================================================
        2. Seleccionar parcial → obtener ITEMS y dibujar tabla
     ========================================================== */
  selectParcial.addEventListener("change", async () => {
    const parcial = selectParcial.value;
    const asignacionId = selectAsignacion.value;

    if (!parcial || !asignacionId) return;

    // Obtener estructura COMPLETA
    const res = await fetch(
      `${window.base_url}profesor/grupos/calificaciones/valores/${asignacionId}/${parcial}`,
      { headers: { "X-Requested-With": "XMLHttpRequest" } }
    );

    const { criterios, calificaciones } = await res.json();

    if (!criterios || Object.keys(criterios).length === 0) {
      tablaCalificaciones.innerHTML = `<p class="placeholder">No hay criterios configurados.</p>`;
      tablaCalificaciones.classList.remove("hidden");
      return;
    }

    const alumnos = window.__alumnosCargados;

    // ==========================================
    // CONSTRUIR CABECERA (criterios agrupados)
    // ==========================================
    let header1 = `<tr><th rowspan="2">Alumno</th>`;
    let header2 = `<tr>`;

    const criterioIds = Object.entries(criterios).map(([cid]) => parseInt(cid));

    // CABECERA
    criterioIds.forEach((cid) => {
      const c = criterios[cid];
      const span = c.items.length > 0 ? c.items.length : 1;

      header1 += `
    <th colspan="${span}">
      ${c.nombre} <small>(${c.porcentaje}%)</small>
    </th>
  `;

      if (c.items.length === 0) {
        header2 += `<th>—</th>`;
      } else {
        c.items.forEach((it) => {
          header2 += `
  <th>
    ${it.titulo}
    <br>
    <small>${it.porcentaje ? it.porcentaje + "%" : ""}</small>
  </th>
`;
        });
      }
    });

    // ==========================================
    // CUERPO DE LA TABLA
    // ==========================================
    let body = ``;

    alumnos.forEach((al) => {
      body += `<tr><td>${al.nombre} ${al.apellido_paterno} ${al.apellido_materno}</td>`;

      criterioIds.forEach((cid) => {
        const c = criterios[cid];

        if (c.items.length === 0) {
          body += `
      <td>
        <select disabled><option>—</option></select>
      </td>
    `;
          return;
        }

        c.items.forEach((it) => {
          const val = calificaciones?.[al.alumno_id]?.[it.id] ?? "";

          body += `
<td>
  <select 
    data-alumno="${al.alumno_id}" 
    data-item="${it.id}"
    data-tipo="${it.tipo}"
    data-criterio="${cid}">
    
    <option value="" ${val === "" ? "selected" : ""}>--</option>
    <option value="0" ${val == 0 ? "selected" : ""}>0</option>
    <option value="5" ${val == 5 ? "selected" : ""}>5</option>
    <option value="6" ${val == 6 ? "selected" : ""}>6</option>
    <option value="7" ${val == 7 ? "selected" : ""}>7</option>
    <option value="8" ${val == 8 ? "selected" : ""}>8</option>
    <option value="9" ${val == 9 ? "selected" : ""}>9</option>
    <option value="10" ${val == 10 ? "selected" : ""}>10</option>
  </select>
</td>
`;
        });
      });

      body += `</tr>`;
    });

    // ==========================================
    // RENDER FINAL
    // ==========================================
    tablaCalificaciones.innerHTML = `
    <table class="tabla">
      <thead>
        ${header1}
        ${header2}
      </thead>
      <tbody>
        ${body}
      </tbody>
    </table>

    <button id="btnGuardarCalificaciones" class="btn-main">
      Guardar calificaciones
    </button>
  `;

    tablaCalificaciones.classList.remove("hidden");
    document
      .getElementById("btnGuardarCalificaciones")
      .addEventListener("click", guardarCalificaciones);
  });
});

async function guardarCalificaciones() {
  const asignacionId = document.getElementById("selectAsignacion").value;
  const parcial = document.getElementById("selectParcial").value;

  // Obtener ciclo_id desde el select
  const option = document.querySelector(
    `#selectAsignacion option[value="${asignacionId}"]`
  );
  const cicloId = option?.dataset.ciclo;

  if (!cicloId) {
    console.error("No se encontró ciclo_id en el option seleccionado");
    mostrarAlerta("No se pudo identificar el ciclo de la asignación.", "error");
    return;
  }

  // 📌 Buscar el ciclo_parcial_id REAL en el backend
  const cicloRes = await fetch(
    `${window.base_url}api/ciclo-parcial/${cicloId}/${parcial}`,
    {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    }
  );

  const ciclo = await cicloRes.json();
  console.log("Respuesta ciclo-parcial:", ciclo);

  if (!ciclo || ciclo.error || !ciclo.id) {
    console.error("Error API ciclo-parcial:", ciclo);
    mostrarAlerta("No se pudo obtener el ciclo_parcial_id", "error");
    return;
  }

  const cicloParcialId = ciclo.id;

  const selects = document.querySelectorAll(
    "#tablaCalificaciones select[data-item]"
  );

  const items = [];

  selects.forEach((sel) => {
    const val = sel.value;
    if (val === "") return;

    items.push({
      alumno_id: sel.dataset.alumno,
      item_id: sel.dataset.item,
      tipo: sel.dataset.tipo,
      criterio_id: sel.dataset.criterio,
      calificacion: parseInt(val),
    });
  });

  const payload = {
    asignacion_id: asignacionId,
    ciclo_parcial_id: cicloParcialId,
    items,
  };

  const res = await fetch(
    `${window.base_url}profesor/grupos/calificaciones/guardar`,
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify(payload),
    }
  );

  const data = await res.json();
  console.log("Respuesta guardarCalificaciones:", data);

  mostrarAlerta(data.msg || "Calificaciones guardadas", "success");
}
