// ==========================================================
// 🌐 VARIABLES GLOBALES
// ==========================================================
const baseUrl = document.body.dataset.base || window.location.origin + "/";

const grupoSelect = document.getElementById("grupo_id");
const materiaSelect = document.getElementById("materia_id");
const profesorSelect = document.getElementById("profesor_id");
const formAsignacion = document.getElementById("formAsignacion");

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
  ],
};

function horaToMinutos(hora) {
  const [hh, mm] = hora.split(":").map(Number);
  return hh * 60 + mm;
}

// ==========================================================
// 🧠 CAMBIO DE GRUPO → generar cuadrícula y cargar materias
// ==========================================================
grupoSelect?.addEventListener("change", async () => {
  const grupoId = grupoSelect.value;
  if (!grupoId) return;

  const turno = grupoSelect.selectedOptions[0]?.dataset.turno || "Matutino";
  generarCuadricula(turno);

  // 🟢 Cargar horario actual del grupo y marcar casillas ocupadas
  await new Promise((r) => setTimeout(r, 50));
  await cargarHorarioGrupo(grupoId);

  // 🟢 Cargar materias del grupo
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
    mostrarAlerta(data.msg || "No se pudieron cargar las materias.", "warning");
  }
});

// ==========================================================
// 🎯 CAMBIO DE MATERIA → mostrar profesor y frecuencias
// ==========================================================
materiaSelect?.addEventListener("change", async () => {
  const grupoId = grupoSelect.value;
  const materiaId = parseInt(materiaSelect.value);
  const infoBox = document.getElementById("frecuenciasInfo");
  const barra = infoBox.querySelector(".frecuencia-barra-progreso");
  const texto = infoBox.querySelector(".frecuencia-texto");

  if (!grupoId || !materiaId) {
    infoBox.classList.add("hidden");
    return;
  }

  try {
    const res = await fetch(
      `${baseUrl}admin/asignaciones/horario-grupo/${grupoId}`
    );
    const data = await res.json();
    if (data.ok && data.asignaciones) {
      const asig = data.asignaciones.find(
        (a) => parseInt(a.materia_id) === materiaId && a.profesor_id
      );
      profesorSelect.value = asig ? asig.profesor_id : "";
      if (asig) mostrarAlerta(`👨‍🏫 Profesor asignado: ${asig.profesor}`, "info");
    }

    // --- Consultar frecuencias restantes ---
    const resFreq = await fetch(
      `${baseUrl}admin/asignaciones/frecuencias-restantes/${grupoId}/${materiaId}`
    );
    const dataFreq = await resFreq.json();

    if (dataFreq.ok) {
      infoBox.classList.remove("hidden");
      const total = dataFreq.totales,
        usadas = dataFreq.usadas,
        restantes = dataFreq.restantes;
      window.frecuenciasBase = { total, usadas, restantes };

      const porcentaje = Math.min((usadas / total) * 100, 100);
      barra.style.width = porcentaje + "%";
      barra.style.background = restantes === 0 ? "#e63946" : "var(--primary)";
      texto.innerHTML = `📊 <b>${usadas}</b> de <b>${total}</b> frecuencias asignadas. Restan <b>${restantes}</b>.`;

      if (restantes <= 0)
        mostrarAlerta(
          "⚠️ Ya se alcanzó el número máximo de frecuencias para esta materia.",
          "warning"
        );
    } else infoBox.classList.add("hidden");
  } catch (err) {
    console.error("Error al verificar asignación o frecuencias:", err);
  }
});

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

    const grupoActual = grupoSelect.value;
    const cicloActual = document.getElementById("ciclo").value;
    const aulaActual = document.getElementById("aula").value;

    // 🔄 refrescar cuadrícula y tabla
    await cargarHorarioGrupo(grupoActual);
    actualizarTablaAsignaciones(grupoActual);

    formAsignacion.reset();
    grupoSelect.value = grupoActual;
    document.getElementById("ciclo").value = cicloActual;
    document.getElementById("aula").value = aulaActual;

    // 🧹 Limpiar selección y frecuencias visuales
    document
      .querySelectorAll(".celda.seleccionada")
      .forEach((c) => c.classList.remove("seleccionada"));
    inputHorarios.value = "[]";
    window.frecuenciasBase = { total: 0, usadas: 0, restantes: 0 };
    const infoBox = document.getElementById("frecuenciasInfo");
    if (infoBox) {
      infoBox.classList.add("hidden");
      const barra = infoBox.querySelector(".frecuencia-barra-progreso");
      const texto = infoBox.querySelector(".frecuencia-texto");
      barra.style.width = "0%";
      texto.innerHTML = "";
    }

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
// 🎨 CUADRÍCULA DE HORARIOS
// ==========================================================
const contenedorSelector = document.getElementById("horarioSelector");
const inputHorarios = document.getElementById("horarios_json");
const turnoInfo = document.querySelector(".turno-info");
const dias = ["L", "M", "X", "J", "V"];
let horasBloques = [];
let seleccionando = false;
let modoSeleccion = true;

