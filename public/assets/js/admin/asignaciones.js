// ==========================================================
// 🌐 VARIABLES GLOBALES
// ==========================================================
const baseUrl = document.body.dataset.base || window.location.origin + "/";

const grupoSelect = document.getElementById("grupo_id");
const materiaSelect = document.getElementById("materia_id");
const profesorSelect = document.getElementById("profesor_id");
const horaInicioSelect = document.getElementById("hora_inicio");
const horaFinSelect = document.getElementById("hora_fin");
const formAsignacion = document.getElementById("formAsignacion");
const gridCanvas = document.getElementById("gridCanvas");
const horarioContainer = document.getElementById("horarioGrid");

let modoEdicion = false;
let asignacionEditando = null;

// ==========================================================
// 🕒 HORARIOS DISPONIBLES
// ==========================================================
const HORARIOS = {
  Matutino: [
    "07:30",
    "08:20",
    "09:10",
    "10:00",
    "10:50",
    "11:40",
    "12:30",
    "13:20",
    "14:10",
    "15:00",
  ],
  Vespertino: [
    "16:40",
    "17:20",
    "18:00",
    "18:40",
    "19:20",
    "20:00",
    "20:40",
    "21:20",
    "22:00",
  ],
};

// ==========================================================
// 🔄 CAMBIO DE GRUPO: cargar materias + horario + horas
// ==========================================================
grupoSelect?.addEventListener("change", async () => {
  const grupoId = grupoSelect.value;
  if (!grupoId) return;

  const turno = grupoSelect.selectedOptions[0].dataset.turno || "Matutino";
  renderGrid(turno);
  horarioContainer.classList.remove("hidden");

  llenarHoras(turno);
  await cargarHorarioActual();

  // 📚 Cargar materias según carrera/plan
  const res = await fetch(
    `${baseUrl}admin/asignaciones/materias-por-grupo/${grupoId}`
  );
  const data = await res.json();

  materiaSelect.innerHTML = '<option value="">-- Selecciona --</option>';
  if (data.ok) {
    data.materias.forEach((m) => {
      materiaSelect.innerHTML += `<option value="${m.id}">${m.nombre}</option>`;
    });
  } else {
    mostrarAlerta(data.msg, "warning");
  }

  actualizarLineaHora(turno);
  setInterval(() => actualizarLineaHora(turno), 60000);
});

// ==========================================================
// 🎯 CAMBIO DE MATERIA: si ya tiene profesor asignado, seleccionarlo automáticamente
// ==========================================================
materiaSelect?.addEventListener("change", async () => {
  const grupoId = grupoSelect.value;
  const materiaId = parseInt(materiaSelect.value);
  if (!grupoId || !materiaId) return;

  try {
    const res = await fetch(
      `${baseUrl}admin/asignaciones/horario-grupo/${grupoId}`
    );
    const data = await res.json();
    if (!data.ok || !data.asignaciones) return;

    const asig = data.asignaciones.find(
      (a) => parseInt(a.materia_id) === materiaId && a.profesor_id
    );

    if (asig) {
      profesorSelect.value = asig.profesor_id;
      mostrarAlerta(`👨‍🏫 Profesor asignado: ${asig.profesor}`, "info");
    } else {
      profesorSelect.value = "";
    }
  } catch (err) {
    console.error("Error al verificar asignación existente:", err);
  }
});

// ==========================================================
// 🕒 Llenar selects de hora inicio / fin
// ==========================================================
function llenarHoras(turno) {
  const horas = HORARIOS[turno] || HORARIOS["Matutino"];
  horaInicioSelect.innerHTML = '<option value="">-- Selecciona --</option>';
  horaFinSelect.innerHTML = '<option value="">-- Selecciona --</option>';

  horas.forEach((h) => {
    const val = h.trim();
    horaInicioSelect.innerHTML += `<option value="${val}">${val}</option>`;
    horaFinSelect.innerHTML += `<option value="${val}">${val}</option>`;
  });
}

