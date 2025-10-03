document.addEventListener("DOMContentLoaded", () => {
  const baseUrl =
    document.querySelector('meta[name="base-url"]')?.content ||
    window.location.origin;

  const inputBuscar = document.getElementById("inputBuscarUsuario");
  const listaSugerencias = document.getElementById("listaSugerencias");
  const contenedor = document.getElementById("formularioDetalles");

  let timer;

  // 🧠 Detectar escritura con retardo
  inputBuscar.addEventListener("input", () => {
    clearTimeout(timer);
    const query = inputBuscar.value.trim();

    if (query.length < 2) {
      listaSugerencias.innerHTML = "";
      return;
    }

    timer = setTimeout(() => buscarUsuarios(query), 300);
  });

  /**
   * 🔍 Busca usuarios según el texto ingresado
   */
  async function buscarUsuarios(q) {
    try {
      const { data } = await axios.get(`${baseUrl}/usuarios-detalles/buscar`, {
        params: { q },
      });

      const results = data.results || [];

      if (!results.length) {
        listaSugerencias.innerHTML = `<li><small>Sin resultados</small></li>`;
        return;
      }

      listaSugerencias.innerHTML = results
        .map(
          (u) => `
      <li data-id="${u.id}" data-rol="${u.rol}">
        ${u.nombre} <small>– ${u.rol}</small>
      </li>`
        )
        .join("");

      document
        .querySelectorAll("#listaSugerencias li[data-id]")
        .forEach((li) => {
          li.addEventListener("click", async () => {
            const id = li.dataset.id;
            inputBuscar.value = li.textContent.trim();
            listaSugerencias.innerHTML = "";
            await cargarFormulario(id);
          });
        });
    } catch {
      listaSugerencias.innerHTML = `<li><small>Error al buscar usuarios</small></li>`;
    }
  }

  /**
   * 📋 Cargar formulario del usuario seleccionado
   */
  async function cargarFormulario(id) {
    contenedor.innerHTML = `<p>Cargando información...</p>`;
    try {
      const url = `${baseUrl}/admin/usuarios-detalles/ver/${id}`;
      const { data } = await axios.get(url);

      const u = data.usuario;
      const d = data.detalles || {};

      if (!u) throw new Error("No se encontró el usuario en la respuesta");

      contenedor.innerHTML = generarFormulario(u, d);
      registrarEventoFormulario(baseUrl);
      cargarPaisesYEstados(d.pais_origen || "", d.estado || "");

      // 🔒 Ocultar formación académica si el rol no es Profesor
      const rol = u.rol?.toLowerCase();
      if (rol !== "profesor" && rol !== "superusuario" && rol !== "escolares") {
        const formacion = document.querySelector(".form-section.formacion");
        if (formacion) formacion.remove();
      }
    } catch (error) {
      console.error("🚨 Error al cargar los datos:", error.response || error);
      contenedor.innerHTML = `<p style="color:red;">Error al cargar los datos del usuario.</p>`;
    }
  }

  // 🧹 Cerrar lista de sugerencias al hacer clic fuera
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".form-group")) listaSugerencias.innerHTML = "";
  });

  // UX: seleccionar texto al enfocar
  inputBuscar.addEventListener("focus", () => inputBuscar.select());
});

/**
 * 🧱 Generar formulario
 */
