// ==========================================================
// 🧑‍🏫 Tabs principales (Profesores / Alumnos)
// ==========================================================
document.querySelectorAll(".tab-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    document
      .querySelectorAll(".tab-btn")
      .forEach((b) => b.classList.remove("active"));
    document
      .querySelectorAll(".tab-content")
      .forEach((c) => c.classList.remove("active"));

    btn.classList.add("active");
    const target = document.getElementById(btn.dataset.tab);
    if (target) target.classList.add("active");
  });
});

// ==========================================================
// 🎓 Subtabs (Carreras / Grupos)
// ==========================================================
document.querySelectorAll(".subtab-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    document
      .querySelectorAll(".subtab-btn")
      .forEach((b) => b.classList.remove("active"));
    document
      .querySelectorAll(".subtab-content")
      .forEach((c) => c.classList.remove("active"));

    btn.classList.add("active");
    const target = document.getElementById("subtab-" + btn.dataset.subtab);
    if (target) target.classList.add("active");
  });
});

// ==========================================================
// 🎓 Vincular alumno a carrera (sin recargar la página)
// ==========================================================
const formVincularCarrera = document.getElementById("formVincularCarrera");
formVincularCarrera?.addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(formVincularCarrera);
  const res = await fetch(
    `${baseUrl}admin/asignaciones-alumnos/vincular-alumno-carrera`,
    {
      method: "POST",
      body: formData,
    }
  );

  const data = await res.json();
  mostrarAlerta(data.msg, data.ok ? "success" : "error");

  if (data.ok) {
    // 🔹 Limpia campos y deja la interfaz lista
    formVincularCarrera.reset();
    document.getElementById("buscadorAlumno").value = "";
    document.getElementById("alumno_id").value = "";
    document.getElementById("resultadosBusqueda").innerHTML = "";
  }
  document.getElementById("buscadorAlumno").focus();
});

// ==========================================================
// 🧠 Cargar alumnos según carrera del grupo seleccionado
// ==========================================================
document
  .getElementById("grupoAlumnoSelect")
  ?.addEventListener("change", async (e) => {
    const grupoId = e.target.value;
    if (!grupoId) return;
    const res = await fetch(
      `${baseUrl}admin/asignaciones-alumnos/alumnos-por-carrera/${grupoId}`
    );

    const data = await res.json();
    const sel = document.getElementById("alumnosSelect");
    sel.innerHTML = "";
    if (data.ok && data.alumnos.length) {
      data.alumnos.forEach((a) => {
        sel.innerHTML += `<option value="${a.id}">${a.nombre}</option>`;
      });
    } else {
      sel.innerHTML = '<option value="">Sin alumnos disponibles</option>';
      mostrarAlerta(data.msg || "Sin alumnos para esta carrera.", "warning");
    }
  });
// ==========================================================
// 🔍 Búsqueda en vivo de alumnos
// ==========================================================
const buscador = document.getElementById("buscadorAlumno");
const listaResultados = document.getElementById("resultadosBusqueda");
const inputAlumnoId = document.getElementById("alumno_id");
const selectCarrera = document.getElementById("carreraSelect");

let timeoutBusqueda;

buscador?.addEventListener("input", () => {
  const q = buscador.value.trim();
  clearTimeout(timeoutBusqueda);

  if (q.length < 2) {
    listaResultados.innerHTML = "";
    return;
  }

  timeoutBusqueda = setTimeout(async () => {
    const res = await fetch(
      `${baseUrl}admin/asignaciones-alumnos/buscar-alumno?q=${encodeURIComponent(
        q
      )}`
    );
    const data = await res.json();

    listaResultados.innerHTML = "";

    if (data.ok && data.alumnos.length > 0) {
      data.alumnos.forEach((a) => {
        const li = document.createElement("li");
        li.textContent = `${a.nombre_completo} (${
          a.matricula || "Sin matrícula"
        })`;

        li.dataset.id = a.id;
        li.dataset.carrera = a.carrera || "";
        listaResultados.appendChild(li);
      });
    } else {
      listaResultados.innerHTML =
        "<li class='sin-resultados'>No se encontraron alumnos</li>";
    }
  }, 300); // pequeña pausa para evitar muchas peticiones
});

