/* ============================================================
   📘 MÓDULO DE EXÁMENES - PROFESOR (VERSIÓN CORREGIDA)
   ============================================================ */
window.ExamenesUI = (() => {
  let asignacionId, lista, filtroEstado;

  // ============================================================
  // Inicializar módulo
  // ============================================================
  async function inicializar(asigId) {
    asignacionId = asigId;

    lista = document.getElementById("listaExamenes");
    filtroEstado = document.getElementById("filtroEstadoExamen");

    // Si la vista no cargó aún, evitar errores
    if (!lista) return;

    // Listener del filtro
    if (filtroEstado) {
      filtroEstado.addEventListener("change", cargarExamenes);
    }

    // Botón nuevo examen
    const btnNuevo = document.getElementById("btnNuevoExamen");
    if (btnNuevo) {
      btnNuevo.addEventListener("click", () => {
        window.location.href = `${window.base_url}profesor/grupos/examenes/crear/${asignacionId}`;
      });
    }

    // Delegación global para acciones
    document.addEventListener("click", manejarAcciones);

    cargarExamenes();
  }

  // ============================================================
  // Cargar exámenes
  // ============================================================
  async function cargarExamenes() {
    lista.innerHTML = `<p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando exámenes...</p>`;

    try {
      const res = await fetch(
        `${window.base_url}profesor/grupos/listar-examenes/${asignacionId}`
      );
      let data = await res.json();

      if (!Array.isArray(data)) data = [];

      // Aplicar filtro por estado
      const estadoSel = filtroEstado?.value ?? "";
      if (estadoSel) {
        data = data.filter((e) => e.estado === estadoSel);
      }

      if (data.length === 0) {
        lista.innerHTML = `<p class="placeholder">No hay exámenes registrados.</p>`;
        return;
      }

      renderExamenes(data);
    } catch (err) {
      lista.innerHTML = `<p class="error">❌ ${err.message}</p>`;
    }
  }

  // ============================================================
  // Renderizar exámenes en tabla estilo proyectos/tareas
  // ============================================================
  function renderExamenes(rows) {
    lista.innerHTML = "";

    const tabla = document.createElement("table");
    tabla.className = "tabla-tareas";

    tabla.innerHTML = `
      <thead>
    <tr>
        <th>Título</th>
        <th>Descripción</th>
        <th>Tiempo</th>
        <th>Puntos</th>
        <th>% en parcial</th> 
        <th>Estado</th>
        <th>Acciones</th>
    </tr>
</thead>
<tbody></tbody>
    `;

    const tbody = tabla.querySelector("tbody");

    rows.forEach((x) => {
      const fila = document.createElement("tr");

      fila.innerHTML = `
  <td>${x.titulo}</td>
  <td>${x.descripcion ?? "Sin descripción"}</td>
  <td>${x.tiempo_minutos ?? "—"} min</td>
  <td>${x.puntos_totales ?? 0}</td>
  <td>${x.porcentaje ?? 0}%</td>
  <td>${x.estado ?? "borrador"}</td>

  <td class="acciones">
    <button class="btn-editar" data-id="${x.id}" title="Editar">
        <i class="fas fa-edit"></i>
    </button>
    <button class="btn-eliminar" data-id="${x.id}" title="Eliminar">
        <i class="fas fa-trash"></i>
    </button>
    <button class="btn-publicar" data-id="${x.id}" title="Publicar">
        <i class="fas fa-bullhorn"></i>
    </button>
    <button class="btn-cerrar" data-id="${x.id}" title="Cerrar">
        <i class="fas fa-lock"></i>
    </button>
    <button class="btn-respuestas" data-id="${x.id}" title="Respuestas">
        <i class="fas fa-users"></i>
    </button>
  </td>
`;

      tbody.appendChild(fila);
    });

    lista.appendChild(tabla);
  }

  // ============================================================
  // Acciones (editar, eliminar, publicar, cerrar, respuestas)
  // ============================================================
  async function manejarAcciones(e) {
    const btn = e.target.closest(
      ".btn-editar, .btn-eliminar, .btn-publicar, .btn-cerrar, .btn-respuestas"
    );
    if (!btn) return;

    const id = btn.dataset.id;

    // Editar
    if (btn.classList.contains("btn-editar")) {
      window.location.href = `${window.base_url}profesor/grupos/examenes/editar/${id}`;
      return;
    }

    // Eliminar
    if (btn.classList.contains("btn-eliminar")) {
      if (!confirm("¿Eliminar examen?")) return;

      const res = await fetch(
        `${window.base_url}profesor/grupos/eliminar-examen/${id}`,
        { method: "DELETE" }
      );
      const data = await res.json();

      if (data.success) {
        mostrarAlerta(data.mensaje, "success");
        cargarExamenes();
      } else {
        mostrarAlerta(data.error || "Error al eliminar", "error");
      }
      return;
    }

    // Publicar
    if (btn.classList.contains("btn-publicar")) {
      const res = await fetch(
        `${window.base_url}profesor/grupos/publicar-examen/${id}`,
        { method: "POST" }
      );
      const data = await res.json();

      if (data.success) {
        mostrarAlerta(data.mensaje, "success");
        cargarExamenes();
      }
      return;
    }

    // Cerrar
    if (btn.classList.contains("btn-cerrar")) {
      const res = await fetch(
        `${window.base_url}profesor/grupos/cerrar-examen/${id}`,
        { method: "POST" }
      );
      const data = await res.json();

      if (data.success) {
        mostrarAlerta(data.mensaje, "success");
        cargarExamenes();
      }
      return;
    }

    // Ver respuestas
    if (btn.classList.contains("btn-respuestas")) {
      window.location.href = `${window.base_url}profesor/grupos/examenes/respuestas/${id}`;
      return;
    }
  }

  return { inicializar };
})();
