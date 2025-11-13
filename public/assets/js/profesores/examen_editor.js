/* ============================================================
   ✏️ MODO EDITOR DE EXÁMENES (crear/editar individual)
   ============================================================ */
document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("btnAgregarPregunta");
  const btnGuardar = document.getElementById("btnGuardarExamen");
  const contPreg = document.getElementById("contenedorPreguntas");
  const form = document.getElementById("formExamen");
  const tiempoInput = form.querySelector('[name="tiempo_minutos"]');
  const previewTiempo = document.getElementById("previewTiempo");

  if (tiempoInput && previewTiempo) {
    tiempoInput.addEventListener("input", () => {
      const val = parseInt(tiempoInput.value || 0);
      previewTiempo.textContent =
        val > 0
          ? `Los alumnos tendrán un máximo de ${val} minutos para resolver el examen.`
          : "Sin límite de tiempo (podrán permanecer indefinidamente).";
    });

    // 🔹 Ejecutar al cargar (para corregir el bug visual)
    const inicial = parseInt(tiempoInput.value || 0);
    previewTiempo.textContent =
      inicial > 0
        ? `Los alumnos tendrán un máximo de ${inicial} minutos para resolver el examen.`
        : "Sin límite de tiempo (podrán permanecer indefinidamente).";
  }

  actualizarTotalPuntos();

  if (!form || !contPreg) return;

  // 🔁 Escucha cualquier cambio en puntos o check de extra dentro del contenedor
  contPreg.addEventListener("input", (e) => {
    if (e.target.classList.contains("preg-puntos")) {
      actualizarTotalPuntos();
    }
  });

  contPreg.addEventListener("change", (e) => {
    if (e.target.classList.contains("preg-extra")) {
      actualizarTotalPuntos();
    }
  });

  // 🧩 Vincular eventos a preguntas existentes (modo edición)
  contPreg.querySelectorAll(".pregunta-card").forEach((wrap) => {
    const id = wrap.dataset.id || null;

    // Botón eliminar
    wrap
      .querySelector(".eliminar-pregunta")
      ?.addEventListener("click", async () => {
        const confirmar = confirm("¿Seguro que deseas eliminar esta pregunta?");
        if (!confirmar) return;

        if (id) {
          try {
            const res = await fetch(
              `${window.base_url}profesor/grupos/eliminar-pregunta/${id}`,
              {
                method: "DELETE",
              }
            );
            const data = await res.json();
            if (data.success) {
              mostrarAlerta("✅ Pregunta eliminada correctamente", "success");
              wrap.remove();
              actualizarNumeracionYPuntaje();
            } else {
              mostrarAlerta(
                data.error || "Error al eliminar pregunta",
                "error"
              );
            }
          } catch (err) {
            console.error(err);
            mostrarAlerta("❌ Error de conexión o servidor", "error");
          }
        } else {
          wrap.remove();
          actualizarNumeracionYPuntaje();
        }
      });

    // Eventos para recalcular puntaje
    wrap
      .querySelector(".preg-puntos")
      ?.addEventListener("input", actualizarTotalPuntos);
    wrap
      .querySelector(".preg-extra")
      ?.addEventListener("change", actualizarTotalPuntos);
  });

  // ==============================
  // ➕ Agregar nueva pregunta
  // ==============================
  btnAgregar?.addEventListener("click", () => agregarPreguntaUI());

  function agregarPreguntaUI(p = null) {
    const i = contPreg.children.length;
    const wrap = document.createElement("div");
    wrap.className = "tarea-card pregunta-card";
    if (p && p.id) wrap.dataset.id = p.id; // 👈 Guarda el ID existente

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

    <div class="puntos-wrap" style="display:flex;gap:10px;align-items:center;margin-top:6px;">
      <label style="flex:1;">Valor (puntos)</label>
      <input type="number" class="preg-puntos" min="0" step="0.5" style="width:100px;">
      <label class="chk-line" style="display:flex;align-items:center;gap:6px;">
        <input type="checkbox" class="preg-extra"> <span>Puntos extra</span>
      </label>
    </div>

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

    // 🔹 Si hay datos previos (modo edición), rellenar campos
    if (p) {
      wrap.querySelector(".preg-tipo").value = p.tipo || "opcion";
      wrap.querySelector(".preg-texto").value = p.pregunta || "";
      wrap.querySelector(".preg-puntos").value = p.puntos || 1;
      wrap.querySelector(".preg-extra").checked = !!p.es_extra;

      const listaOpc = wrap.querySelector(".lista-opciones");
      if (p.opciones && p.opciones.length > 0) {
        p.opciones.forEach((op) => {
          const div = document.createElement("div");
          div.className = "opcion-card";
          div.innerHTML = `
          <label class="chk-line">
            <input type="checkbox" class="op-correcta" ${
              op.es_correcta ? "checked" : ""
            }>
            <span>Correcta</span>
          </label>
          <input type="text" class="op-texto" value="${op.texto}">
          <button type="button" class="btn-sec eliminar-opcion">Eliminar</button>
        `;
          div
            .querySelector(".eliminar-opcion")
            .addEventListener("click", () => div.remove());
          listaOpc.appendChild(div);
        });
      }

      // Ocultar opciones si es tipo abierta
      wrap.querySelector(".opciones-wrap").style.display =
        p.tipo === "abierta" ? "none" : "";
    }

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
    wrap
      .querySelector(".eliminar-pregunta")
      .addEventListener("click", async () => {
        const id = wrap.dataset.id || null;
        const confirmar = confirm("¿Seguro que deseas eliminar esta pregunta?");
        if (!confirmar) return;

        if (id) {
          try {
            const res = await fetch(
              `${window.base_url}profesor/grupos/eliminar-pregunta/${id}`,
              {
                method: "POST",
              }
            );
            const data = await res.json();
            if (data.success) {
              mostrarAlerta("✅ Pregunta eliminada correctamente", "success");
            } else {
              mostrarAlerta(
                data.error || "Error al eliminar pregunta",
                "error"
              );
            }
          } catch (err) {
            console.error(err);
            mostrarAlerta("❌ Error de conexión o servidor", "error");
          }
        }

        wrap.remove();
        [...contPreg.children].forEach((c, idx) => {
          c.querySelector("strong").textContent = `Pregunta ${idx + 1}`;
        });

        wrap
          .querySelector(".preg-puntos")
          .addEventListener("input", actualizarTotalPuntos);
        wrap
          .querySelector(".preg-extra")
          .addEventListener("change", actualizarTotalPuntos);
        actualizarTotalPuntos(); // inicial
      });
  }

  function actualizarTotalPuntos() {
    let total = 0;
    [...contPreg.children].forEach((wrap) => {
      const puntos = parseFloat(wrap.querySelector(".preg-puntos").value || 0);
      const extra = wrap.querySelector(".preg-extra").checked;
      if (!extra) total += puntos;
    });

    const totalElem = document.getElementById("totalPuntos");
    if (totalElem) {
      totalElem.textContent = `Puntos totales: ${total.toFixed(1)} / 100`;

      if (total < 100) {
        totalElem.style.color = "#d35400"; // naranja: incompleto
      } else if (total > 100) {
        totalElem.style.color = "#e74c3c"; // rojo: exceso
      } else {
        totalElem.style.color = "#27ae60"; // verde: perfecto
      }
    }

    return total;
  }

  function actualizarNumeracionYPuntaje() {
    [...contPreg.children].forEach((c, idx) => {
      c.querySelector("strong").textContent = `Pregunta ${idx + 1}`;
    });
    actualizarTotalPuntos();
  }

  // ==============================
  // 💾 Guardar examen (AJAX)
  // ==============================
  btnGuardar?.addEventListener("click", async (e) => {
    e.preventDefault();

    const total = actualizarTotalPuntos();
    if (total !== 100) {
      mostrarAlerta(
        total < 100
          ? `⚠️ El total es ${total.toFixed(
              1
            )}. Debe sumar exactamente 100 puntos.`
          : `⚠️ El total excede los 100 puntos (${total.toFixed(1)}).`,
        "error"
      );
      return;
    }
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
        puntos: parseFloat(wrap.querySelector(".preg-puntos").value || 0),
        es_extra: wrap.querySelector(".preg-extra").checked ? 1 : 0,
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
    btnGuardar.innerHTML = `<i class="fas fa-spinner fa-spin"></i>`;

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
      btnGuardar.innerHTML = `<i class="fas fa-save"></i>`;
    }
  });
});