function generarFormulario(u, d) {
  return `
  <form id="formDetalles">
    <input type="hidden" name="usuario_id" value="${u.id}">

    <!-- 🧍 Datos personales -->
    <section class="form-section">
      <h3>Datos personales</h3>
      <div class="form-grid">
        <div class="form-group">
          <label>Sexo:</label>
          <select name="sexo">
            <option value="">Seleccione</option>
            <option value="Masculino" ${
              d.sexo === "Masculino" ? "selected" : ""
            }>Masculino</option>
            <option value="Femenino" ${
              d.sexo === "Femenino" ? "selected" : ""
            }>Femenino</option>
          </select>
        </div>

        <div class="form-group">
          <label>Fecha de nacimiento:</label>
          <input type="date" name="fecha_nacimiento" value="${
            d.fecha_nacimiento || ""
          }">
        </div>

        <div class="form-group">
          <label>Estado civil:</label>
          <select name="estado_civil">
            <option value="">Seleccione</option>
            ${[
              "Soltero(a)",
              "Casado(a)",
              "Unión libre",
              "Divorciado(a)",
              "Viudo(a)",
            ]
              .map(
                (ec) =>
                  `<option value="${ec}" ${
                    d.estado_civil === ec ? "selected" : ""
                  }>${ec}</option>`
              )
              .join("")}
          </select>
        </div>

        <div class="form-group">
          <label>CURP:</label>
          <input type="text" name="curp" maxlength="18" value="${d.curp || ""}">
        </div>

        <div class="form-group">
          <label>RFC:</label>
          <input type="text" name="rfc" maxlength="13" value="${d.rfc || ""}">
        </div>

        <div class="form-group">
          <label>País de origen:</label>
          <select name="pais_origen" id="selectPais"></select>
        </div>
      </div>
    </section>

    <!-- ❤️ Datos médicos -->
    <section class="form-section">
      <h3>Datos médicos</h3>
      <div class="form-grid">
        <div class="form-group"><label>Peso (kg):</label><input type="number" step="0.01" name="peso" value="${
          d.peso || ""
        }"></div>
        <div class="form-group"><label>Estatura (m):</label><input type="number" step="0.01" name="estatura" value="${
          d.estatura || ""
        }"></div>
        <div class="form-group">
  <label>Tipo de sangre:</label>
  <select name="tipo_sangre">
    <option value="">Seleccione</option>
    ${["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"]
      .map(
        (tipo) =>
          `<option value="${tipo}" ${
            d.tipo_sangre === tipo ? "selected" : ""
          }>${tipo}</option>`
      )
      .join("")}
  </select>
</div>

      </div>

      <div class="checkbox-group">
  <label>
    <input type="checkbox" name="antecedente_diabetico" ${
      d.antecedente_diabetico == 1 ? "checked" : ""
    }> Familiar diabético
  </label>
  <label>
    <input type="checkbox" name="antecedente_hipertenso" ${
      d.antecedente_hipertenso == 1 ? "checked" : ""
    }> Familiar hipertenso
  </label>
  <label>
    <input type="checkbox" name="antecedente_cardiaco" ${
      d.antecedente_cardiaco == 1 ? "checked" : ""
    }> Familiar cardiaco
  </label>
</div>

    </section>

    <!-- 🏠 Domicilio -->
    <section class="form-section">
      <h3>Domicilio</h3>
      <div class="form-grid">
        <div class="form-group"><label>Estado:</label><select name="estado" id="selectEstado"></select></div>
        <div class="form-group"><label>Municipio:</label><input type="text" name="municipio" value="${
          d.municipio || ""
        }"></div>
        <div class="form-group"><label>Colonia:</label><input type="text" name="colonia" value="${
          d.colonia || ""
        }"></div>
        <div class="form-group"><label>Calle:</label><input type="text" name="calle" value="${
          d.calle || ""
        }"></div>
        <div class="form-group"><label>Número exterior:</label><input type="text" name="numero_exterior" value="${
          d.numero_exterior || ""
        }"></div>
        <div class="form-group"><label>Número interior:</label><input type="text" name="numero_interior" value="${
          d.numero_interior || ""
        }"></div>
      </div>
    </section>

    <!-- ☎️ Comunicación -->
    <section class="form-section">
      <h3>Comunicación</h3>
      <div class="form-grid">
        <div class="form-group"><label>Teléfono:</label><input type="text" name="telefono" value="${
          d.telefono || ""
        }"></div>
        <div class="form-group"><label>Correo alternativo:</label><input type="email" name="correo_alternativo" value="${
          d.correo_alternativo || ""
        }"></div>
        <div class="form-group"><label>Teléfono de trabajo:</label><input type="text" name="telefono_trabajo" value="${
          d.telefono_trabajo || ""
        }"></div>
      </div>
    </section>

    <!-- 🎓 Formación académica -->
    <section class="form-section formacion">
      <h3>Formación académica</h3>
      <div class="form-grid">
        <div class="form-group">
  <label>Grado académico:</label>
  <select name="grado_academico">
    <option value="">Seleccione</option>
    ${[
      "Técnico Superior",
      "Licenciatura",
      "Ingeniería",
      "Maestría",
      "Doctorado",
      "Posdoctorado",
    ]
      .map(
        (grado) =>
          `<option value="${grado}" ${
            d.grado_academico === grado ? "selected" : ""
          }>${grado}</option>`
      )
      .join("")}
  </select>
</div>

        <div class="form-group"><label>Descripción del grado:</label><input type="text" name="descripcion_grado" value="${
          d.descripcion_grado || ""
        }"></div>
        <div class="form-group"><label>Cédula profesional:</label><input type="text" name="cedula_profesional" value="${
          d.cedula_profesional || ""
        }"></div>
      </div>
    </section>

    <button type="submit" class="btn-nuevo">Guardar cambios</button>
  </form>`;
}

