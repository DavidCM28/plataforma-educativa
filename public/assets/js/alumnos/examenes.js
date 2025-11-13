/* ============================================================
   📘 MÓDULO DE EXÁMENES — ALUMNO
   ============================================================ */
window.ExamenesAlumnoUI = {
  asignacionId: null,

  inicializar(asignacionId) {
    this.asignacionId = asignacionId;
    console.log(
      "✅ Módulo de exámenes inicializado para asignación:",
      asignacionId
    );

    // 🎯 Listener para los botones "Comenzar examen"
    document.querySelectorAll(".iniciar-examen").forEach((btn) => {
      btn.addEventListener("click", () => {
        const id = btn.dataset.id;

        // 🚀 Redirigir al modo examen completo
        window.location.href = `${window.base_url}alumno/examenes/resolver/${id}`;
      });
    });
  },
};
