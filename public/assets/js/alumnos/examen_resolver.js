document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formExamen");
  const examenId = form.dataset.id;
  const tiempoExamen = parseInt(form.dataset.minutos || 0);
  let respuestasCache = {};
  let advertencias = parseInt(
    localStorage.getItem(`examen_${examenId}_advertencias`) || 0
  );
  let guardarTimeout = null;
  let ultimaGuardada = 0;
  let saliendo = false;
  let limite_advertencias = 50;

  /* ============================================================
     🚫 ANTITRAMPAS — SOLO ADVERTENCIAS REALES
  ============================================================ */
  function registrarAdvertencia(motivo) {
    advertencias++;
    localStorage.setItem(`examen_${examenId}_advertencias`, advertencias);
    mostrarAlerta(
      `⚠️ Advertencia ${advertencias}/${limite_advertencias}: ${motivo}`,
      "warning"
    );

    if (advertencias >= limite_advertencias) {
      mostrarAlerta("🚨 Examen bloqueado por múltiples advertencias.", "error");
      finalizarExamen(true);
    }
  }

  // 🚫 Combinaciones sospechosas (copiar, inspeccionar, etc.)
  document.addEventListener("keydown", (e) => {
    const key = e.key.toLowerCase();

    if (
      (e.ctrlKey && ["c", "x", "v", "a", "s", "u"].includes(key)) ||
      e.key === "F12" ||
      (e.ctrlKey && e.shiftKey && key === "i")
    ) {
      e.preventDefault();
      registrarAdvertencia("Uso de comandos prohibidos (copiar/inspeccionar).");
    }

    if (e.altKey && key === "tab") {
      e.preventDefault();
      registrarAdvertencia("Cambio de ventana detectado (Alt+Tab).");
    }
  });

  // 🔒 Detectar cambio de pestaña o ventana
  window.addEventListener("blur", () => {
    if (!saliendo) registrarAdvertencia("Cambio de pestaña o ventana.");
  });

  // ❌ Deshabilitar clic derecho
  //document.addEventListener("contextmenu", (e) => e.preventDefault());

  // ⚠️ Evitar cierre accidental
  window.addEventListener("beforeunload", (e) => {
    saliendo = true;
    e.preventDefault();
    e.returnValue = "¿Seguro que deseas salir del examen?";
  });

  /* ============================================================
     💾 AUTOGUARDADO INTELIGENTE (con control de frecuencia)
  ============================================================ */
  function recolectarRespuestas() {
    const data = {};
    form.querySelectorAll("input, textarea").forEach((el) => {
      if (el.type === "radio" && el.checked) data[el.name] = el.value;
      else if (el.tagName === "TEXTAREA" || el.type === "text")
        data[el.name] = el.value.trim();
    });
    return data;
  }

  async function guardarRespuestas(auto = false) {
    const ahora = Date.now();
    if (auto && ahora - ultimaGuardada < 3000) return; // no más de 1 cada 3s
    ultimaGuardada = ahora;

    const respuestas = recolectarRespuestas();
    if (JSON.stringify(respuestas) === JSON.stringify(respuestasCache)) return;
    respuestasCache = respuestas;

    try {
      const res = await fetch(
        `${window.base_url}alumno/examenes/guardar-respuestas/${examenId}`,
        {
          method: "POST",
          body: new FormData(form),
          headers: { "X-Requested-With": "XMLHttpRequest" },
          credentials: "include", // 🔥 esto envía las cookies de sesión PHP
          redirect: "manual",
        }
      );

      if (res.ok && auto) mostrarAlerta("💾 Cambios guardados.", "success");
      if (!res.ok) mostrarAlerta("⚠️ Error al guardar (conexión).", "warning");
    } catch (err) {
      console.error(err);
      mostrarAlerta("⚠️ Error de red. Reintentando...", "error");
    }
  }

  form.addEventListener("input", () => {
    clearTimeout(guardarTimeout);
    guardarTimeout = setTimeout(() => guardarRespuestas(true), 2000);
  });

  form.addEventListener("change", () => {
    clearTimeout(guardarTimeout);
    guardarTimeout = setTimeout(() => guardarRespuestas(true), 500);
  });

  /* ============================================================
     🕒 TEMPORIZADOR FLOTANTE PERSISTENTE + ADVERTENCIAS EN VIVO
  ============================================================ */
  if (tiempoExamen > 0) {
    const timerEl = document.createElement("div");
    timerEl.className = "temporizador-flotante";
    document.body.appendChild(timerEl);

    const storageKey = `examen_${examenId}_tiempo`;
    const ahora = Date.now();
    let segundosTotales = tiempoExamen * 60;
    let tiempoRestante = segundosTotales;

    const dataGuardada = localStorage.getItem(storageKey);
    if (dataGuardada) {
      const { inicio, restante } = JSON.parse(dataGuardada);
      const transcurrido = Math.floor((ahora - inicio) / 1000);
      tiempoRestante = Math.max(restante - transcurrido, 0);
    } else {
      localStorage.setItem(
        storageKey,
        JSON.stringify({ inicio: ahora, restante: segundosTotales })
      );
    }

    function actualizarTimer() {
      const min = Math.floor(tiempoRestante / 60);
      const sec = tiempoRestante % 60;
      timerEl.textContent = `⏱️ ${min}:${sec
        .toString()
        .padStart(2, "0")} | ⚠️ ${advertencias}/${limite_advertencias}`;
      timerEl.style.color =
        tiempoRestante <= 300
          ? "#ff6b6b"
          : advertencias > 0
          ? "#ffae42"
          : "#fff";
    }

    actualizarTimer();

    const interval = setInterval(() => {
      tiempoRestante--;
      actualizarTimer();

      if (tiempoRestante % 15 === 0) {
        localStorage.setItem(
          storageKey,
          JSON.stringify({ inicio: Date.now(), restante: tiempoRestante })
        );
      }

      if (tiempoRestante <= 0) {
        clearInterval(interval);
        localStorage.removeItem(storageKey);
        mostrarAlerta("⏰ Tiempo finalizado. Enviando examen...", "info");
        finalizarExamen();
      }
    }, 1000);

    form.addEventListener("submit", () => {
      clearInterval(interval);
      localStorage.removeItem(storageKey);
    });

    const originalRegistrarAdvertencia = registrarAdvertencia;
    registrarAdvertencia = function (motivo) {
      originalRegistrarAdvertencia(motivo);
      actualizarTimer();
    };
  }

  /* ============================================================
     🚀 FINALIZAR EXAMEN (manual o forzado)
  ============================================================ */
  async function enviarExamenFinal() {
    try {
      await guardarRespuestas();
      // (Opcional) registrar cierre en backend
      await fetch(
        `${window.base_url}alumno/examenes/finalizar-examen/${examenId}`,
        {
          method: "POST",
          headers: { "X-Requested-With": "XMLHttpRequest" },
        }
      );
    } catch (err) {
      console.warn("Error al finalizar examen:", err);
    }

    localStorage.removeItem(`examen_${examenId}_tiempo`);
    localStorage.removeItem(`examen_${examenId}_advertencias`);
    mostrarAlerta("📨 Enviando examen...", "info");
    setTimeout(
      () => (window.location.href = `${window.base_url}alumno/dashboard`),
      2000
    );
  }

  async function finalizarExamen(forzado = false) {
    await guardarRespuestas(true);
    if (forzado)
      mostrarAlerta(
        "🚫 El examen se ha cerrado automáticamente por exceso de advertencias.",
        "error"
      );
    setTimeout(() => enviarExamenFinal(), 2000);
  }

  /* ============================================================
     ✉️ ENVÍO MANUAL (botón)
  ============================================================ */
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    await enviarExamenFinal();
  });

  /* ============================================================
     🔄 BOTÓN DE PRUEBA (reinicio de datos)
  ============================================================ */
  const resetBtn = document.getElementById("resetPrueba");
  if (resetBtn) {
    resetBtn.addEventListener("click", () => {
      localStorage.removeItem(`examen_${examenId}_tiempo`);
      localStorage.removeItem(`examen_${examenId}_advertencias`);
      advertencias = 0;
      mostrarAlerta("🧹 Contador y advertencias reiniciados.", "success");
      setTimeout(() => location.reload(), 1000);
    });
  }

  // Guardar el estado inicial para evitar un primer guardado redundante
  respuestasCache = recolectarRespuestas();
});