// ==========================================================
// ⏩ FILTRAR HORAS FIN según hora inicio seleccionada
// ==========================================================
horaInicioSelect?.addEventListener("change", () => {
  const turno = grupoSelect.selectedOptions[0]?.dataset.turno || "Matutino";
  const horas = HORARIOS[turno] || HORARIOS["Matutino"];
  const horaInicio = horaInicioSelect.value;

  horaFinSelect.innerHTML = '<option value="">-- Selecciona --</option>';
  if (!horaInicio) return; // Si no se eligió nada, mostrar todas

  // Obtener índice de la hora de inicio
  const indexInicio = horas.indexOf(horaInicio);

  // Llenar solo las horas posteriores
  horas.forEach((h, i) => {
    if (i > indexInicio) {
      horaFinSelect.innerHTML += `<option value="${h}">${h}</option>`;
    }
  });

  // Si no hay opciones disponibles (inicio fue la última hora)
  if (horaFinSelect.options.length === 1) {
    mostrarAlerta(
      "⚠️ No hay horarios disponibles después de esa hora.",
      "warning"
    );
  }
});

// ==========================================================
// 🧱 Renderizar rejilla base del horario
// ==========================================================
function renderGrid(turno = "Matutino") {
  gridCanvas.innerHTML = "";

  const dias = ["Hora", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes"];
  const bloquesHora = HORARIOS[turno];
  const altoPorBloque = 60;
  const anchoCol = (gridCanvas.offsetWidth - 70) / 5;

  // === CABECERA DE DÍAS ===
  const header = document.createElement("div");
  header.className = "grid-header";
  dias.forEach((d, i) => {
    const div = document.createElement("div");
    div.textContent = d;
    Object.assign(div.style, {
      position: "absolute",
      top: "0",
      left: i === 0 ? "0" : `${70 + (i - 1) * anchoCol}px`,
      width: i === 0 ? "70px" : `${anchoCol}px`,
      height: "25px",
      background: i === 0 ? "transparent" : "var(--primary)",
      color: "white",
      textAlign: "center",
      lineHeight: "25px",
      fontWeight: "600",
      borderBottom: "1px solid var(--border)",
    });
    header.appendChild(div);
  });
  gridCanvas.appendChild(header);

  // === COLUMNA DE HORARIOS ===
  const colHoras = document.createElement("div");
  colHoras.className = "col-horas";
  Object.assign(colHoras.style, {
    position: "absolute",
    top: "25px",
    left: "0",
    width: "70px",
  });

  for (let i = 0; i < bloquesHora.length - 1; i++) {
    const bloque = document.createElement("div");
    bloque.textContent = `${bloquesHora[i]} - ${bloquesHora[i + 1]}`;
    Object.assign(bloque.style, {
      position: "absolute",
      top: `${i * altoPorBloque}px`,
      height: `${altoPorBloque}px`,
      fontSize: "0.75rem",
      color: "#ccc",
      textAlign: "right",
      paddingRight: "5px",
      display: "flex",
      alignItems: "center",
      justifyContent: "flex-end",
      borderBottom: "1px solid rgba(255,255,255,0.05)",
    });
    colHoras.appendChild(bloque);
  }
  gridCanvas.appendChild(colHoras);

  // === FONDO DE CUADRÍCULA ===
  const fondo = document.createElement("div");
  fondo.className = "grid-fondo";
  Object.assign(fondo.style, {
    position: "absolute",
    top: "25px",
    left: "70px",
    right: "0",
    bottom: "0",
    background:
      "linear-gradient(var(--border) 1px, transparent 1px) 0 0 / 100% 60px repeat-y," +
      "linear-gradient(90deg, var(--border) 1px, transparent 1px) 0 0 / calc(20%) 100% repeat-x",
    zIndex: 0,
  });
  gridCanvas.appendChild(fondo);
}

// ==========================================================
// 🗓️ Cargar asignaciones del grupo
// ==========================================================
async function cargarHorarioActual() {
  const grupoId = grupoSelect.value;
  if (!grupoId) return;

  gridCanvas.innerHTML = ""; // limpiar
  const res = await fetch(
    `${baseUrl}admin/asignaciones/horario-grupo/${grupoId}`
  );
  const data = await res.json();
  if (!data.ok) return;

  const turno = grupoSelect.selectedOptions[0].dataset.turno || "Matutino";
  const anchoCol = (gridCanvas.offsetWidth - 70) / 5;
  const bloquesHora = HORARIOS[turno];
  const altoPorBloque = 60;

  data.asignaciones.forEach((a) => {
    const [inicio, fin] = a.rango.map(String);
    const inicioMin = horaToMinutos(inicio);
    const finMin = horaToMinutos(fin);
    const primeraHora = horaToMinutos(bloquesHora[0].replace(":", ""));
    const minutosDesdeInicio = inicioMin - primeraHora;
    const duracion = finMin - inicioMin;
    const minutosTotalesHorario =
      horaToMinutos(bloquesHora[bloquesHora.length - 1].replace(":", "")) -
      primeraHora;
    const alturaTotalCanvas = (bloquesHora.length - 1) * altoPorBloque;
    const pxPorMinuto = alturaTotalCanvas / minutosTotalesHorario;

    const top = minutosDesdeInicio * pxPorMinuto;
    const alto = duracion * pxPorMinuto;

    a.dias.forEach((d) => {
      const col = ["L", "M", "X", "J", "V"].indexOf(d);
      if (col >= 0) {
        const bloque = document.createElement("div");
        bloque.className = "bloque";
        bloque.dataset.asignacionId = a.id;
        bloque.innerHTML = `
                    <div style="font-weight:600;">${a.materia}</div>
                    <div style="font-size:0.75rem;opacity:0.85;">${formatearHora(
                      inicio
                    )} - ${formatearHora(fin)}</div>
                    <div style="font-size:0.75rem;opacity:0.75;">${
                      a.profesor ?? "Sin profesor"
                    }</div>
                `;
        bloque.style.position = "absolute";
        bloque.style.left = `${70 + col * anchoCol}px`;
        bloque.style.top = `${top}px`;
        bloque.style.width = `${anchoCol - 8}px`;
        bloque.style.height = `${alto}px`;

        bloque.style.width = anchoCol - 8 + "px";
        bloque.style.height = alto + "px";
        gridCanvas.appendChild(bloque);
      }
    });
  });

  initContextMenu();
  initInteract(anchoCol, altoPorBloque, turno);
}

// ==========================================================
// 🧲 Interacción precisa de bloques: arrastre, redimensionamiento y previsualización
// ==========================================================
function initInteract(anchoCol, altoPorBloque, turno) {
  const bloques = document.querySelectorAll(".bloque");
  let modoPreview = false;
  let cambios = [];

  // ========================================
  // 🟢 Barra de previsualización
  // ========================================
  let barraPreview = document.getElementById("barraPreview");
  if (!barraPreview) {
    barraPreview = document.createElement("div");
    barraPreview.id = "barraPreview";
    Object.assign(barraPreview.style, {
      position: "fixed",
      bottom: "10px",
      left: "50%",
      transform: "translateX(-50%)",
      background: "#222",
      color: "#fff",
      padding: "10px 20px",
      borderRadius: "8px",
      boxShadow: "0 4px 12px rgba(0,0,0,.4)",
      display: "none",
      gap: "10px",
      zIndex: "9999",
    });
    barraPreview.innerHTML = `
      <span>👁️ Estás en modo previsualización</span>
      <button id="btnGuardarPreview" class="btn-mini">💾 Guardar</button>
      <button id="btnCancelarPreview" class="btn-mini">❌ Cancelar</button>`;
    document.body.appendChild(barraPreview);

    document.getElementById("btnCancelarPreview").onclick = () => {
      mostrarAlerta("❌ Cambios descartados", "warning");
      barraPreview.style.display = "none";
      cargarHorarioActual();
    };
    document.getElementById("btnGuardarPreview").onclick = async () => {
      if (!cambios.length)
        return mostrarAlerta("No hay cambios por guardar.", "info");

      for (const c of cambios) {
        await fetch(`${baseUrl}admin/asignaciones/actualizar/${c.id}`, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({
            hora_inicio: c.inicio,
            hora_fin: c.fin,
            dias: [c.dia],
            materia_id: c.materia_id,
            profesor_id: c.profesor_id,
            aula: c.aula,
          }),
        });
      }
      mostrarAlerta("✅ Cambios guardados correctamente", "success");
      barraPreview.style.display = "none";
      cargarHorarioActual();
    };
  }

  // ========================================
  // 🟦 Guía visual azul (línea de inserción)
  // ========================================
  let guia = document.getElementById("guiaInsercion");
  if (!guia) {
    guia = document.createElement("div");
    guia.id = "guiaInsercion";
    Object.assign(guia.style, {
      position: "absolute",
      left: "70px",
      right: "0",
      height: "2px",
      background: "#00b4ff",
      opacity: "0",
      zIndex: "999",
      transition: "opacity 0.1s ease, top 0.1s ease",
    });
    gridCanvas.appendChild(guia);
  }

  // ========================================
  // 🎯 InteractJS Drag & Resize (fluido y preciso)
  // ========================================
  interact(".bloque")
    .draggable({
      inertia: false,
      modifiers: [
        interact.modifiers.snap({
          targets: [interact.snappers.grid({ x: anchoCol, y: altoPorBloque })],
          range: Infinity,
          relativePoints: [{ x: 0, y: 0 }],
        }),
        interact.modifiers.restrictRect({
          restriction: gridCanvas,
          endOnly: true,
        }),
      ],
      listeners: {
        start(event) {
          if (!modoPreview) {
            modoPreview = true;
            barraPreview.style.display = "flex";
            mostrarAlerta("👁️ Modo previsualización activado", "info");
          }

          const target = event.target;
          target.classList.add("arrastrando");

          // Crear clon fantasma (efecto tipo Google Calendar)
          const ghost = target.cloneNode(true);
          ghost.style.opacity = "0.35";
          ghost.style.pointerEvents = "none";
          ghost.style.border = "2px dashed #00ff90";
          ghost.id = "ghost";
          gridCanvas.appendChild(ghost);
          target._ghost = ghost;
        },
        move(event) {
          const target = event.target;
          const ghost = target._ghost;

          // Movimiento acumulado
          const x = (parseFloat(target.getAttribute("data-x")) || 0) + event.dx;
          const y = (parseFloat(target.getAttribute("data-y")) || 0) + event.dy;

          target.style.transform = `translate(${x}px, ${y}px)`;
          target.setAttribute("data-x", x);
          target.setAttribute("data-y", y);

          // Mover clon fantasma
          if (ghost) {
            ghost.style.left = `${parseFloat(target.style.left) + x}px`;
            ghost.style.top = `${parseFloat(target.style.top) + y}px`;
            ghost.style.width = target.style.width;
            ghost.style.height = target.style.height;
          }

          // === Mostrar línea azul de inserción ===
          const gridTop = Math.max(
            0,
            Math.round((parseFloat(target.style.top) + y) / altoPorBloque)
          );
          guia.style.top = `${gridTop * altoPorBloque + 25}px`; // +25px por cabecera
          guia.style.opacity = "0.8";
        },
        end(event) {
          const target = event.target;
          const x = parseFloat(target.getAttribute("data-x")) || 0;
          const y = parseFloat(target.getAttribute("data-y")) || 0;

          // Aplicar posición final al grid
          const newLeft = parseFloat(target.style.left) + x;
          const newTop = parseFloat(target.style.top) + y;
          const colIndex = Math.max(0, Math.round((newLeft - 70) / anchoCol));
          const rowIndex = Math.max(0, Math.round(newTop / altoPorBloque));

          target.style.left = `${70 + colIndex * anchoCol}px`;
          target.style.top = `${rowIndex * altoPorBloque}px`;
          target.style.transform = "none";
          target.setAttribute("data-x", 0);
          target.setAttribute("data-y", 0);

          if (target._ghost) {
            target._ghost.remove();
            target._ghost = null;
          }

          guia.style.opacity = "0"; // ocultar línea azul

          // Registrar cambio
          const nuevaHoraInicio =
            HORARIOS[turno][rowIndex] || HORARIOS[turno][0];
          const duracionBloques = Math.round(
            parseFloat(target.style.height) / altoPorBloque
          );
          const nuevaHoraFin =
            HORARIOS[turno][rowIndex + duracionBloques] ||
            HORARIOS[turno][rowIndex + 1];

          cambios.push({
            id: target.dataset.asignacionId,
            dia: ["L", "M", "X", "J", "V"][colIndex],
            inicio: nuevaHoraInicio,
            fin: nuevaHoraFin,
            materia_id: target.dataset.materiaId,
            profesor_id: target.dataset.profesorId,
            aula: target.dataset.aula || "",
          });

          mostrarAlerta("💾 Movimiento registrado en previsualización", "info");
          target.classList.remove("arrastrando");
        },
      },
    })
    .resizable({
      edges: { top: true, bottom: true },
      modifiers: [
        interact.modifiers.snapSize({
          targets: [interact.snappers.grid({ y: altoPorBloque })],
          range: Infinity,
        }),
        interact.modifiers.restrictEdges({ outer: gridCanvas }),
      ],
      listeners: {
        move(event) {
          const target = event.target;
          let newTop = parseFloat(target.style.top) + event.deltaRect.top;
          let newHeight =
            parseFloat(target.style.height) + event.deltaRect.height;

          // Ajustar al grid vertical
          newTop = Math.round(newTop / altoPorBloque) * altoPorBloque;
          newHeight = Math.round(newHeight / altoPorBloque) * altoPorBloque;

          const maxBottom = (HORARIOS[turno].length - 1) * altoPorBloque;
          if (newTop < 0) {
            newTop = 0;
            mostrarAlerta("🚫 Límite superior alcanzado", "warning");
          }
          if (newTop + newHeight > maxBottom) {
            newHeight = maxBottom - newTop;
            mostrarAlerta("🚫 Límite inferior alcanzado", "warning");
          }

          target.style.top = `${newTop}px`;
          target.style.height = `${newHeight}px`;
        },
      },
    });
}

