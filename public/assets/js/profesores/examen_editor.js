/* ============================================================
   ✏️ MODO EDITOR DE EXÁMENES (crear/editar individual)
   ============================================================ */
document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("btnAgregarPregunta");
  const btnGuardar = document.getElementById("btnGuardarExamen");
  const contPreg = document.getElementById("contenedorPreguntas");
  const form = document.getElementById("formExamen");

  if (!form || !contPreg) return;

  // ==============================
  // ➕ Agregar nueva pregunta
  // ==============================
  btnAgregar?.addEventListener("click", () => agregarPreguntaUI());

  function agregarPreguntaUI(p = null) {
    const i = contPreg.children.length;
    const wrap = document.createElement("div");
    wrap.className = "tarea-card pregunta-card";
    wrap.innerHTML = `
      <div class="preg-header">
        <strong>Pregunta ${i + 1}</strong>
        <div>
          <button type="button" class="btn-sec eliminar-pregunta">Eliminar</button>
        </div>
      </div>

      <label>Tipo de pregunta</label>
      <select class="preg-tipo">
        <option value="opcion">Opción múltiple</option>
        <option value="abierta">Respuesta abierta</option>
      </select>

      <label>Enunciado</label>
      <textarea class="preg-texto" rows="2"></textarea>

      <label>Imagen (opcional)</label>
      <input type="file" name="pregunta_imagen_${i}">

      <div class="opciones-wrap">
        <div class="op-header">
          <strong>Opciones</strong>
          <button type="button" class="btn-sec add-opcion">Agregar opción</button>
        </div>
        <div class="lista-opciones"></div>
      </div>
    `;
    contPreg.appendChild(wrap);

    const tipo = wrap.querySelector(".preg-tipo");
    const opciones = wrap.querySelector(".opciones-wrap");

    // alternar visibilidad de opciones
    tipo.addEventListener("change", () => {
      opciones.style.display = tipo.value === "opcion" ? "" : "none";
    });

    // agregar opción
    wrap.querySelector(".add-opcion").addEventListener("click", () => {
      const div = document.createElement("div");
      div.className = "opcion-card";
      div.innerHTML = `
        <label class="chk-line">
          <input type="checkbox" class="op-correcta">
          <span>Correcta</span>
        </label>
        <input type="text" class="op-texto" placeholder="Texto de la opción">
        <button type="button" class="btn-sec eliminar-opcion">Eliminar</button>
      `;
      div
        .querySelector(".eliminar-opcion")
        .addEventListener("click", () => div.remove());
      wrap.querySelector(".lista-opciones").appendChild(div);
    });

    // eliminar pregunta
    wrap.querySelector(".eliminar-pregunta").addEventListener("click", () => {
      wrap.remove();
      // renumerar
      [...contPreg.children].forEach((c, idx) => {
        c.querySelector("strong").textContent = `Pregunta ${idx + 1}`;
      });
    });
  }

  // ==============================
  // 💾 Guardar examen (AJAX)
  // ==============================
  btnGuardar?.addEventListener("click", async (e) => {
    e.preventDefault();

    const preguntas = [...contPreg.children].map((wrap, idx) => {
      const tipo = wrap.querySelector(".preg-tipo").value;
      const opciones =
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
        id: wrap.dataset.id || null,
        tipo,
        pregunta: wrap.querySelector(".preg-texto").value.trim(),
        puntos: 1,
        orden: idx + 1,
        opciones,
      };
    });

    const fd = new FormData(form);
    fd.append("preguntas", JSON.stringify(preguntas));

    // incluir archivos (imágenes)
    [...contPreg.children].forEach((wrap, idx) => {
      const input = wrap.querySelector(`input[name="pregunta_imagen_${idx}"]`);
      if (input && input.files[0]) {
        fd.set(`pregunta_imagen_${idx}`, input.files[0]);
      }
    });

    btnGuardar.disabled = true;
    btnGuardar.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Guardando...`;

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
        mostrarAlerta("✅ Examen guardado correctamente", "success");
        if (data.id) {
          window.history.replaceState(
            {},
            "",
            window.location.href.replace(/\/crear\/\d+/, `/editar/${data.id}`)
          );
        }
      } else {
        mostrarAlerta(data.error || "Error al guardar examen", "error");
      }
    } catch (err) {
      console.error(err);
      mostrarAlerta("❌ Error de conexión o servidor", "error");
    } finally {
      btnGuardar.disabled = false;
      btnGuardar.innerHTML = `<i class="fas fa-save"></i> Guardar`;
    }
  });
});
