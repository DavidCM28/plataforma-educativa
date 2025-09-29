document.addEventListener("DOMContentLoaded", function () {
  // === Sidebar toggle ===
  const toggleBtn = document.getElementById("sidebarToggle");
  const sidebar = document.getElementById("sidebar");
  const content = document.querySelector(".content");
  if (toggleBtn && sidebar && content) {
    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
      content.classList.toggle("collapsed");
    });
  }

  // === Modal Detalle de Usuario ===
  const modalDetalle = document.getElementById("userModal");
  const closeDetalle = modalDetalle.querySelector(".close-btn");

  document.querySelectorAll(".btn-view").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault(); // evita saltar a "#"
      const id = this.dataset.id;

      fetch(`${baseUrl}/admin/usuarios/detalle/${id}`)
        .then((res) => {
          if (!res.ok) throw new Error("Error HTTP");
          return res.json();
        })
        .then((data) => {
          if (!data) return;

          document.getElementById("modal-nombre").innerText = data.nombre || "";
          document.getElementById("modal-apellido-pat").innerText =
            data.apellido_paterno || "";
          document.getElementById("modal-apellido-mat").innerText =
            data.apellido_materno || "";
          document.getElementById("modal-email").innerText = data.email || "";
          document.getElementById("modal-matricula").innerText =
            data.matricula || "";
          document.getElementById("modal-num_empleado").innerText =
            data.num_empleado || "";
          document.getElementById("modal-rol").innerText = data.rol || "";
          document.getElementById("modal-activo").innerText =
            data.activo == 1 ? "Sí" : "No";
          document.getElementById("modal-verificado").innerText =
            data.verificado == 1 ? "Sí" : "No";
          document.getElementById("modal-ultimo_login").innerText =
            data.ultimo_login || "Nunca";
          document.getElementById("modal-created_at").innerText =
            data.created_at || "-";
          document.getElementById("modal-updated_at").innerText =
            data.updated_at || "-";
          document.getElementById("modal-deleted_at").innerText =
            data.deleted_at || "No eliminado";
          document.getElementById("modal-foto").src = data.foto
            ? `${baseUrl}/uploads/usuarios/${data.foto}`
            : `${baseUrl}/assets/img/default-user.png`;

          // Mostrar solo campos relevantes
          const detalleMatricula = document.getElementById("detalleMatricula");
          const detalleEmpleado = document.getElementById("detalleEmpleado");

          if (data.rol && data.rol.toLowerCase().includes("alumno")) {
            detalleMatricula.style.display = "block";
            detalleEmpleado.style.display = "none";
          } else {
            detalleMatricula.style.display = "none";
            detalleEmpleado.style.display = "block";
          }

          modalDetalle.style.display = "flex";
        })
        .catch((err) => {
          console.error("Error al obtener usuario:", err);
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudo cargar la información del usuario.",
          });
        });
    });
  });

  closeDetalle.addEventListener("click", () => {
    modalDetalle.style.display = "none";
  });

  window.addEventListener("click", (e) => {
    if (e.target === modalDetalle) modalDetalle.style.display = "none";
  });

  // === Modal Crear Usuario ===
  const modalCrear = document.getElementById("modalCrearUsuario");
  const btnAbrir = document.getElementById("btnAbrirModal");
  const btnCerrar = modalCrear.querySelector(".close-btn");
  const form = document.getElementById("formCrearUsuario");

  btnAbrir.addEventListener("click", () => {
    modalCrear.style.display = "flex";
  });

  btnCerrar.addEventListener("click", () => {
    modalCrear.style.display = "none";
    form.reset();
  });

  window.addEventListener("click", (e) => {
    if (e.target === modalCrear) {
      modalCrear.style.display = "none";
      form.reset();
    }
  });

  // === Enviar formulario Crear Usuario (AJAX) ===
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    try {
      const res = await fetch(`${baseUrl}/admin/usuarios/guardar`, {
        method: "POST",
        body: formData,
      });

      if (!res.ok) throw new Error("Error en el servidor");

      const data = await res.json();

      if (data.success) {
        let infoExtra = "";

        if (data.matricula) {
          infoExtra += `Matrícula: <b>${data.matricula}</b><br>`;
        } else if (data.num_empleado) {
          infoExtra += `Número de empleado: <b>${data.num_empleado}</b><br>`;
        }

        Swal.fire({
          icon: "success",
          title: "Usuario creado correctamente",
          html: `
            ${infoExtra}
            Correo: <b>${data.email}</b><br>
            Contraseña temporal: <b>${data.password}</b>
          `,
          confirmButtonText: "Aceptar",
        }).then(() => {
          modalCrear.style.display = "none";
          form.reset();
          location.reload();
        });
      } else {
        Swal.fire({
          icon: "warning",
          title: "Atención",
          text: "No se pudo generar correctamente el usuario.",
        });
      }
    } catch (error) {
      console.error("Error al guardar usuario:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Hubo un problema al crear el usuario.",
      });
    }
  });

  // === Modal Editar Usuario ===
  const modalEditar = document.getElementById("modalEditarUsuario");
  const formEditar = document.getElementById("formEditarUsuario");
  const btnCerrarEditar = modalEditar.querySelector(".close-btn");

  // Abrir modal con datos
  document.querySelectorAll(".btn-edit").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const id = this.dataset.id;

      fetch(`${baseUrl}/admin/usuarios/detalle/${id}`)
        .then((res) => res.json())
        .then((data) => {
          if (!data) return;

          // Rellenar formulario
          document.getElementById("edit_id").value = data.id;
          document.getElementById("edit_nombres").value = data.nombre || "";
          document.getElementById("edit_apellido_paterno").value =
            data.apellido_paterno || "";
          document.getElementById("edit_apellido_materno").value =
            data.apellido_materno || "";
          document.getElementById("edit_rol_id").value = data.rol_id || "";

          modalEditar.style.display = "flex";
        })
        .catch((err) => {
          console.error("Error al cargar datos:", err);
          Swal.fire(
            "Error",
            "No se pudo cargar la información del usuario.",
            "error"
          );
        });
    });
  });

  // Cerrar modal
  btnCerrarEditar.addEventListener("click", () => {
    modalEditar.style.display = "none";
    formEditar.reset();
  });

  window.addEventListener("click", (e) => {
    if (e.target === modalEditar) {
      modalEditar.style.display = "none";
      formEditar.reset();
    }
  });

  // Enviar actualización
  formEditar.addEventListener("submit", async (e) => {
    e.preventDefault();

    const id = document.getElementById("edit_id").value;
    const formData = new FormData(formEditar);

    try {
      const res = await fetch(`${baseUrl}/admin/usuarios/actualizar/${id}`, {
        method: "POST",
        body: formData,
      });

      const data = await res.json();

      if (data.success) {
        Swal.fire({
          icon: "success",
          title: "Usuario actualizado correctamente",
          confirmButtonText: "Aceptar",
        }).then(() => {
          modalEditar.style.display = "none";
          formEditar.reset();
          location.reload();
        });
      } else {
        Swal.fire("Atención", "No se pudo actualizar el usuario.", "warning");
      }
    } catch (error) {
      console.error("Error al actualizar usuario:", error);
      Swal.fire("Error", "Hubo un problema al actualizar el usuario.", "error");
    }
  });

  // === Eliminar Usuario con SweetAlert ===
  document.querySelectorAll(".btn-delete").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();

      const url = this.getAttribute("href"); // ✅ ahora obtiene la URL correcta
      const fila = this.closest("tr"); // ✅ para eliminar la fila visualmente sin recargar

      Swal.fire({
        title: "¿Estás seguro?",
        text: "Este usuario será eliminado permanentemente.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(url, { method: "GET" })
            .then((res) => {
              if (!res.ok) throw new Error("Error HTTP");
              return res.json();
            })
            .then((data) => {
              if (data.success) {
                // ✅ Eliminar la fila sin recargar
                fila.style.transition = "opacity 0.4s ease";
                fila.style.opacity = "0";
                setTimeout(() => fila.remove(), 400);

                Swal.fire({
                  icon: "success",
                  title: "Eliminado",
                  text:
                    data.message || "El usuario fue eliminado correctamente.",
                  timer: 1500,
                  showConfirmButton: false,
                });
              } else {
                Swal.fire(
                  "Atención",
                  data.message || "No se pudo eliminar el usuario.",
                  "warning"
                );
              }
            })
            .catch((err) => {
              console.error("Error al eliminar:", err);
              Swal.fire("Error", "No se pudo eliminar el usuario.", "error");
            });
        }
      });
    });
  });
});