/**
 * 💾 Guardar cambios del formulario
 */
function registrarEventoFormulario(baseUrl) {
  const form = document.getElementById("formDetalles");
  const inputBuscar = document.getElementById("inputBuscarUsuario");
  const contenedor = document.getElementById("formularioDetalles");

  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    try {
      const res = await axios.post(
        `${baseUrl}/admin/usuarios-detalles/guardar`,
        formData
      );

      await Swal.fire({
        title: "✅ Éxito",
        text: res.data.message || "Los datos se guardaron correctamente.",
        icon: "success",
        confirmButtonText: "Aceptar",
      });

      // 🔄 Reiniciar vista
      inputBuscar.value = "";
      contenedor.innerHTML = "";
      inputBuscar.focus();
    } catch (err) {
      console.error("❌ Error al guardar:", err);
      Swal.fire("Error", "No se pudo guardar la información.", "error");
    }
  });
}

/**
 * 🌍 Cargar países (lista completa) y estados
 */
function cargarPaisesYEstados(paisSeleccionado = "", estadoSeleccionado = "") {
  const paisSelect = document.getElementById("selectPais");
  const estadoSelect = document.getElementById("selectEstado");

  if (!paisSelect || !estadoSelect) return;

  // 🌎 Lista completa de países (195)
  const paises = [
    "Afganistán",
    "Albania",
    "Alemania",
    "Andorra",
    "Angola",
    "Antigua y Barbuda",
    "Arabia Saudita",
    "Argelia",
    "Argentina",
    "Armenia",
    "Australia",
    "Austria",
    "Azerbaiyán",
    "Bahamas",
    "Bangladés",
    "Barbados",
    "Baréin",
    "Bélgica",
    "Belice",
    "Benín",
    "Bielorrusia",
    "Birmania",
    "Bolivia",
    "Bosnia y Herzegovina",
    "Botsuana",
    "Brasil",
    "Brunéi",
    "Bulgaria",
    "Burkina Faso",
    "Burundi",
    "Bután",
    "Cabo Verde",
    "Camboya",
    "Camerún",
    "Canadá",
    "Catar",
    "Chad",
    "Chile",
    "China",
    "Chipre",
    "Colombia",
    "Comoras",
    "Corea del Norte",
    "Corea del Sur",
    "Costa Rica",
    "Costa de Marfil",
    "Croacia",
    "Cuba",
    "Dinamarca",
    "Dominica",
    "Ecuador",
    "Egipto",
    "El Salvador",
    "Emiratos Árabes Unidos",
    "Eritrea",
    "Eslovaquia",
    "Eslovenia",
    "España",
    "Estados Unidos",
    "Estonia",
    "Etiopía",
    "Filipinas",
    "Finlandia",
    "Fiyi",
    "Francia",
    "Gabón",
    "Gambia",
    "Georgia",
    "Ghana",
    "Granada",
    "Grecia",
    "Guatemala",
    "Guinea",
    "Guinea-Bisáu",
    "Guinea Ecuatorial",
    "Guyana",
    "Haití",
    "Honduras",
    "Hungría",
    "India",
    "Indonesia",
    "Irak",
    "Irán",
    "Irlanda",
    "Islandia",
    "Islas Marshall",
    "Islas Salomón",
    "Israel",
    "Italia",
    "Jamaica",
    "Japón",
    "Jordania",
    "Kazajistán",
    "Kenia",
    "Kirguistán",
    "Kiribati",
    "Kuwait",
    "Laos",
    "Lesoto",
    "Letonia",
    "Líbano",
    "Liberia",
    "Libia",
    "Liechtenstein",
    "Lituania",
    "Luxemburgo",
    "Madagascar",
    "Malasia",
    "Malaui",
    "Maldivas",
    "Malí",
    "Malta",
    "Marruecos",
    "Mauricio",
    "Mauritania",
    "México",
    "Micronesia",
    "Moldavia",
    "Mónaco",
    "Mongolia",
    "Montenegro",
    "Mozambique",
    "Namibia",
    "Nauru",
    "Nepal",
    "Nicaragua",
    "Níger",
    "Nigeria",
    "Noruega",
    "Nueva Zelanda",
    "Omán",
    "Países Bajos",
    "Pakistán",
    "Palaos",
    "Palestina",
    "Panamá",
    "Papúa Nueva Guinea",
    "Paraguay",
    "Perú",
    "Polonia",
    "Portugal",
    "Reino Unido",
    "República Centroafricana",
    "República Checa",
    "República del Congo",
    "República Democrática del Congo",
    "República Dominicana",
    "Ruanda",
    "Rumania",
    "Rusia",
    "Samoa",
    "San Cristóbal y Nieves",
    "San Marino",
    "San Vicente y las Granadinas",
    "Santa Lucía",
    "Santo Tomé y Príncipe",
    "Senegal",
    "Serbia",
    "Seychelles",
    "Sierra Leona",
    "Singapur",
    "Siria",
    "Somalia",
    "Sri Lanka",
    "Sudáfrica",
    "Sudán",
    "Sudán del Sur",
    "Suecia",
    "Suiza",
    "Surinam",
    "Tailandia",
    "Tanzania",
    "Tayikistán",
    "Timor Oriental",
    "Togo",
    "Tonga",
    "Trinidad y Tobago",
    "Túnez",
    "Turkmenistán",
    "Turquía",
    "Tuvalu",
    "Ucrania",
    "Uganda",
    "Uruguay",
    "Uzbekistán",
    "Vanuatu",
    "Vaticano",
    "Venezuela",
    "Vietnam",
    "Yemen",
    "Yibuti",
    "Zambia",
    "Zimbabue",
  ];

  const datos = {
    México: [
      "Aguascalientes",
      "Baja California",
      "Baja California Sur",
      "Campeche",
      "Chiapas",
      "Chihuahua",
      "Ciudad de México",
      "Coahuila",
      "Colima",
      "Durango",
      "Estado de México",
      "Guanajuato",
      "Guerrero",
      "Hidalgo",
      "Jalisco",
      "Michoacán",
      "Morelos",
      "Nayarit",
      "Nuevo León",
      "Oaxaca",
      "Puebla",
      "Querétaro",
      "Quintana Roo",
      "San Luis Potosí",
      "Sinaloa",
      "Sonora",
      "Tabasco",
      "Tamaulipas",
      "Tlaxcala",
      "Veracruz",
      "Yucatán",
      "Zacatecas",
    ],
  };

  paisSelect.innerHTML = '<option value="">Seleccione un país</option>';
  estadoSelect.innerHTML = '<option value="">Seleccione un estado</option>';

  paises.forEach((pais) => {
    const opt = document.createElement("option");
    opt.value = pais;
    opt.textContent = pais;
    if (pais === paisSeleccionado) opt.selected = true;
    paisSelect.appendChild(opt);
  });

  if (paisSeleccionado && datos[paisSeleccionado]) {
    datos[paisSeleccionado].forEach((estado) => {
      const opt = document.createElement("option");
      opt.value = estado;
      opt.textContent = estado;
      if (estado === estadoSeleccionado) opt.selected = true;
      estadoSelect.appendChild(opt);
    });
  }

  paisSelect.addEventListener("change", () => {
    const pais = paisSelect.value;
    estadoSelect.innerHTML = '<option value="">Seleccione un estado</option>';
    if (datos[pais]) {
      datos[pais].forEach((estado) => {
        const opt = document.createElement("option");
        opt.value = estado;
        opt.textContent = estado;
        estadoSelect.appendChild(opt);
      });
    }
  });
}
