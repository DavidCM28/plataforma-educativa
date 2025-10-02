/**
 * perfil.js
 * Controla la interacción del perfil de usuario:
 * - Tabs
 * - Cropper (recorte y subida de imagen)
 * - Paises y estados
 * - Alertas de guardado
 * - Cambio de contraseña
 */

document.addEventListener("DOMContentLoaded", () => {
  const baseUrl =
    document.querySelector('meta[name="base-url"]')?.content ||
    window.location.origin;

  // Obtener rol actual del usuario
  const userRole =
    document.querySelector('meta[name="rol-usuario"]')?.content || "";

  /* =========================================================
   🧭 1. Navegación entre pestañas (Tabs)
  ========================================================= */
  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      document
        .querySelectorAll(".tab-btn")
        .forEach((b) => b.classList.remove("active"));
      document
        .querySelectorAll(".tab-content")
        .forEach((c) => c.classList.remove("active"));
      btn.classList.add("active");
      document.getElementById(btn.dataset.tab)?.classList.add("active");
    });
  });

  /* =========================================================
   🌍 2. Cargar países y estados
  ========================================================= */
  const paisSelect = document.querySelector('select[name="pais_origen"]');
  const estadoSelect = document.querySelector('input[name="estado"]');

  if (paisSelect) {
    const paisSeleccionado = paisSelect.dataset.value || ""; // 🟠 NUEVO
    const paises = [
      "México",
      "Estados Unidos",
      "Canadá",
      "Argentina",
      "Colombia",
      "España",
      "Brasil",
      "Chile",
      "Perú",
      "Uruguay",
      "Venezuela",
      "Paraguay",
      "Bolivia",
      "Guatemala",
      "Ecuador",
      "Costa Rica",
      "Panamá",
    ];

    paisSelect.innerHTML = `<option value="">Seleccione un país</option>`;
    paises.forEach((p) => {
      const opt = document.createElement("option");
      opt.value = p;
      opt.textContent = p;
      if (p === paisSeleccionado) opt.selected = true; // ✅ Selección automática
      paisSelect.appendChild(opt);
    });
  }

  /* =========================================================
   🎓  Ocultar pestaña "Formación" si el rol no es profesor
  ========================================================= */
  if (!["profesor", "superusuario", "escolares"].includes(userRole)) {
    const tabBtn = document.querySelector('.tab-btn[data-tab="tab5"]');
    const tabContent = document.getElementById("tab5");
    if (tabBtn) tabBtn.remove();
    if (tabContent) tabContent.remove();
  }

  /* =========================================================
   📸 3. Subir y recortar imagen de perfil
  ========================================================= */
  const fileInput = document.getElementById("foto");
  const preview = document.getElementById("profileImage");
  const btnLabel = document.querySelector(".btn-foto");

  if (fileInput && preview) {
    fileInput.addEventListener("change", (event) => {
      const file = event.target.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = (e) => {
        Swal.fire({
          title: "Recorta tu imagen",
          html: `
            <div style="width:100%;max-width:300px;margin:auto;">
              <img id="cropImage" src="${e.target.result}" style="width:100%;border-radius:10px;"/>
            </div>`,
          showCancelButton: true,
          confirmButtonText: "Subir a Cloudinary",
          didOpen: () => {
            const image = document.getElementById("cropImage");
            const cropper = new Cropper(image, {
              aspectRatio: 1,
              viewMode: 1,
              dragMode: "move",
              background: false,
              autoCropArea: 1,
            });

            Swal.getConfirmButton().addEventListener("click", async () => {
              const canvas = cropper.getCroppedCanvas({
                width: 300,
                height: 300,
              });

              btnLabel.innerHTML =
                '<i class="fa fa-spinner fa-spin"></i> Subiendo...';
              btnLabel.classList.add("disabled");
              preview.style.filter = "blur(2px)";
              preview.style.opacity = "0.6";

              canvas.toBlob(async (blob) => {
                const formData = new FormData();
                formData.append("foto", blob, "perfil.jpg");

                Swal.fire({
                  title: "Subiendo foto...",
                  text: "Por favor espera unos segundos",
                  allowOutsideClick: false,
                  didOpen: () => Swal.showLoading(),
                });

                try {
                  const response = await fetch(
                    `${baseUrl}/perfil/subirFotoCloud`,
                    {
                      method: "POST",
                      body: formData,
                    }
                  );

                  const result = await response.json();
                  Swal.close();

                  btnLabel.innerHTML = "Cambiar Foto";
                  btnLabel.classList.remove("disabled");

                  if (result.success) {
                    preview.style.transition = "opacity 0.4s ease";
                    preview.style.opacity = "0";
                    setTimeout(() => {
                      preview.src = result.url;
                      preview.style.filter = "none";
                      preview.style.opacity = "1";
                    }, 300);

                    Swal.fire(
                      "✅ Foto actualizada",
                      "Tu foto fue subida correctamente.",
                      "success"
                    );
                  } else {
                    resetPreview();
                    Swal.fire("⚠️ Error", result.message, "error");
                  }
                } catch (err) {
                  resetPreview();
                  Swal.fire(
                    "❌ Error inesperado",
                    "Hubo un problema al subir la imagen.",
                    "error"
                  );
                }
              }, "image/jpeg");

              function resetPreview() {
                preview.style.filter = "none";
                preview.style.opacity = "1";
                btnLabel.innerHTML = "Cambiar Foto";
                btnLabel.classList.remove("disabled");
              }
            });
          },
        });
      };
      reader.readAsDataURL(file);
    });
  }

  /* =========================================================
   💾 4. Guardar detalles personales (form principal)
  ========================================================= */
  const formInfo = document.querySelector(".form-info");

  if (formInfo) {
    formInfo.addEventListener("submit", async (e) => {
      e.preventDefault();

      // Determinar si es cambio de contraseña
      const isPasswordChange =
        e.submitter?.formAction?.includes("actualizarPassword");
      const formData = new FormData(formInfo);

      const url = isPasswordChange
        ? e.submitter.formAction
        : `${baseUrl}/perfil/guardarDetalles`;

      try {
        const res = await fetch(url, { method: "POST", body: formData });
        const data = await res.json().catch(() => null);

        Swal.fire({
          title: data?.success ? "✅ Éxito" : "Información guardada",
          text:
            data?.message ||
            (isPasswordChange
              ? "Contraseña actualizada correctamente."
              : "Tu información fue guardada con éxito."),
          icon: "success",
          confirmButtonText: "Aceptar",
        });
      } catch (error) {
        console.error(error);
        Swal.fire(
          "❌ Error",
          "Ocurrió un problema al guardar la información.",
          "error"
        );
      }
    });
  }
});
