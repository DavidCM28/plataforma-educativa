document.addEventListener("DOMContentLoaded", function () {
  /* ============================
     🧭 SIDEBAR
     ============================ */
  const toggleBtn = document.getElementById("sidebarToggle");
  const sidebar = document.getElementById("sidebar");
  const content = document.querySelector(".content-dark");

  if (toggleBtn && sidebar && content) {
    sidebar.classList.add("collapsed");
    content.classList.add("collapsed");
    toggleBtn.classList.remove("active");

    toggleBtn.addEventListener("click", () => {
      const isCollapsed = sidebar.classList.toggle("collapsed");
      content.classList.toggle("collapsed", isCollapsed);
      toggleBtn.classList.toggle("active", !isCollapsed);
    });
  }

  /* ============================
     🧩 MODAL CREAR USUARIO
     ============================ */
  const modalCrear = document.getElementById("modalCrearUsuario");
  const btnAbrir = document.getElementById("btnAbrirModal");
  const btnCerrar = modalCrear.querySelector(".close-btn");
  const form = document.getElementById("formCrearUsuario");

  const selectRol = document.getElementById("selectRol");
  const campoCarrera = document.getElementById("campoCarrera");
  const selectCarrera = document.getElementById("selectCarrera");

  // 🔹 Abrir modal
  btnAbrir?.addEventListener("click", () => {
    modalCrear.style.display = "flex";
  });

  // 🔹 Cerrar modal
  const cerrarModalCrear = () => {
    modalCrear.style.display = "none";
    form.reset();
    campoCarrera.style.display = "none";
  };

  btnCerrar?.addEventListener("click", cerrarModalCrear);
  window.addEventListener("click", (e) => {
    if (e.target === modalCrear) cerrarModalCrear();
  });

  /* ============================
     🎓 CARGAR CARRERAS AL ELEGIR ROL ALUMNO
     ============================ */
  selectRol?.addEventListener("change", async () => {
    const texto =
      selectRol.options[selectRol.selectedIndex]?.text.toLowerCase() || "";
    if (texto.includes("alumno")) {
      campoCarrera.style.display = "block";
      selectCarrera.innerHTML =
        '<option value="">Cargando carreras...</option>';

      try {
        const res = await fetch(`${baseUrl}/admin/usuarios/obtenerCarreras`);
        if (!res.ok) throw new Error("Error al cargar carreras");

        const data = await res.json();
        selectCarrera.innerHTML =
          '<option value="">Seleccione una carrera</option>';
        data.forEach((carrera) => {
          const opt = document.createElement("option");
          opt.value = carrera.id;
          opt.textContent = carrera.nombre;
          selectCarrera.appendChild(opt);
        });
      } catch (err) {
        console.error("❌ Error al cargar carreras:", err);
        mostrarAlerta("No se pudieron cargar las carreras", "error");
        selectCarrera.innerHTML = '<option value="">Error al cargar</option>';
      }
    } else {
      campoCarrera.style.display = "none";
      selectCarrera.innerHTML = "";
    }
  });

  /* ============================
     💾 ENVIAR FORMULARIO CREAR USUARIO
     ============================ */
  form?.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const btn = form.querySelector("button[type='submit']");
    btn.disabled = true;

    try {
      const res = await fetch(`${baseUrl}/admin/usuarios/guardar`, {
        method: "POST",
        body: formData,
      });

      const contentType = res.headers.get("Content-Type") || "";

      // 📥 Si devuelve archivo Excel (binario)
      if (
        contentType.includes("application/vnd.openxmlformats") ||
        contentType.includes("application/octet-stream")
      ) {
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "credenciales.xlsx";
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);

        // ✅ Alerta de éxito
        mostrarAlerta("Usuario creado y credenciales descargadas", "success");
        cerrarModalCrear();

        // ✅ Recargar después de un pequeño delay
        setTimeout(() => location.reload(), 1000);

        // 🚫 Importante: detener aquí
        return;
      }

      // 🔹 En caso de que el servidor responda algo inesperado
      if (!res.ok) {
        throw new Error(`Error HTTP ${res.status}`);
      }
    } catch (err) {
      // ⚠️ Solo mostrar error si NO es un AbortError o similar
      if (err.name !== "AbortError") {
        console.error("❌ Error en la petición:", err);
        mostrarAlerta(
          "Ocurrió un error inesperado, pero el usuario pudo haberse creado.",
          "warning"
        );
      }
    } finally {
      btn.disabled = false;
    }
  });

  /* ============================
     ✏️ MODAL EDITAR USUARIO
     ============================ */
  const modalEditar = document.getElementById("modalEditarUsuario");
  const formEditar = document.getElementById("formEditarUsuario");
  const btnCerrarEditar = modalEditar.querySelector(".close-btn");

  document.querySelectorAll(".btn-edit").forEach((btn) => {
    btn.addEventListener("click", async (e) => {
      e.preventDefault();
      const id = btn.dataset.id;

      try {
        const res = await fetch(`${baseUrl}/admin/usuarios/detalle/${id}`);
        const data = await res.json();

        document.getElementById("edit_id").value = data.id;
        document.getElementById("edit_nombres").value = data.nombre || "";
        document.getElementById("edit_apellido_paterno").value =
          data.apellido_paterno || "";
        document.getElementById("edit_apellido_materno").value =
          data.apellido_materno || "";
        document.getElementById("edit_rol_id").value = data.rol_id || "";

        modalEditar.style.display = "flex";
      } catch (err) {
        console.error("❌ Error al cargar usuario:", err);
        mostrarAlerta("No se pudo cargar el usuario", "error");
      }
    });
  });

  const cerrarModalEditar = () => {
    modalEditar.style.display = "none";
    formEditar.reset();
  };

  btnCerrarEditar?.addEventListener("click", cerrarModalEditar);
  window.addEventListener("click", (e) => {
    if (e.target === modalEditar) cerrarModalEditar();
  });

  formEditar?.addEventListener("submit", async (e) => {
    e.preventDefault();

    const id = document.getElementById("edit_id").value;
    const formData = new FormData(formEditar);
    const btn = formEditar.querySelector("button[type='submit']");
    btn.disabled = true;

    try {
      const res = await fetch(`${baseUrl}/admin/usuarios/actualizar/${id}`, {
        method: "POST",
        body: formData,
      });
      const data = await res.json();

      if (data.success) {
        mostrarAlerta("Usuario actualizado correctamente", "success");
        cerrarModalEditar();
        setTimeout(() => location.reload(), 1000);
      } else {
        mostrarAlerta("No se pudo actualizar el usuario", "warning");
      }
    } catch (err) {
      console.error(err);
      mostrarAlerta("Error al actualizar el usuario", "error");
    } finally {
      btn.disabled = false;
    }
  });

  /* ============================
     🗑️ ELIMINAR USUARIO
     ============================ */
  document.querySelectorAll(".btn-delete").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const url = btn.dataset.deleteUrl;
      const fila = btn.closest("tr");

      mostrarConfirmacion(
        "Eliminar usuario",
        "¿Deseas eliminar este usuario? Esta acción no se puede deshacer.",
        async () => {
          try {
            const res = await fetch(url, { method: "DELETE" });

            const data = await res.json();

            if (res.ok && data.success) {
              fila.style.transition = "opacity 0.4s ease";
              fila.style.opacity = "0";
              setTimeout(() => fila.remove(), 400);
              mostrarAlerta("Usuario eliminado correctamente", "success");
            } else {
              mostrarAlerta("No se pudo eliminar el usuario", "error");
            }
          } catch (err) {
            console.error(err);
            mostrarAlerta("Error de red o servidor", "error");
          }
        }
      );
    });
  });

  /* ============================
   👤 MODAL DETALLE DE USUARIO
   ============================ */
  const userModal = document.getElementById("userModal");
  const closeUserModal = userModal?.querySelector(".close-btn");

  // Cerrar modal (reutilizable)
  const cerrarDetalleUsuario = () => {
    if (!userModal) return;
    userModal.classList.remove("show");
    setTimeout(() => (userModal.style.display = "none"), 150);
  };

  // Abrir modal con los datos del usuario
  const abrirDetalleUsuario = (usuario) => {
    if (!userModal) return;

    // Nombre completo
    document.getElementById("modal-nombre").textContent = `${usuario.nombre} ${
      usuario.apellido_paterno || ""
    } ${usuario.apellido_materno || ""}`.trim();

    document.getElementById("modal-email").textContent = usuario.email || "—";

    /* === Rol con ícono y color según tipo === */
    const modalRol = document.getElementById("modal-rol");
    let icono = "fa-user";
    let color = "var(--text)";

    if (usuario.rol) {
      const rol = usuario.rol.toLowerCase();
      if (rol.includes("alumno")) {
        icono = "fa-graduation-cap";
        color = "#4ade80"; // verde
      } else if (rol.includes("profesor")) {
        icono = "fa-chalkboard-teacher";
        color = "#60a5fa"; // azul
      } else if (rol.includes("admin")) {
        icono = "fa-gear";
        color = "#facc15"; // amarillo
      } else if (rol.includes("escolar")) {
        icono = "fa-building-columns";
        color = "#f97316"; // naranja
      }
    }

    modalRol.innerHTML = `<i class="fa-solid ${icono}" style="color:${color}"></i> Rol: ${
      usuario.rol || "—"
    }`;

    // Matrícula / Empleado
    document.getElementById("modal-matricula").textContent =
      usuario.matricula || "—";
    document.getElementById("modal-num_empleado").textContent =
      usuario.num_empleado || "—";

    // Fechas y estado
    document.getElementById("modal-ultimo_login").textContent =
      usuario.ultimo_login || "—";
    document.getElementById("modal-created_at").textContent =
      usuario.created_at || "—";
    document.getElementById("modal-updated_at").textContent =
      usuario.updated_at || "—";
    document.getElementById("modal-verificado").textContent =
      usuario.verificado == 1 ? "Sí" : "No";
    document.getElementById("modal-deleted_at").textContent = usuario.deleted_at
      ? usuario.deleted_at
      : "No eliminado";

    // Mostrar u ocultar matrícula / empleado según corresponda
    const esAlumno = usuario.matricula && !usuario.num_empleado;
    document.getElementById("detalleMatricula").style.display = esAlumno
      ? "flex"
      : "none";
    document.getElementById("detalleEmpleado").style.display = esAlumno
      ? "none"
      : "flex";

    // Foto
    const foto = document.getElementById("modal-foto");
    foto.src = usuario.foto
      ? usuario.foto
      : "https://cdn-icons-png.flaticon.com/512/847/847969.png";
    foto.alt = usuario.nombre || "Usuario";

    // Estado activo/inactivo
    const estado = document.getElementById("modal-activo");
    estado.textContent = usuario.activo == 1 ? "Activo" : "Inactivo";
    estado.style.background =
      usuario.activo == 1 ? "rgba(0,200,100,0.2)" : "rgba(200,0,0,0.2)";
    estado.style.color = usuario.activo == 1 ? "#00ff9c" : "#ff6b6b";

    /* === Carrera (si existe) === */
    const carreraExistente = document.querySelector("#info-carrera");
    if (carreraExistente) carreraExistente.remove(); // evita duplicar si se abre varias veces

    if (usuario.carrera) {
      const carreraRow = document.createElement("div");
      carreraRow.className = "info-row";
      carreraRow.id = "info-carrera";
      carreraRow.innerHTML = `
      <i class="fa fa-graduation-cap"></i>
      <div><strong>Carrera:</strong><p>${usuario.carrera}</p></div>
    `;
      document.querySelector(".user-info").appendChild(carreraRow);
    }

    // Mostrar modal
    userModal.style.display = "flex";
    userModal.classList.add("show");
  };

  // Evento click en los botones .btn-view
  document.querySelectorAll(".btn-view").forEach((btn) => {
    btn.addEventListener("click", async (e) => {
      e.preventDefault();
      const id = btn.dataset.id;

      try {
        const res = await fetch(`${baseUrl}/admin/usuarios/detalle/${id}`);
        const data = await res.json();
        abrirDetalleUsuario(data);
      } catch (err) {
        console.error("❌ Error al obtener detalles:", err);
        mostrarAlerta(
          "No se pudieron cargar los detalles del usuario",
          "error"
        );
      }
    });
  });

  // Cerrar modal
  closeUserModal?.addEventListener("click", cerrarDetalleUsuario);
  window.addEventListener("click", (e) => {
    if (e.target === userModal) cerrarDetalleUsuario();
  });
});
/* ============================
   📤 MODAL IMPORTAR EXCEL
   ============================ */
const modalImportar = document.getElementById("modalImportar");
const btnImportar = document.getElementById("btnImportarExcel");
const btnCerrarImportar = modalImportar.querySelector(".close-btn");

btnImportar?.addEventListener("click", () => {
  modalImportar.style.display = "flex";
});

btnCerrarImportar?.addEventListener("click", () => {
  modalImportar.style.display = "none";
});

window.addEventListener("click", (e) => {
  if (e.target === modalImportar) modalImportar.style.display = "none";
});
