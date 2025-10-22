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
    `${baseUrl}admin/asignaciones-alumnos/vincular-alumno-carrera`, // ✅ CORRECTO
    {
      method: "POST",
      body: formData,
    }
  );

  const data = await res.json();
  mostrarAlerta(data.msg, data.ok ? "success" : "error");

  if (data.ok) {
    // limpia el formulario
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
// 📈 Promover grupo al siguiente ciclo (con confirmación personalizada)
// ==========================================================
const btnPromover = document.getElementById("btnPromoverGrupo");
btnPromover?.addEventListener("click", async () => {
  const grupoId = document.getElementById("grupoPromover").value;
  if (!grupoId) {
    mostrarAlerta("Selecciona un grupo primero.", "warning");
    return;
  }

  // 🔹 Confirmación personalizada
  mostrarConfirmacion(
    "Promover grupo",
    "¿Deseas crear el grupo del siguiente ciclo y promover a los alumnos activos?",
    async () => {
      try {
        const res = await fetch(
          `${baseUrl}admin/asignaciones-alumnos/promover-grupo/${grupoId}`,
          { method: "POST" }
        );

        const data = await res.json();

        if (data.ok) {
          mostrarAlerta(data.msg || "Grupo promovido correctamente", "success");

          if (data.nuevoGrupo) {
            const existeOpcion = Array.from(grupoPromover.options).some(
              (opt) => opt.textContent === data.nuevoGrupo.grupo
            );

            // ✅ Solo agregar si no existía previamente
            if (!existeOpcion && !data.yaExistia) {
              const opt = document.createElement("option");
              opt.value = data.nuevoGrupo.id;
              opt.textContent = data.nuevoGrupo.grupo;
              grupoPromover.appendChild(opt);

              gruposTotales.push(data.nuevoGrupo);
            }

            // ✅ Seleccionar el grupo destino (nuevo o existente)
            grupoPromover.value = data.nuevoGrupo.id;

            // 🔄 Refrescar tabla de alumnos inscritos
            actualizarAlumnosInscritos(data.nuevoGrupo.id);
          }
        } else {
          mostrarAlerta(data.msg || "Error al promover el grupo", "error");
        }
      } catch (err) {
        console.error(err);
        mostrarAlerta("Error de conexión o servidor", "error");
      }
    }
  );
});

// ==========================================================
// 👥 Cargar alumnos inscritos al seleccionar grupo de promoción
// ==========================================================
const grupoPromoverSelect = document.getElementById("grupoPromover");

async function actualizarAlumnosInscritos(grupoId) {
  const tbody = document.getElementById("tablaAlumnosInscritos");
  tbody.innerHTML = "";

  if (!grupoId) return;

  try {
    const res = await fetch(
      `${baseUrl}admin/asignaciones-alumnos/alumnos-inscritos/${grupoId}`
    );
    const data = await res.json();

    if (data.ok && data.alumnos.length) {
      // ✅ Ordenar por matrícula (alfanuméricamente)
      const alumnosOrdenados = [...data.alumnos].sort((a, b) => {
        if (!a.matricula) return 1;
        if (!b.matricula) return -1;
        return a.matricula.localeCompare(b.matricula, undefined, {
          numeric: true,
        });
      });

      alumnosOrdenados.forEach((a) => {
        tbody.innerHTML += `
      <tr>
        <td><input type="checkbox" class="check-alumno" data-id="${a.id}"></td>
        <td>${a.matricula || ""}</td>
        <td>${a.alumno}</td>
        <td>${
          grupoPromoverSelect.options[grupoPromoverSelect.selectedIndex].text
        }</td>
        <td><span class="badge">${a.estatus}</span></td>
        <td>
          <button type="button" class="btn-mini btn-eliminar" data-id="${a.id}">
            <i class="fa fa-trash"></i>
          </button>
        </td>
      </tr>`;
      });
    } else {
      tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#999;">${
        data.msg || "Sin alumnos"
      }</td></tr>`;
    }
  } catch (err) {
    console.error(err);
    mostrarAlerta("Error al cargar los alumnos", "error");
  }
}

grupoPromoverSelect?.addEventListener("change", () => {
  const grupoId = grupoPromoverSelect.value;
  actualizarAlumnosInscritos(grupoId);
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

// 🔹 Crear el filtro visual
const filtroCarreraPromover = document.createElement("select");
filtroCarreraPromover.id = "filtroCarreraPromover";
filtroCarreraPromover.innerHTML = `<option value="">-- Todas las carreras --</option>`;

// Insertarlo antes del label “Seleccionar grupo”
const labelGrupo = document.querySelector(".gestion-grupos label");
if (labelGrupo) {
  labelGrupo.insertAdjacentElement("beforebegin", filtroCarreraPromover);
}

// 🔹 Llenar el select de carreras
(Array.isArray(carrerasLista) ? carrerasLista : []).forEach((c) => {
  const opt = document.createElement("option");
  opt.value = c.id;
  opt.textContent = c.nombre;
  filtroCarreraPromover.appendChild(opt);
});

// 🔹 Referencia al select de grupos
const grupoPromover = document.getElementById("grupoPromover");

// 🔹 Llenar todos los grupos al inicio
function llenarGruposPromover(lista) {
  grupoPromover.innerHTML = '<option value="">-- Selecciona grupo --</option>';
  lista.forEach((g) => {
    const opt = document.createElement("option");
    opt.value = g.id;
    opt.textContent = g.grupo;
    grupoPromover.appendChild(opt);
  });
}
llenarGruposPromover(gruposTotales);

// 🔹 Cuando cambie la carrera seleccionada
filtroCarreraPromover.addEventListener("change", async (e) => {
  const carreraId = e.target.value;

  // Si selecciona "todas las carreras", mostrar todos los grupos otra vez
  if (!carreraId) {
    llenarGruposPromover(gruposTotales);
    return;
  }

  try {
    const res = await fetch(
      `${baseUrl}admin/asignaciones-alumnos/grupos-por-carrera/${carreraId}`
    );
    const data = await res.json();

    if (data.ok && data.grupos.length) {
      llenarGruposPromover(data.grupos);
    } else {
      grupoPromover.innerHTML =
        '<option value="">Sin grupos disponibles</option>';
      mostrarAlerta(data.msg || "Sin grupos para esta carrera", "warning");
    }
  } catch (err) {
    grupoPromover.innerHTML =
      '<option value="">Error al cargar grupos</option>';
  }
});
// ==========================================================
// 🗑️ Eliminar alumno del grupo (dinámico y con confirmación)
// ==========================================================
document.addEventListener("click", async (e) => {
  const btn = e.target.closest(".btn-eliminar");
  if (!btn) return;

  const alumnoId = btn.dataset.id;
  if (!alumnoId) return;

  mostrarConfirmacion(
    "Eliminar alumno",
    "¿Deseas eliminar al alumno de este grupo?",
    async () => {
      try {
        const res = await fetch(
          `${baseUrl}admin/asignaciones-alumnos/eliminar-alumno/${alumnoId}`,
          { method: "DELETE" }
        );

        const data = await res.json();

        if (data.ok) {
          mostrarAlerta(data.msg, "success");
          // 🔹 Animación y eliminación visual
          const fila = btn.closest("tr");
          if (fila) {
            fila.style.transition = "all 0.3s ease";
            fila.style.opacity = "0";
            setTimeout(() => fila.remove(), 300);
          }
        } else {
          mostrarAlerta(data.msg || "No se pudo eliminar al alumno", "error");
        }
      } catch (err) {
        console.error(err);
        mostrarAlerta("Error de conexión o servidor", "error");
      }
    }
  );
});
// ==========================================================
// 🎓 Asignar alumnos a grupo (sin recargar la página)
// ==========================================================
const formAsignarAlumnos = document.getElementById("formAsignarAlumnos");

formAsignarAlumnos?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = new FormData(formAsignarAlumnos);

  try {
    const res = await fetch(
      `${baseUrl}admin/asignaciones-alumnos/asignar-alumno`,
      {
        method: "POST",
        body: formData,
      }
    );

    const data = await res.json();

    // ⚠️ Si excede el límite, preguntar
    if (data.excede) {
      mostrarConfirmacion(
        "Límite alcanzado",
        data.msg,
        async () => {
          // Si acepta, crear nuevo grupo
          const grupoId = formData.get("grupo_id");
          const res2 = await fetch(
            `${baseUrl}admin/asignaciones-alumnos/crear-grupo-extra/${grupoId}`,
            {
              method: "POST",
            }
          );
          const data2 = await res2.json();
          mostrarAlerta(data2.msg, data2.ok ? "success" : "error");
        },
        () => {
          mostrarAlerta("Solo se guardarán los primeros 40 alumnos.", "info");
        }
      );
      return;
    }

    mostrarAlerta(data.msg, data.ok ? "success" : "error");

    if (data.ok) {
      formAsignarAlumnos.reset();
      document.getElementById("alumnosSelect").innerHTML =
        '<option value="">-- Selecciona alumno --</option>';
      const grupoId = document.getElementById("grupoAlumnoSelect").value;
      if (grupoId) actualizarAlumnosInscritos(grupoId);
    }
  } catch (err) {
    console.error(err);
    mostrarAlerta("Error de conexión o servidor", "error");
  }
});
// ==========================================================
// 🗑️ Eliminar varios alumnos seleccionados
// ==========================================================
const btnEliminarSeleccionados = document.getElementById(
  "btnEliminarSeleccionados"
);
const selectAllAlumnos = document.getElementById("selectAllAlumnos");

btnEliminarSeleccionados?.addEventListener("click", async () => {
  const checks = document.querySelectorAll(".check-alumno:checked");
  if (checks.length === 0) {
    mostrarAlerta("Selecciona al menos un alumno para eliminar.", "warning");
    return;
  }

  const ids = Array.from(checks).map((c) => c.dataset.id);

  mostrarConfirmacion(
    "Eliminar seleccionados",
    `¿Seguro que deseas eliminar a <b>${ids.length}</b> alumno(s) del grupo?`,
    async () => {
      try {
        const res = await fetch(
          `${baseUrl}admin/asignaciones-alumnos/eliminar-multiples`,
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ ids }),
          }
        );

        const data = await res.json();
        mostrarAlerta(data.msg, data.ok ? "success" : "error");

        if (data.ok) {
          const grupoId = document.getElementById("grupoPromover").value;
          if (grupoId) actualizarAlumnosInscritos(grupoId);
        }
      } catch (err) {
        console.error(err);
        mostrarAlerta("Error al eliminar alumnos", "error");
      }
    }
  );
});

// 🔘 Seleccionar todos
selectAllAlumnos?.addEventListener("change", (e) => {
  const checks = document.querySelectorAll(".check-alumno");
  checks.forEach((ch) => (ch.checked = e.target.checked));
});
