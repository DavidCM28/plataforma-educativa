document.addEventListener("DOMContentLoaded", () => {
  /* ==========================================================
     1️⃣ Seleccionar Correcta / Parcial / Incorrecta
     ========================================================== */
  document.querySelectorAll(".btn-grade").forEach((btn) => {
    btn.addEventListener("click", () => {
      const detalleId = btn.dataset.id;
      const puntos = parseFloat(btn.dataset.pts);

      const grupo = btn.closest(".cal-buttons");
      grupo
        .querySelectorAll(".btn-grade")
        .forEach((b) => b.classList.remove("activo"));

      btn.classList.add("activo");

      const obs = document.querySelector(`textarea[data-id='${detalleId}']`);
      obs.dataset.puntos = puntos;

      actualizarTotal();
    });
  });

  /* ==========================================================
     2️⃣ Guardar TODAS las preguntas en un solo request
     ========================================================== */
  document.getElementById("btnGuardarTodo").addEventListener("click", () => {
    mostrarConfirmacion(
      "Guardar calificación",
      "Se guardarán todas las calificaciones y observaciones.",
      guardarTodo
    );
  });

  async function guardarTodo() {
    const detalles = [];

    document.querySelectorAll(".obs").forEach((obs) => {
      const id = obs.dataset.id;
      const puntos = obs.dataset.puntos ?? 0;
      const observacion = obs.value;

      detalles.push({
        id,
        puntos,
        observacion,
      });
    });

    const fd = new FormData();
    fd.append("detalles", JSON.stringify(detalles));

    try {
      const res = await fetch(
        `${window.base}profesor/grupos/examenes/calificar-detalle-multiple`,
        {
          method: "POST",
          body: fd,
        }
      );

      const data = await res.json();

      if (data.success) {
        mostrarAlerta("Calificación guardada correctamente", "success");
        document.getElementById("totalPts").innerText = parseFloat(
          data.total
        ).toFixed(2);
      } else {
        mostrarAlerta("Error al guardar", "error");
      }
    } catch (err) {
      console.error(err);
      mostrarAlerta("Error de red o servidor", "error");
    }
  }

  /* ==========================================================
     3️⃣ Actualizar total dinámico
     ========================================================== */
  function actualizarTotal() {
    let total = 0;

    document.querySelectorAll(".obs").forEach((obs) => {
      total += parseFloat(obs.dataset.puntos ?? 0);
    });

    document.getElementById("totalPts").innerText = total.toFixed(2);
  }
});

document.getElementById("btnVolverRespuestas").addEventListener("click", () => {
  window.location.href = `${window.base}profesor/grupos/examenes/respuestas/${window.examenId}`;
});
