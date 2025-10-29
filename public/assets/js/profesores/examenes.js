/* ============================================================
   📘 MÓDULO DE EXÁMENES - PROFESOR
   ============================================================ */
window.ExamenesUI = (() => {
  let asignacionId, lista, modal, form, contPreg, btnNuevo;

  // Inicializar módulo
  async function inicializar(asigId) {
    asignacionId = asigId;
    lista = document.getElementById("listaExamenes");
    modal = document.getElementById("modalExamen");
    form = document.getElementById("formExamen");
    contPreg = document.getElementById("contenedorPreguntas");
    btnNuevo = document.getElementById("btnNuevoExamen");

    if (!lista || !form) return;

    btnNuevo.addEventListener("click", () => {
      window.location.href = `${window.base_url}profesor/grupos/examenes/crear/${asignacionId}`;
    });

    modal
      .querySelectorAll(".close, .cerrar-modal")
      .forEach((b) =>
        b.addEventListener("click", () => modal.classList.add("hidden"))
      );

    document
      .getElementById("btnAgregarPregunta")
      .addEventListener("click", () => agregarPreguntaUI(null));

    form.addEventListener("submit", guardarExamen);
    document.addEventListener("click", manejarAcciones);

    cargarExamenes();
  }

  // =============================
  // Cargar exámenes
  // =============================
  async function cargarExamenes() {
    lista.innerHTML = `<p class="placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando exámenes...</p>`;
    try {
      const res = await fetch(
        `${window.base_url}profesor/grupos/listar-examenes/${asignacionId}`
      );
      const data = await res.json();
      if (!Array.isArray(data) || data.length === 0) {
        lista.innerHTML = `<p class="placeholder">No hay exámenes registrados.</p>`;
        return;
      }
      renderExamenes(data);
    } catch (err) {
      lista.innerHTML = `<p class="error">❌ ${err.message}</p>`;
    }
  }

  function renderExamenes(rows) {
    lista.innerHTML = "";
    rows.forEach((x) => {
      const card = document.createElement("div");
      card.className = "tarea-card";
      const estadoBadge =
        x.estado === "publicado" ? "🟢" : x.estado === "cerrado" ? "🔒" : "📝";

      card.innerHTML = `
        <div class="tarea-info">
          <h4>${estadoBadge} ${x.titulo}</h4>
          <p>${x.descripcion ?? ""}</p>
          <div class="tarea-meta">
            <span><i class="fas fa-clock"></i> Tiempo: ${
              x.tiempo_minutos ?? "—"
            } min</span>
            <span><i class="fas fa-star"></i> Puntos: ${
              x.puntos_totales ?? 0
            }</span>
          </div>
        </div>
        <div class="tarea-acciones">
          <button class="btn-editar" data-id="${
            x.id
          }" title="Editar"><i class="fas fa-edit"></i></button>
          <button class="btn-eliminar" data-id="${
            x.id
          }" title="Eliminar"><i class="fas fa-trash"></i></button>
          <button class="btn-publicar" data-id="${
            x.id
          }" title="Publicar"><i class="fas fa-bullhorn"></i></button>
          <button class="btn-cerrar" data-id="${
            x.id
          }" title="Cerrar"><i class="fas fa-lock"></i></button>
          <button class="btn-respuestas" data-id="${
            x.id
          }" title="Ver respuestas"><i class="fas fa-users"></i></button>
        </div>
      `;
      lista.appendChild(card);
    });
  }

  // =============================
  // Modal
  // =============================
  async function abrirModal(id = null) {
    modal.classList.remove("hidden");
    form.reset();
    contPreg.innerHTML = "";
    document.getElementById("examenId").value = id || "";

    document.getElementById("tituloModalExamen").innerHTML = id
      ? "<i class='fas fa-edit'></i> Editar examen"
      : "<i class='fas fa-file-alt'></i> Nuevo examen";

    if (id) {
      try {
        const res = await fetch(
          `${window.base_url}profesor/grupos/detalle-examen/${id}`
        );
        const data = await res.json();
        if (data.error) return mostrarAlerta(data.error, "error");

        document.getElementById("tituloExamen").value = data.titulo ?? "";
        document.getElementById("descripcionExamen").value =
          data.descripcion ?? "";
        document.getElementById("instruccionesExamen").value =
          data.instrucciones ?? "";
        if (data.tiempo_minutos)
          document.getElementById("tiempoExamen").value = data.tiempo_minutos;
        if (data.intentos_maximos)
          document.getElementById("intentosExamen").value =
            data.intentos_maximos;
        if (data.fecha_publicacion)
          document.getElementById("pubExamen").value =
            data.fecha_publicacion.replace(" ", "T");
        if (data.fecha_cierre)
          document.getElementById("cierreExamen").value =
            data.fecha_cierre.replace(" ", "T");

        (data.preguntas ?? []).forEach((p, idx) => agregarPreguntaUI(p, idx));
      } catch (e) {
        mostrarAlerta("Error al cargar examen: " + e.message, "error");
      }
    }
  }

  // =============================
  // Crear pregunta
  // =============================
  function agregarPreguntaUI(p = null) {
    const i = contPreg.children.length;
    const tipo = p?.tipo ?? "opcion";
    const puntos = p?.puntos ?? 1;
    const orden = p?.orden ?? i + 1;
    const pregunta = p?.pregunta ?? "";

    const wrap = document.createElement("div");
    wrap.className = "tarea-card";
    wrap.dataset.index = i;

    wrap.innerHTML = `
      <div class="preg-header">
        <strong>Pregunta ${i + 1}</strong>
        <div class="preg-actions">
          <button type="button" class="btn-sec btn-up">▲</button>
          <button type="button" class="btn-sec btn-down">▼</button>
          <button type="button" class="btn-eliminar-pregunta btn-danger">Eliminar</button>
        </div>
      </div>
      <input type="hidden" class="preg-id" value="${p?.id ?? ""}">
      <label>Tipo</label>
      <select class="preg-tipo">
        <option value="opcion" ${
          tipo === "opcion" ? "selected" : ""
        }>Opción múltiple</option>
        <option value="abierta" ${
          tipo === "abierta" ? "selected" : ""
        }>Respuesta abierta</option>
      </select>
      <label>Enunciado</label>
      <textarea class="preg-texto" rows="2">${pregunta}</textarea>
      <div class="grid">
        <div><label>Puntos</label><input type="number" class="preg-puntos" min="0" step="0.5" value="${puntos}"></div>
        <div><label>Orden</label><input type="number" class="preg-orden" min="1" value="${orden}"></div>
      </div>
      <label>Imagen (opcional)</label>
      <input type="file" name="pregunta_imagen_${i}">
      <div class="opciones-wrap" ${
        tipo === "abierta" ? 'style="display:none"' : ""
      }>
        <div class="op-header">
          <strong>Opciones</strong>
          <button type="button" class="btn-sec btn-add-opcion"><i class="fas fa-plus"></i> Agregar opción</button>
        </div>
        <div class="lista-opciones"></div>
      </div>`;

    contPreg.appendChild(wrap);

    const listaOps = wrap.querySelector(".lista-opciones");
    if (tipo === "opcion") {
      const ops = p?.opciones ?? [
        { texto: "Opción A", es_correcta: 1 },
        { texto: "Opción B", es_correcta: 0 },
      ];
      ops.forEach((op) => agregarOpcionUI(listaOps, op));
    }

    // eventos internos
    wrap.querySelector(".preg-tipo").addEventListener("change", (ev) => {
      wrap.querySelector(".opciones-wrap").style.display =
        ev.target.value === "opcion" ? "" : "none";
    });

    wrap
      .querySelector(".btn-add-opcion")
      .addEventListener("click", () => agregarOpcionUI(listaOps));

    wrap
      .querySelector(".btn-eliminar-pregunta")
      .addEventListener("click", () => {
        wrap.remove();
        [...contPreg.children].forEach(
          (c, ix) =>
            (c.querySelector("strong").textContent = `Pregunta ${ix + 1}`)
        );
      });

    wrap.querySelector(".btn-up").addEventListener("click", () => {
      const prev = wrap.previousElementSibling;
      if (prev) contPreg.insertBefore(wrap, prev);
    });
    wrap.querySelector(".btn-down").addEventListener("click", () => {
      const next = wrap.nextElementSibling;
      if (next) contPreg.insertBefore(next, wrap);
    });
  }

  function agregarOpcionUI(lista, op = null) {
    const row = document.createElement("div");
    row.className = "tarea-card opcion-card";
    row.innerHTML = `
      <label class="chk-line">
        <input type="checkbox" class="op-correcta" ${
          op?.es_correcta ? "checked" : ""
        }>
        <span>Correcta</span>
      </label>
      <input type="text" class="op-texto" placeholder="Texto de la opción" value="${
        op?.texto ?? ""
      }">
      <button type="button" class="btn-sec btn-del-opcion">Eliminar opción</button>
    `;
    lista.appendChild(row);
    row
      .querySelector(".btn-del-opcion")
      .addEventListener("click", () => row.remove());
  }

  // =============================
  // Guardar examen
  // =============================
  async function guardarExamen(e) {
    e.preventDefault();

    const preguntas = [...contPreg.children].map((wrap, idx) => {
      const tipo = wrap.querySelector(".preg-tipo").value;
      const ops =
        tipo === "opcion"
          ? [...wrap.querySelectorAll(".lista-opciones .opcion-card")].map(
              (r, j) => ({
                texto: r.querySelector(".op-texto").value,
                es_correcta: r.querySelector(".op-correcta").checked ? 1 : 0,
                orden: j + 1,
              })
            )
          : [];
      return {
        id: wrap.querySelector(".preg-id").value || null,
        tipo,
        pregunta: wrap.querySelector(".preg-texto").value,
        puntos: parseFloat(wrap.querySelector(".preg-puntos").value || "1"),
        orden: parseInt(wrap.querySelector(".preg-orden").value || idx + 1),
        opciones: ops,
      };
    });

    const fd = new FormData(form);
    fd.append("preguntas", JSON.stringify(preguntas));

    [...contPreg.children].forEach((wrap, idx) => {
      const input = wrap.querySelector(`input[name="pregunta_imagen_${idx}"]`);
      if (input && input.files[0])
        fd.set(`pregunta_imagen_${idx}`, input.files[0]);
    });

    try {
      const res = await fetch(
        `${window.base_url}profesor/grupos/guardar-examen`,
        {
          method: "POST",
          body: fd,
        }
      );
      const data = await res.json();
      if (data.success) {
        mostrarAlerta("Examen guardado correctamente", "success");
        modal.classList.add("hidden");
        cargarExamenes();
      } else mostrarAlerta(data.error || "Error al guardar", "error");
    } catch (err) {
      mostrarAlerta("Error: " + err.message, "error");
    }
  }

  // =============================
  // Acciones (editar, eliminar, publicar, cerrar, respuestas)
  // =============================
  async function manejarAcciones(e) {
    const btn = e.target.closest(
      ".btn-editar, .btn-eliminar, .btn-publicar, .btn-cerrar, .btn-respuestas"
    );
    if (!btn) return;

    const id = btn.dataset.id;
    if (btn.classList.contains("btn-editar")) {
      window.location.href = `${window.base_url}profesor/grupos/examenes/editar/${id}`;
      return;
    }

    if (btn.classList.contains("btn-eliminar")) {
      if (!confirm("¿Eliminar examen?")) return;
      const res = await fetch(
        `${window.base_url}profesor/grupos/eliminar-examen/${id}`,
        {
          method: "DELETE",
        }
      );
      const data = await res.json();
      if (data.success) {
        mostrarAlerta(data.mensaje, "success");
        cargarExamenes();
      } else mostrarAlerta(data.error || "Error al eliminar", "error");
    }

    if (btn.classList.contains("btn-publicar")) {
      const res = await fetch(
        `${window.base_url}profesor/grupos/publicar-examen/${id}`,
        {
          method: "POST",
        }
      );
      const data = await res.json();
      if (data.success) {
        mostrarAlerta(data.mensaje, "success");
        cargarExamenes();
      }
    }

    if (btn.classList.contains("btn-cerrar")) {
      const res = await fetch(
        `${window.base_url}profesor/grupos/cerrar-examen/${id}`,
        {
          method: "POST",
        }
      );
      const data = await res.json();
      if (data.success) {
        mostrarAlerta(data.mensaje, "success");
        cargarExamenes();
      }
    }

    if (btn.classList.contains("btn-respuestas")) {
      const res = await fetch(
        `${window.base_url}profesor/grupos/respuestas-examen/${id}`
      );
      const data = await res.json();
      mostrarAlerta(
        `📋 Respuestas registradas: ${
          Array.isArray(data) ? data.length : data.respuestas ?? 0
        }`,
        "info",
        4000
      );
    }
  }

  return { inicializar };
})();
