// === Menú responsive ===
const menuToggle = document.getElementById("menuToggle");
const navLinks = document.querySelector(".nav-links");

if (menuToggle && navLinks) {
  menuToggle.addEventListener("click", () => {
    navLinks.classList.toggle("active");
    menuToggle.classList.toggle("active");
  });
}

// === Modo oscuro ===
const themeToggle = document.getElementById("themeToggle");

if (themeToggle) {
  const themeIcon = themeToggle.querySelector("i");

  themeToggle.addEventListener("click", () => {
    document.body.classList.toggle("dark-mode");

    if (document.body.classList.contains("dark-mode")) {
      themeIcon.classList.remove("fa-moon");
      themeIcon.classList.add("fa-sun");
    } else {
      themeIcon.classList.remove("fa-sun");
      themeIcon.classList.add("fa-moon");
    }

    localStorage.setItem(
      "theme",
      document.body.classList.contains("dark-mode") ? "dark" : "light"
    );
  });

  if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-mode");
    themeIcon.classList.remove("fa-moon");
    themeIcon.classList.add("fa-sun");
  }
}

// === Navbar scroll efecto ===
const navbar = document.querySelector(".navbar");
if (navbar) {
  window.addEventListener("scroll", () => {
    if (window.scrollY > 50) {
      navbar.style.padding = "10px 0";
      navbar.style.boxShadow = "0 5px 20px rgba(0, 0, 0, 0.1)";
    } else {
      navbar.style.padding = "15px 0";
      navbar.style.boxShadow = "var(--shadow)";
    }
  });
}

// === Sección dinámica de escuelas ===
const schoolSelect = document.getElementById("schoolSelect");

if (schoolSelect) {
  const schoolName = document.getElementById("schoolName");
  const heroBadge = document.getElementById("heroBadge");
  const heroTitle = document.getElementById("heroTitle");
  const heroDescription = document.getElementById("heroDescription");

  const schoolData = {
    utsc: {
      name: "UTSC",
      badge: "Abiertas inscripciones 2025",
      title: "Formación de calidad para el futuro",
      description:
        "Descubre cómo nuestra institución puede transformar tu futuro con programas académicos de vanguardia y una comunidad de aprendizaje excepcional. Únete a más de 5,000 estudiantes que han elegido UTSC para su formación profesional.",
    },
    engineering: {
      name: "Ingeniería",
      badge: "Nuevos laboratorios disponibles",
      title: "Ingeniería de vanguardia",
      description:
        "Programas de ingeniería con tecnología de punta y enfoque práctico para formar a los profesionales del mañana. Especialidades en inteligencia artificial, robótica y nanotecnología.",
    },
    business: {
      name: "Negocios",
      badge: "Programas internacionales",
      title: "Liderazgo empresarial",
      description:
        "Formación en negocios con enfoque global y oportunidades de intercambio con las mejores universidades del mundo. Desarrolla habilidades directivas y emprendedoras.",
    },
    arts: {
      name: "Artes",
      badge: "Exposición estudiantil abierta",
      title: "Expresión creativa sin límites",
      description:
        "Desarrolla tu talento artístico en un ambiente que fomenta la creatividad y la innovación en todas las disciplinas. Desde diseño digital hasta artes escénicas.",
    },
    sciences: {
      name: "Ciencias",
      badge: "Investigación de avanzada",
      title: "Descubre el mundo científico",
      description:
        "Programas de ciencia básica y aplicada con oportunidades de participar en proyectos de investigación de impacto global. Laboratorios equipados con tecnología de punta.",
    },
  };

  schoolSelect.addEventListener("change", () => {
    const selectedSchool = schoolSelect.value;
    const data = schoolData[selectedSchool];
    if (!data) return;

    schoolName.textContent = data.name;
    heroBadge.textContent = data.badge;
    heroTitle.textContent = data.title;
    heroDescription.textContent = data.description;
  });
}

// === CRUD de estudiantes (solo si existe el formulario) ===
const studentForm = document.getElementById("studentForm");

if (studentForm) {
  const studentsList = document.getElementById("studentsList");
  let students = JSON.parse(localStorage.getItem("students")) || [];
  let editingStudentId = null;

  function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
  }

  function renderStudents() {
    studentsList.innerHTML = "";

    if (students.length === 0) {
      studentsList.innerHTML =
        '<tr><td colspan="5" style="text-align: center;">No hay alumnos registrados</td></tr>';
      return;
    }

    students.forEach((student) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${student.nombre} ${student.apellidoPaterno} ${
        student.apellidoMaterno
      }</td>
        <td>${student.email}</td>
        <td>${student.telefono}</td>
        <td>${getCareerName(student.carrera)}</td>
        <td>
          <div class="action-buttons">
            <button class="action-btn edit-btn" data-id="${student.id}">
              <i class="fas fa-edit"></i>
            </button>
            <button class="action-btn delete-btn" data-id="${student.id}">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>`;
      studentsList.appendChild(tr);
    });

    document.querySelectorAll(".edit-btn").forEach((btn) => {
      btn.addEventListener("click", (e) =>
        editStudent(e.currentTarget.dataset.id)
      );
    });

    document.querySelectorAll(".delete-btn").forEach((btn) => {
      btn.addEventListener("click", (e) =>
        deleteStudent(e.currentTarget.dataset.id)
      );
    });
  }

  function getCareerName(value) {
    const careers = {
      sistemas: "Ingeniería en Sistemas",
      administracion: "Administración de Empresas",
      diseno: "Diseño Gráfico Digital",
      industrial: "Ingeniería Industrial",
      mecatronica: "Ingeniería Mecatrónica",
      contabilidad: "Contabilidad",
    };
    return careers[value] || value;
  }

  function saveStudent(studentData) {
    if (editingStudentId) {
      const index = students.findIndex((s) => s.id === editingStudentId);
      if (index !== -1)
        students[index] = { ...studentData, id: editingStudentId };
      editingStudentId = null;
    } else {
      students.push({ ...studentData, id: generateId() });
    }

    localStorage.setItem("students", JSON.stringify(students));
    renderStudents();
    studentForm.reset();
  }

  function editStudent(id) {
    const student = students.find((s) => s.id === id);
    if (!student) return;

    for (const [key, value] of Object.entries(student)) {
      const input = document.getElementById(key);
      if (input) input.value = value;
    }

    editingStudentId = id;
    document
      .getElementById("student-crud")
      ?.scrollIntoView({ behavior: "smooth" });
  }

  function deleteStudent(id) {
    if (confirm("¿Estás seguro de que deseas eliminar este alumno?")) {
      students = students.filter((s) => s.id !== id);
      localStorage.setItem("students", JSON.stringify(students));
      renderStudents();
    }
  }

  studentForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(studentForm);
    const studentData = Object.fromEntries(formData.entries());
    saveStudent(studentData);

    alert(
      editingStudentId
        ? "Alumno actualizado correctamente"
        : "Alumno registrado correctamente"
    );
  });

  renderStudents();
}
