document.addEventListener("DOMContentLoaded", () => {
  window.AsistenciasUI = {
    inicializar(asignacionId) {
      const tabla = document.querySelector(".tabla-asistencia");
      if (!tabla) return;

      // 🟢 Botón de estado
      tabla.addEventListener("click", (e) => {
        const btn = e.target.closest(".estado-btn");
        if (!btn) return;

        const fila = btn.closest("tr");
        // ✅ Selector dinámico para inputs con índices
        const estadoInput = fila.querySelector(
          "input[name^='asistencias'][name$='[estado]']"
        );
        const selectJust = fila.querySelector(".select-justificacion");

        if (!estadoInput) {
          console.warn(
            "⚠️ No se encontró el input de estado en esta fila",
            fila
          );
          return;
        }

        if (btn.classList.contains("asistencia")) {
          btn.className = "estado-btn falta";
          btn.textContent = "❌ Falta";
          estadoInput.value = "falta";
          selectJust.disabled = true;
          selectJust.value = "";
          mostrarAlerta(" Marcado como falta.", "warning");
        } else if (btn.classList.contains("falta")) {
          btn.className = "estado-btn justificada";
          btn.textContent = "⚪ Justificada";
          estadoInput.value = "justificada";
          selectJust.disabled = false;
          mostrarAlerta("Selecciona un motivo de justificación.", "info");
        } else {
          btn.className = "estado-btn asistencia";
          btn.textContent = "✅ Asistencia";
          estadoInput.value = "asistencia";
          selectJust.disabled = true;
          selectJust.value = "";
          mostrarAlerta(" Marcado como asistencia.", "success");
        }
      });

      // 📅 Cambio de fecha o historial
      const inputFecha = document.getElementById("fechaAsistencia");
      const selectHistorial = document.getElementById("selectHistorial");
      [inputFecha, selectHistorial].forEach((el) => {
        el?.addEventListener("change", async (e) => {
          const nuevaFecha = e.target.value;
          if (!nuevaFecha) return;

          const contenedor = document.getElementById("asistenciaInner");
          contenedor.innerHTML = `<p class="cargando"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>`;

          const res = await fetch(
            `${base_url}profesor/grupos/asistencias/${asignacionId}?fecha=${nuevaFecha}`
          );
          const html = await res.text();

          const tempDiv = document.createElement("div");
          tempDiv.innerHTML = html;
          const nuevoContenido = tempDiv.querySelector("#asistenciaInner");

          if (nuevoContenido) {
            contenedor.innerHTML = nuevoContenido.innerHTML;
            window.AsistenciasUI.inicializar(asignacionId);
          }
        });
      });

      // 🕒 Cambio de frecuencia
      const selectFrecuencia = document.getElementById("selectFrecuencia");
      if (selectFrecuencia) {
        selectFrecuencia.addEventListener("change", async () => {
          const frecuenciaSeleccionada = selectFrecuencia.value;
          const hidden = document.getElementById("frecuenciaSeleccionada");
          if (hidden) hidden.value = frecuenciaSeleccionada;

          const fecha = document.getElementById("fechaAsistencia").value;
          const contenedor = document.getElementById("asistenciaInner");
          contenedor.innerHTML = `<p class="cargando"><i class="fas fa-spinner fa-spin"></i> Cargando frecuencia ${frecuenciaSeleccionada}...</p>`;

          const res = await fetch(
            `${base_url}profesor/grupos/asistencias/${asignacionId}?fecha=${fecha}&frecuencia=${frecuenciaSeleccionada}`
          );
          const html = await res.text();

          const tempDiv = document.createElement("div");
          tempDiv.innerHTML = html;
          const nuevoContenido = tempDiv.querySelector("#asistenciaInner");

          if (nuevoContenido) {
            contenedor.innerHTML = nuevoContenido.innerHTML;
            mostrarAlerta(
              ` Mostrando ${
                selectFrecuencia.options[selectFrecuencia.selectedIndex].text
              }`,
              "info"
            );
            window.AsistenciasUI.inicializar(asignacionId);
          }
        });
      }
    },
  };

  // 💾 Manejador global para el formulario
  document.addEventListener("submit", async (e) => {
    if (e.target.id !== "formAsistencia") return;
    e.preventDefault();

    const form = e.target;
    const asignacionId = form.dataset.asignacion ?? window.asignacionId;
    const formData = new FormData(form);
    const btnGuardar = form.querySelector(".btn-main");

    // 🕓 Desactivar mientras guarda
    const iconoOriginal = btnGuardar.innerHTML;
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Guardando...`;

    console.log("✅ Submit detectado correctamente", asignacionId);

    try {
      const res = await fetch(
        `${base_url}profesor/grupos/guardar-asistencias/${asignacionId}`,
        {
          method: "POST",
          body: formData,
        }
      );

      const data = await res.json();
      if (data.success) {
        mostrarAlerta(
          data.mensaje || "Asistencias guardadas correctamente.",
          "success"
        );
        btnGuardar.innerHTML = `<i class="fas fa-check-circle"></i> Guardado`;

        // 🆕 Agregar la fecha al historial si no existía
        const selectHistorial = document.getElementById("selectHistorial");
        if (data.nuevaFecha && selectHistorial) {
          const existe = Array.from(selectHistorial.options).some(
            (opt) => opt.value === data.nuevaFecha
          );
          if (!existe) {
            const opcion = document.createElement("option");
            opcion.value = data.nuevaFecha;
            opcion.textContent = new Date(data.nuevaFecha).toLocaleDateString(
              "es-MX"
            );
            selectHistorial.insertBefore(opcion, selectHistorial.firstChild);

            // 💡 Mostrar alerta adicional indicando que el historial se actualizó
            mostrarAlerta("Nueva fecha agregada al historial.", "info");
          }
        }
      } else {
        mostrarAlerta(
          data.error || "No se pudo guardar la asistencia.",
          "error"
        );
        btnGuardar.innerHTML = `<i class="fas fa-times-circle"></i> Error`;
      }
    } catch (err) {
      mostrarAlerta("⚠️ Error en la conexión con el servidor.", "error");
      btnGuardar.innerHTML = `<i class="fas fa-times-circle"></i> Error`;
    } finally {
      // ✅ Restaurar el botón
      setTimeout(() => {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = iconoOriginal;
      }, 1500);
    }
  });

  // 🔒 Mantener frecuencia seleccionada
  const selectFrecuencia = document.getElementById("selectFrecuencia");
  if (selectFrecuencia) {
    selectFrecuencia.addEventListener("change", () => {
      const hidden = document.getElementById("frecuenciaSeleccionada");
      if (hidden) hidden.value = selectFrecuencia.value;
    });
  }

  // 🚀 Inicializar al cargar la vista
  window.AsistenciasUI.inicializar(window.asignacionId);
});