// ==========================================================
// 🎨 Generar cuadrícula de horarios
// ==========================================================
function generarCuadricula(turno) {
  const HORAS = HORARIOS[turno] || HORARIOS["Matutino"];
  horasBloques = HORAS;

  contenedorSelector.innerHTML = "";

  // Encabezado
  const encabezado = document.createElement("div");
  encabezado.classList.add("fila", "encabezado");
  encabezado.innerHTML =
    `<div class="celda hora">Hora</div>` +
    dias.map((d) => `<div class="celda dia">${d}</div>`).join("");
  contenedorSelector.appendChild(encabezado);

  // Filas
  HORAS.forEach((h) => {
    const fila = document.createElement("div");
    fila.classList.add("fila");
    fila.innerHTML =
      `<div class="celda hora">${h}</div>` +
      dias
        .map(
          (d) => `<div class="celda" data-dia="${d}" data-hora="${h}"></div>`
        )
        .join("");
    contenedorSelector.appendChild(fila);
  });

  contenedorSelector.classList.remove("hidden");
  turnoInfo.textContent = `🕒 Horario: ${turno}`;
}

// ==========================================================
// 🖱️ Eventos de selección (solo una vez, no se destruyen)
// ==========================================================
function agregarEventosSelector() {
  // Evitar duplicar eventos
  if (contenedorSelector._eventosAgregados) return;
  contenedorSelector._eventosAgregados = true;

  contenedorSelector.addEventListener("mousedown", (e) => {
    if (e.button === 2) return;
    const celda = e.target.closest(".celda[data-dia]");
    if (!celda) return;

    if (!materiaSelect.value || !profesorSelect.value) {
      mostrarAlerta(
        "Selecciona una materia y un profesor antes de marcar horarios.",
        "warning"
      );
      return;
    }

    if (celda.classList.contains("ocupada")) {
      mostrarAlerta(
        `⚠️ Choque: ${celda.dataset.materia} (${celda.dataset.profesor})`,
        "error"
      );
      return;
    }

    seleccionando = true;
    modoSeleccion = !celda.classList.contains("seleccionada");
    celda.classList.toggle("seleccionada");
    actualizarInputHorarios();
  });

  contenedorSelector.addEventListener("mouseover", (e) => {
    if (!seleccionando) return;
    const celda = e.target.closest(".celda[data-dia]");
    if (!celda || celda.classList.contains("ocupada")) return;
    if (modoSeleccion) celda.classList.add("seleccionada");
    else celda.classList.remove("seleccionada");
    actualizarInputHorarios();
  });

  document.addEventListener("mouseup", () => (seleccionando = false));
}

// ==========================================================
// 🔹 Convertir selección a JSON
// ==========================================================
function actualizarInputHorarios() {
  const seleccionadas = Array.from(
    document.querySelectorAll(".celda.seleccionada")
  );
  const resultado = {};
  seleccionadas.forEach((c) => {
    const dia = c.dataset.dia;
    const hora = c.dataset.hora;
    if (!resultado[dia]) resultado[dia] = [];
    resultado[dia].push(hora);
  });
  inputHorarios.value = JSON.stringify(resultado);
  actualizarFrecuenciasVisual();
}

// ==========================================================
// 📊 Frecuencias visual
// ==========================================================
function actualizarFrecuenciasVisual() {
  const infoBox = document.getElementById("frecuenciasInfo");
  if (!infoBox || infoBox.classList.contains("hidden")) return;

  const barra = infoBox.querySelector(".frecuencia-barra-progreso");
  const texto = infoBox.querySelector(".frecuencia-texto");

  const total = window.frecuenciasBase?.total || 0;
  const usadasBase = window.frecuenciasBase?.usadas || 0;
  const seleccionadas = document.querySelectorAll(".celda.seleccionada").length;

  const usadas = Math.min(usadasBase + seleccionadas, total);
  const restantes = Math.max(total - usadas, 0);
  const porcentaje = Math.min((usadas / total) * 100, 100);

  barra.style.width = `${porcentaje}%`;
  barra.style.background = restantes === 0 ? "#e63946" : "var(--primary)";
  texto.innerHTML = `📊 <b>${usadas}</b> de <b>${total}</b> frecuencias asignadas. Restan <b>${restantes}</b>.`;

  if (restantes === 0)
    mostrarAlerta("⚠️ Límite máximo de frecuencias alcanzado.", "warning");
}

