const menuToggle = document.getElementById("menuToggle");
const navLinks = document.querySelector(".nav-links");

menuToggle.addEventListener("click", () => {
  navLinks.classList.toggle("active");
  menuToggle.classList.toggle("active");
});

const themeToggle = document.getElementById("themeToggle");
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

window.addEventListener("scroll", () => {
  const navbar = document.querySelector(".navbar");
  if (window.scrollY > 50) {
    navbar.style.padding = "10px 0";
    navbar.style.boxShadow = "0 5px 20px rgba(0, 0, 0, 0.1)";
  } else {
    navbar.style.padding = "15px 0";
    navbar.style.boxShadow = "var(--shadow)";
  }
});

const schoolSelect = document.getElementById("schoolSelect");
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

  schoolName.textContent = data.name;
  heroBadge.textContent = data.badge;
  heroTitle.textContent = data.title;
  heroDescription.textContent = data.description;
});

const studentForm = document.getElementById("studentForm");
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
                            <button class="action-btn edit-btn" data-id="${
                              student.id
                            }">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" data-id="${
                              student.id
                            }">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
    studentsList.appendChild(tr);
  });

  document.querySelectorAll(".edit-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const id = e.currentTarget.getAttribute("data-id");
      editStudent(id);
    });
  });

  document.querySelectorAll(".delete-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const id = e.currentTarget.getAttribute("data-id");
      deleteStudent(id);
    });
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
    if (index !== -1) {
      students[index] = { ...studentData, id: editingStudentId };
    }
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
  if (student) {
    document.getElementById("nombre").value = student.nombre;
    document.getElementById("apellidoPaterno").value = student.apellidoPaterno;
    document.getElementById("apellidoMaterno").value = student.apellidoMaterno;
    document.getElementById("fechaNacimiento").value = student.fechaNacimiento;
    document.getElementById("genero").value = student.genero;
    document.getElementById("email").value = student.email;
    document.getElementById("telefono").value = student.telefono;
    document.getElementById("carrera").value = student.carrera;
    document.getElementById("direccion").value = student.direccion;
    document.getElementById("comentarios").value = student.comentarios || "";

    editingStudentId = id;

    document
      .getElementById("student-crud")
      .scrollIntoView({ behavior: "smooth" });
  }
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
  const studentData = {
    nombre: formData.get("nombre"),
    apellidoPaterno: formData.get("apellidoPaterno"),
    apellidoMaterno: formData.get("apellidoMaterno"),
    fechaNacimiento: formData.get("fechaNacimiento"),
    genero: formData.get("genero"),
    email: formData.get("email"),
    telefono: formData.get("telefono"),
    carrera: formData.get("carrera"),
    direccion: formData.get("direccion"),
    comentarios: formData.get("comentarios"),
  };

  saveStudent(studentData);

  alert(
    editingStudentId
      ? "Alumno actualizado correctamente"
      : "Alumno registrado correctamente"
  );
});

renderStudents();