// ==========================================================
// ⚙️ Menú contextual avanzado de bloques (Editar / Eliminar / Elegir bloque)
// ==========================================================
function initContextMenu() {
  gridCanvas.addEventListener("contextmenu", async (e) => {
    e.preventDefault();
    const bloque = e.target.closest(".bloque");
    if (!bloque) return;

    // Crear menú base
    const menu = document.createElement("div");
    menu.className = "menu-contextual";
    menu.innerHTML = `
      <button class="btn-mini editar"><i class="fa fa-pen"></i> Editar</button>
      <button class="btn-mini eliminar"><i class="fa fa-trash"></i> Eliminar</button>`;
    document.body.appendChild(menu);
    menu.style.left = e.pageX + "px";
    menu.style.top = e.pageY + "px";

    const closeMenu = () => menu.remove();
    document.addEventListener("click", closeMenu, { once: true });

    // 🔹 EDITAR
    menu.querySelector(".editar").onclick = async () => {
      const id = bloque.dataset.asignacionId;
      const res = await fetch(`${baseUrl}admin/asignaciones/detalle/${id}`);
      const data = await res.json();
      if (!data.ok)
        return mostrarAlerta("Error al cargar la asignación.", "error");

      const a = data.asignacion;
      const bloques = a.bloques || [];
      if (bloques.length > 1) {
        // Mostrar submenú para elegir cuál horario editar
        const subMenu = document.createElement("div");
        subMenu.className = "menu-sub-bloques";
        subMenu.style.position = "absolute";
        subMenu.style.left = e.pageX + 160 + "px";
        subMenu.style.top = e.pageY + "px";
        subMenu.style.background = "#2a2a2a";
        subMenu.style.border = "1px solid #444";
        subMenu.style.padding = "6px";
        subMenu.style.borderRadius = "6px";
        subMenu.style.boxShadow = "0 3px 8px rgba(0,0,0,.4)";
        subMenu.innerHTML =
          "<strong style='color:#0f0;'>Selecciona horario:</strong><br>";

        bloques.forEach((b, i) => {
          const btn = document.createElement("button");
          btn.className = "btn-mini";
          btn.style.display = "block";
          btn.style.width = "100%";
          btn.style.textAlign = "left";
          btn.textContent = `${b.dias.join("")} ${b.hora_inicio}-${b.hora_fin}`;
          btn.onclick = () => {
            aplicarEdicionBloque(a, b);
            subMenu.remove();
            closeMenu();
          };
          subMenu.appendChild(btn);
        });
        document.body.appendChild(subMenu);
      } else {
        aplicarEdicionBloque(a, bloques[0] || a);
      }
    };

    // 🔹 ELIMINAR
    menu.querySelector(".eliminar").onclick = async () => {
      const id = bloque.dataset.asignacionId;
      const res = await fetch(
        `${baseUrl}admin/asignaciones/eliminar-profesor/${id}`
      );
      const data = await res.json();
      mostrarAlerta(data.msg, data.ok ? "success" : "error");
      if (data.ok) bloque.remove();
    };
  });
}