// ==========================================================
// 📅 Cargar horario del grupo
// ==========================================================
async function cargarHorarioGrupo(grupoId) {
  const turno = grupoSelect.selectedOptions[0]?.dataset.turno || "Matutino";
  const horas = HORARIOS[turno];

  try {
    const res = await fetch(
      `${baseUrl}admin/asignaciones/horario-grupo/${grupoId}`
    );
    const data = await res.json();
    if (!data.ok) return;

    // Limpiar celdas previas
    contenedorSelector.querySelectorAll(".celda.ocupada").forEach((c) => {
      c.classList.remove("ocupada");
      c.innerHTML = "";
      delete c.dataset.materia;
      delete c.dataset.profesor;
      delete c.dataset.asignacionId;
    });

    // Pintar asignaciones
    data.asignaciones.forEach((asig) => {
      const { materia, profesor, dias, inicio_str, fin_str, id } = asig;
      dias.forEach((dia) => {
        const iInicio = horas.findIndex(
          (h) => horaToMinutos(h) === horaToMinutos(inicio_str)
        );

        // Buscar el primer bloque cuya hora sea mayor al fin
        let iFin = horas.findIndex(
          (h) => horaToMinutos(h) > horaToMinutos(fin_str)
        );

        // Si no hay uno mayor (es el último bloque), tomar hasta el final
        if (iFin === -1) iFin = horas.length;

        if (iInicio === -1) return;

        // Pintar desde inicio hasta fin
        const celda = contenedorSelector.querySelector(
          `.celda[data-dia="${dia}"][data-hora="${inicio_str}"]`
        );
        if (celda) {
          celda.classList.add("ocupada");
          celda.dataset.materia = materia;
          celda.dataset.profesor = profesor;
          celda.dataset.asignacionId = id;
          celda.innerHTML = `
    <div class="info-celda">
      <strong>${materia}</strong><br>
      <small><i class="fa-solid fa-user-tie"></i> ${profesor}</small><br>
      <small><i class="fa-regular fa-clock"></i> ${inicio_str} - ${fin_str}</small>
    </div>`;
        }
      });
    });

    inicializarContextMenu(true);
  } catch (err) {
    console.error("Error al cargar el horario del grupo:", err);
  }
}

// ==========================================================
// 📋 Menú contextual (editar / eliminar / eliminar frecuencia)
// ==========================================================
function inicializarContextMenu(reset = false) {
  if (reset && contenedorSelector._contextMenuHandler) {
    contenedorSelector.removeEventListener(
      "contextmenu",
      contenedorSelector._contextMenuHandler
    );
  }

  const handler = (e) => {
    e.preventDefault();
    const celda = e.target.closest(".celda.ocupada");
    if (!celda) return;

    // 🧹 Quitar menús previos
    document.querySelectorAll(".context-menu").forEach((m) => m.remove());

    // 🔹 Crear menú contextual
    const menu = document.createElement("div");
    menu.classList.add("context-menu");
    menu.innerHTML = `
      <ul>
        <li class="editar"><i class="fa-solid fa-pen-to-square"></i> Editar asignación</li>
        <li class="eliminar-frecuencia"><i class="fa-solid fa-clock"></i> Eliminar frecuencia</li>
        <li class="eliminar"><i class="fa-solid fa-trash"></i> Eliminar asignación</li>
      </ul>`;
    document.body.appendChild(menu);

    // Posicionar menú
    menu.style.top = `${e.pageY}px`;
    menu.style.left = `${e.pageX}px`;

    // IDs y datos
    const id = celda.dataset.asignacionId;
    const dia = celda.dataset.dia;
    const hora = celda.dataset.hora;

    // Eventos de opciones
    menu.querySelector(".editar").onclick = () => editarAsignacion(id);
    menu.querySelector(".eliminar").onclick = () => eliminarAsignacion(id);
    menu.querySelector(".eliminar-frecuencia").onclick = () =>
      eliminarFrecuenciaIndividual(id, dia, hora);

    // Cerrar menú al hacer clic afuera
    document.addEventListener("click", () => menu.remove(), { once: true });
  };

  contenedorSelector.addEventListener("contextmenu", handler);
  contenedorSelector._contextMenuHandler = handler;
}

