// Soft Minimalism Login Form JavaScript
class SoftMinimalismLoginForm {
  constructor() {
    this.form = document.getElementById("loginForm");
    this.usuarioInput = document.getElementById("usuario");
    this.passwordInput = document.getElementById("password");
    this.passwordToggle = document.getElementById("passwordToggle");
    this.submitButton = this.form.querySelector(".comfort-button");

    this.init();
  }

  init() {
    this.bindEvents();
    this.setupPasswordToggle();
    this.setupGentleEffects();
  }

  bindEvents() {
    this.form.addEventListener("submit", (e) => this.handleSubmit(e));
    this.usuarioInput.addEventListener("blur", () => this.validateUsuario());
    this.passwordInput.addEventListener("blur", () => this.validatePassword());
    this.usuarioInput.addEventListener("input", () =>
      this.clearError("usuario")
    );
    this.passwordInput.addEventListener("input", () =>
      this.clearError("password")
    );

    // Label animations
    this.usuarioInput.setAttribute("placeholder", " ");
    this.passwordInput.setAttribute("placeholder", " ");
  }

  setupPasswordToggle() {
    this.passwordToggle.addEventListener("click", () => {
      const type = this.passwordInput.type === "password" ? "text" : "password";
      this.passwordInput.type = type;
      this.passwordToggle.classList.toggle("toggle-active", type === "text");
      this.triggerGentleRipple(this.passwordToggle);
    });
  }

  setupGentleEffects() {
    [this.usuarioInput, this.passwordInput].forEach((input) => {
      input.addEventListener("focus", (e) => {
        this.triggerSoftFocus(e.target.closest(".field-container"));
      });
      input.addEventListener("blur", (e) => {
        this.releaseSoftFocus(e.target.closest(".field-container"));
      });
    });

    const interactiveElements = document.querySelectorAll(
      ".comfort-button, .gentle-checkbox"
    );
    interactiveElements.forEach((element) => {
      element.addEventListener(
        "mousedown",
        () => (element.style.transform = "scale(0.98)")
      );
      element.addEventListener(
        "mouseup",
        () => (element.style.transform = "scale(1)")
      );
      element.addEventListener(
        "mouseleave",
        () => (element.style.transform = "scale(1)")
      );
    });
  }

  triggerSoftFocus(container) {
    container.style.transition = "all 0.3s ease";
    container.style.transform = "translateY(-1px)";
  }

  releaseSoftFocus(container) {
    container.style.transform = "translateY(0)";
  }

  triggerGentleRipple(element) {
    element.style.transform = "scale(0.95)";
    setTimeout(() => (element.style.transform = "scale(1)"), 150);
  }

  // ✅ Validación actualizada
  validateUsuario() {
    const usuario = this.usuarioInput.value.trim();

    if (!usuario) {
      this.showError(
        "usuario",
        "Por favor, ingresa tu matrícula o número de empleado."
      );
      return false;
    }

    this.clearError("usuario");
    return true;
  }

  validatePassword() {
    const password = this.passwordInput.value;

    if (!password) {
      this.showError("password", "Por favor, ingresa tu contraseña.");
      return false;
    }

    if (password.length < 6) {
      this.showError(
        "password",
        "La contraseña debe tener al menos 6 caracteres."
      );
      return false;
    }

    this.clearError("password");
    return true;
  }

  showError(field, message) {
    const fieldContainer = document
      .getElementById(field)
      .closest(".soft-field");
    let errorElement = document.getElementById(`${field}Error`);

    if (!errorElement) {
      errorElement = document.createElement("span");
      errorElement.id = `${field}Error`;
      errorElement.classList.add("gentle-error");
      fieldContainer.appendChild(errorElement);
    }

    fieldContainer.classList.add("error");
    errorElement.textContent = message;
    errorElement.classList.add("show");
  }

  clearError(field) {
    const fieldContainer = document
      .getElementById(field)
      .closest(".soft-field");
    const errorElement = document.getElementById(`${field}Error`);

    fieldContainer.classList.remove("error");
    if (errorElement) {
      errorElement.classList.remove("show");
      setTimeout(() => (errorElement.textContent = ""), 300);
    }
  }

  async handleSubmit(e) {
    e.preventDefault();

    const isUsuarioValid = this.validateUsuario();
    const isPasswordValid = this.validatePassword();

    if (!isUsuarioValid || !isPasswordValid) return;

    this.setLoading(true);

    try {
      // ✅ CAMBIO 1: usar la ruta correcta "auth/doLogin"
      const response = await fetch(`${baseUrl}auth/doLogin`, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        credentials: "same-origin",
        body: new URLSearchParams({
          usuario: this.usuarioInput.value.trim(),
          password: this.passwordInput.value.trim(),
        }),
      });

      // ✅ Si CodeIgniter redirige, seguir la redirección
      if (response.redirected) {
        window.location.href = response.url;
        return;
      }

      const text = await response.text();

      if (text.includes("error")) {
        this.showError(
          "password",
          "Credenciales incorrectas o cuenta inactiva."
        );
      } else {
        // ✅ CAMBIO 2: redirigir con baseUrl
        window.location.href = `${baseUrl}dashboard`;
      }
    } catch (err) {
      this.showError("password", "Ocurrió un error. Intenta nuevamente.");
    } finally {
      this.setLoading(false);
    }
  }

  setLoading(loading) {
    this.submitButton.classList.toggle("loading", loading);
    this.submitButton.disabled = loading;
  }
}

// Initialize form
document.addEventListener("DOMContentLoaded", () => {
  new SoftMinimalismLoginForm();
});