// ==========================================================
// 🧩 Función auxiliar: aplicar datos del bloque elegido al formulario
// ==========================================================
function aplicarEdicionBloque(asignacion, bloque) {
  const turno = grupoSelect.selectedOptions[0]?.dataset.turno || "Matutino";
  llenarHoras(turno);

  grupoSelect.value = asignacion.grupo_id;
  materiaSelect.value = asignacion.materia_id;
  profesorSelect.value = asignacion.profesor_id;
  document.getElementById("ciclo").value = asignacion.ciclo;
  document.getElementById("aula").value = asignacion.aula;

  const inicioVal = (bloque.hora_inicio || "").trim().substring(0, 5);
  const finVal = (bloque.hora_fin || "").trim().substring(0, 5);

  // ✅ Asignar hora inicio
  horaInicioSelect.value = inicioVal;

  // ✅ Ahora aplicar el filtro dinámico para mostrar solo horas válidas de fin
  const horas = HORARIOS[turno] || HORARIOS["Matutino"];
  const indexInicio = horas.indexOf(inicioVal);
  horaFinSelect.innerHTML = '<option value="">-- Selecciona --</option>';

  horas.forEach((h, i) => {
    if (i > indexInicio) {
      horaFinSelect.innerHTML += `<option value="${h}">${h}</option>`;
    }
  });

  // ✅ Seleccionar automáticamente la hora fin que corresponda
  horaFinSelect.value = finVal;

  // ✅ Marcar los días
  document.querySelectorAll("input[name='dias[]']").forEach((chk) => {
    chk.checked = bloque.dias.includes(chk.value);
  });

  mostrarAlerta(
    `✏️ Editando bloque: ${bloque.dias.join("")} ${bloque.hora_inicio}-${
      bloque.hora_fin
    }`,
    "info"
  );

  modoEdicion = true;
  asignacionEditando = asignacion.id;
  document.querySelector(".btn-nuevo").innerHTML =
    '<i class="fa fa-pen"></i> Actualizar';
  document.getElementById("btnCancelar").classList.remove("hidden");
}