// Al hacer clic en un resultado
listaResultados?.addEventListener("click", (e) => {
  if (
    e.target.tagName !== "LI" ||
    e.target.classList.contains("sin-resultados")
  )
    return;

  const nombre = e.target.textContent;
  const alumnoId = e.target.dataset.id;
  const carreraActual = e.target.dataset.carrera;

  buscador.value = nombre;
  inputAlumnoId.value = alumnoId;
  listaResultados.innerHTML = "";

  // Si ya tiene carrera, la selecciona
  if (carreraActual) {
    for (const opt of selectCarrera.options) {
      opt.selected = opt.text === carreraActual;
    }
    mostrarAlerta(
      `El alumno ya pertenece a "${carreraActual}". Puedes reasignarlo.`,
      "info"
    );
  } else {
    selectCarrera.value = "";
  }
});
// ==========================================================
// 📈 Promover grupo al siguiente ciclo
// ==========================================================
const btnPromover = document.getElementById("btnPromoverGrupo");
btnPromover?.addEventListener("click", async () => {
  const grupoId = document.getElementById("grupoPromover").value;
  if (!grupoId) {
    mostrarAlerta("Selecciona un grupo primero.", "warning");
    return;
  }

  if (
    !confirm(
      "¿Deseas crear el grupo del siguiente ciclo y promover a los alumnos activos?"
    )
  )
    return;

  const res = await fetch(
    `${baseUrl}admin/asignaciones-alumnos/promover-grupo/${grupoId}`,
    {
      method: "POST",
    }
  );
  const data = await res.json();

  mostrarAlerta(data.msg, data.ok ? "success" : "error");
});
// ==========================================================
// 👥 Cargar alumnos inscritos al seleccionar grupo de promoción
// ==========================================================
const grupoPromoverSelect = document.getElementById("grupoPromover");
grupoPromoverSelect?.addEventListener("change", async () => {
  const grupoId = grupoPromoverSelect.value;
  const tbody = document.getElementById("tablaAlumnosInscritos");
  tbody.innerHTML = "";

  if (!grupoId) return;

  const res = await fetch(
    `${baseUrl}admin/asignaciones-alumnos/alumnos-inscritos/${grupoId}`
  );
  const data = await res.json();

  if (data.ok && data.alumnos.length) {
    data.alumnos.forEach((a) => {
      tbody.innerHTML += `
        <tr>
          <td>${a.matricula}</td>
          <td>${a.alumno}</td>
          <td>${
            grupoPromoverSelect.options[grupoPromoverSelect.selectedIndex].text
          }</td>
          <td><span class="badge">${a.estatus}</span></td>
          <td>
            <button type="button" class="btn-mini btn-eliminar" data-id="${
              a.id
            }">
              <i class="fa fa-trash"></i>
            </button>
          </td>
        </tr>`;
    });
  } else {
    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:#999;">${data.msg}</td></tr>`;
  }
});
// ==========================================================
// 🎓 Filtrar grupos por carrera seleccionada (Asignar alumnos)
// ==========================================================
const filtroCarrera = document.getElementById("filtroCarreraSelect");
const grupoAlumnoSelect = document.getElementById("grupoAlumnoSelect");

filtroCarrera?.addEventListener("change", async (e) => {
  const carreraId = e.target.value;
  grupoAlumnoSelect.innerHTML = '<option value="">Cargando...</option>';

  if (!carreraId) {
    // 🔁 Volver a mostrar todos los grupos del ciclo 1
    grupoAlumnoSelect.innerHTML =
      '<option value="">-- Selecciona grupo --</option>';
    (gruposPrimerCiclo || []).forEach((g) => {
      const opt = document.createElement("option");
      opt.value = g.id;
      opt.textContent = g.grupo;
      grupoAlumnoSelect.appendChild(opt);
    });
    return;
  }

  try {
    const res = await fetch(
      `${baseUrl}admin/asignaciones-alumnos/grupos-por-carrera/${carreraId}`
    );
    const data = await res.json();

    grupoAlumnoSelect.innerHTML = "";

    if (data.ok && data.grupos.length) {
      grupoAlumnoSelect.innerHTML =
        '<option value="">-- Selecciona grupo --</option>';
      data.grupos.forEach((g) => {
        const opt = document.createElement("option");
        opt.value = g.id;
        opt.textContent = g.grupo;
        grupoAlumnoSelect.appendChild(opt);
      });
    } else {
      grupoAlumnoSelect.innerHTML =
        '<option value="">Sin grupos disponibles</option>';
      mostrarAlerta(data.msg || "Sin grupos para esta carrera", "warning");
    }
  } catch (err) {
    grupoAlumnoSelect.innerHTML =
      '<option value="">Error al cargar grupos</option>';
  }
});

// ==========================================================
// 🚀 Filtrar grupos por carrera (Promover grupo al siguiente ciclo)
// ==========================================================
const filtroCarreraPromover = document.createElement("select");
filtroCarreraPromover.id = "filtroCarreraPromover";
filtroCarreraPromover.innerHTML = `<option value="">-- Todas las carreras --</option>`;
document
  .querySelector(".gestion-grupos label")
  .insertAdjacentElement("beforebegin", filtroCarreraPromover);

(carrerasLista || []).forEach((c) => {
  const opt = document.createElement("option");
  opt.value = c.id;
  opt.textContent = c.nombre;
  filtroCarreraPromover.appendChild(opt);
});

const grupoPromover = document.getElementById("grupoPromover");

filtroCarreraPromover.addEventListener("change", async (e) => {
  const carreraId = e.target.value;
  grupoPromover.innerHTML = '<option value="">Cargando...</option>';

  if (!carreraId) {
    grupoPromover.innerHTML =
      '<option value="">-- Selecciona grupo --</option>';
    (gruposPrimerCiclo || []).forEach((g) => {
      const opt = document.createElement("option");
      opt.value = g.id;
      opt.textContent = g.grupo;
      grupoPromover.appendChild(opt);
    });
    return;
  }

  const res = await fetch(
    `${baseUrl}admin/asignaciones-alumnos/grupos-por-carrera/${carreraId}`
  );
  const data = await res.json();

  grupoPromover.innerHTML = "";

  if (data.ok && data.grupos.length) {
    grupoPromover.innerHTML =
      '<option value="">-- Selecciona grupo --</option>';
    data.grupos.forEach((g) => {
      const opt = document.createElement("option");
      opt.value = g.id;
      opt.textContent = g.grupo;
      grupoPromover.appendChild(opt);
    });
  } else {
    grupoPromover.innerHTML =
      '<option value="">Sin grupos disponibles</option>';
    mostrarAlerta(data.msg || "Sin grupos para esta carrera", "warning");
  }
});