// ==========================================================
// ✏️ Editar asignación global (ajustada al controlador nuevo)
// ==========================================================
function editarAsignacion(id) {
  fetch(`${baseUrl}admin/asignaciones/detalle/${id}`)
    .then((r) => r.json())
    .then(async (data) => {
      if (!data.ok) return mostrarAlerta("Asignación no encontrada.", "error");
      const a = data.asignacion;
      grupoSelect.value = a.grupo_id;
      materiaSelect.value = a.materia_id;
      profesorSelect.value = a.profesor_id;
      document.getElementById("aula").value = a.aula || "";
      document.getElementById("ciclo").value = a.ciclo_id || "";

      modoEdicion = true;
      asignacionEditando = id;
      document.querySelector(".btn-nuevo").innerHTML =
        '<i class="fa-solid fa-floppy-disk"></i> Actualizar Asignación';
      document.getElementById("btnCancelar").classList.remove("hidden");
      mostrarAlerta("✏️ Editando asignación completa.", "info");

      await cargarHorarioGrupo(a.grupo_id);
      actualizarTablaAsignaciones(a.grupo_id);
    });
}

// ==========================================================
// 🗑️ Eliminar asignación completa (con alerta personalizada)
// ==========================================================
async function eliminarAsignacion(id) {
  mostrarConfirmacion(
    "Eliminar asignación",
    "¿Seguro que deseas eliminar esta asignación completa? Esta acción no se puede deshacer.",
    async () => {
      try {
        const res = await fetch(
          `${baseUrl}admin/asignaciones/eliminar-profesor/${id}`
        );
        const data = await res.json();

        mostrarAlerta(data.msg, data.ok ? "success" : "error");

        if (data.ok) {
          await cargarHorarioGrupo(grupoSelect.value);
          actualizarTablaAsignaciones(grupoSelect.value);
        }
      } catch (err) {
        console.error("Error al eliminar asignación:", err);
        mostrarAlerta("Error al eliminar asignación.", "error");
      }
    },
    () => {
      mostrarAlerta("Operación cancelada", "info");
    }
  );
}

// ==========================================================
// 🔄 Actualizar tabla de asignaciones
// ==========================================================
async function actualizarTablaAsignaciones(grupoId) {
  const tabla = document.querySelector("#tablaAsignaciones tbody");
  if (!tabla) return;
  const res = await fetch(
    `${baseUrl}admin/asignaciones/horario-grupo/${grupoId}`
  );
  const data = await res.json();
  if (!data.ok) return;
  tabla.innerHTML = "";
  data.asignaciones.forEach((a) => {
    tabla.innerHTML += `
      <tr>
        <td>${a.materia}</td>
        <td>${a.profesor}</td>
        <td>${a.dias.join(",")}</td>
        <td>${a.inicio_str} - ${a.fin_str}</td>
        <td>
          <button class="btn-eliminar" data-id="${a.id}">
            <i class="fa fa-trash"></i>
          </button>
        </td>
      </tr>`;
  });
}

// ==========================================================
// 🕒 Eliminar frecuencia individual (una celda, con confirmación)
// ==========================================================
async function eliminarFrecuenciaIndividual(asignacionId, dia, horaInicio) {
  // 🔹 Determinar turno y duración por bloque
  const turno = grupoSelect.selectedOptions[0]?.dataset.turno || "Matutino";
  const duracion = turno.toLowerCase() === "vespertino" ? 40 : 50;

  // 🔹 Calcular hora de fin precisa (+40 o +50 minutos)
  const [hh, mm] = horaInicio.split(":").map(Number);
  const finDate = new Date();
  finDate.setHours(hh, mm + duracion, 0, 0);

  const horaFin = finDate
    .toLocaleTimeString("es-MX", {
      hour: "2-digit",
      minute: "2-digit",
      hour12: false,
    })
    .padStart(5, "0");

  // 🔹 Confirmar acción
  mostrarConfirmacion(
    "Eliminar frecuencia",
    `¿Deseas eliminar la frecuencia del <b>${dia}</b> a las <b>${horaInicio}</b>?`,
    async () => {
      try {
        const res = await fetch(
          `${baseUrl}admin/asignaciones/eliminar-frecuencia/${asignacionId}`,
          {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
              dia,
              inicio: horaInicio,
              fin: horaFin,
            }),
          }
        );

        const data = await res.json();

        if (data.ok) {
          mostrarAlerta(
            data.msg || "✅ Frecuencia eliminada correctamente.",
            "success"
          );
          await cargarHorarioGrupo(grupoSelect.value);
          actualizarTablaAsignaciones(grupoSelect.value);
        } else {
          mostrarAlerta(
            data.msg || "⚠️ No se pudo eliminar la frecuencia.",
            "error"
          );
        }
      } catch (err) {
        console.error("Error al eliminar frecuencia:", err);
        mostrarAlerta("❌ Error al eliminar frecuencia.", "error");
      }
    },
    () => {
      mostrarAlerta("Operación cancelada.", "info");
    }
  );
}

document.addEventListener("DOMContentLoaded", () => {
  agregarEventosSelector();
});