// ==========================================================
// ❌ Cancelar modo edición
// ==========================================================
document.getElementById("btnCancelar")?.addEventListener("click", () => {
  modoEdicion = false;
  asignacionEditando = null;
  formAsignacion.reset();
  document.querySelector(".btn-nuevo").innerHTML =
    '<i class="fa fa-save"></i> Guardar';
  document.getElementById("btnCancelar").classList.add("hidden");
});

// ==========================================================
// 💾 Guardar / Actualizar asignación
// ==========================================================
formAsignacion?.addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(formAsignacion);
  const url = modoEdicion
    ? `${baseUrl}admin/asignaciones/actualizar/${asignacionEditando}`
    : `${baseUrl}admin/asignaciones/asignar-profesor`;

  const res = await fetch(url, { method: "POST", body: formData });
  const data = await res.json();

  if (data.ok) {
    mostrarAlerta(data.msg, "success");
    gridCanvas.innerHTML = "";
    if (grupoSelect.value) await cargarHorarioActual();

    const grupoActual = grupoSelect.value;
    const cicloActual = document.getElementById("ciclo").value;
    const aulaActual = document.getElementById("aula").value;

    formAsignacion.reset();
    grupoSelect.value = grupoActual;
    document.getElementById("ciclo").value = cicloActual;
    document.getElementById("aula").value = aulaActual;

    const turno = grupoSelect.selectedOptions[0]?.dataset.turno || "Matutino";
    llenarHoras(turno);

    modoEdicion = false;
    asignacionEditando = null;
    document.querySelector(".btn-nuevo").innerHTML =
      '<i class="fa fa-save"></i> Guardar';
    document.getElementById("btnCancelar").classList.add("hidden");
  } else {
    mostrarAlerta(data.msg || "Error al guardar la asignación.", "error");
  }
});

// ==========================================================
// ⏱️ Línea roja de hora actual
// ==========================================================
function actualizarLineaHora(turno) {
  let linea = document.getElementById("lineaHora");
  let etiqueta = document.getElementById("etiquetaHora");

  if (!linea) {
    linea = document.createElement("div");
    linea.id = "lineaHora";
    Object.assign(linea.style, {
      position: "absolute",
      left: "70px",
      right: "0",
      height: "2px",
      background: "red",
      zIndex: "5",
      opacity: "0.8",
    });
    gridCanvas.appendChild(linea);
  }

  if (!etiqueta) {
    etiqueta = document.createElement("div");
    etiqueta.id = "etiquetaHora";
    Object.assign(etiqueta.style, {
      position: "absolute",
      left: "0",
      width: "70px",
      color: "red",
      fontSize: "0.8rem",
      textAlign: "right",
      paddingRight: "8px",
      fontWeight: "bold",
    });
    gridCanvas.appendChild(etiqueta);
  }

  const ahora = new Date();
  const horaActual = ahora.getHours() * 60 + ahora.getMinutes();
  const bloques = HORARIOS[turno];
  const primeraHora = horaToMinutos(bloques[0].replace(":", ""));
  const ultimaHora = horaToMinutos(
    bloques[bloques.length - 1].replace(":", "")
  );

  if (horaActual < primeraHora || horaActual > ultimaHora + 60) {
    linea.style.display = "none";
    etiqueta.style.display = "none";
    return;
  }

  const minutosDesdeInicio = horaActual - primeraHora;
  const altoPorBloque = 60;
  const minutosTotalesHorario = ultimaHora - primeraHora;
  const alturaTotalCanvas = (bloques.length - 1) * altoPorBloque;
  const pxPorMinuto = alturaTotalCanvas / minutosTotalesHorario;

  const top = minutosDesdeInicio * pxPorMinuto;
  const horaFormateada = ahora.toLocaleTimeString([], {
    hour: "2-digit",
    minute: "2-digit",
  });

  linea.style.display = "block";
  linea.style.top = top + "px";
  etiqueta.style.display = "block";
  etiqueta.style.top = top - 10 + "px";
  etiqueta.textContent = horaFormateada;
}

// ==========================================================
// 🔧 Utilidades
// ==========================================================
function formatearHora(valor) {
  valor = valor.toString().padStart(4, "0");
  const horas = valor.slice(0, 2);
  const minutos = valor.slice(2);
  return `${horas}:${minutos}`;
}

function horaToMinutos(hora) {
  hora = hora.toString().padStart(4, "0");
  const h = parseInt(hora.slice(0, 2));
  const m = parseInt(hora.slice(2));
  return h * 60 + m;
}
