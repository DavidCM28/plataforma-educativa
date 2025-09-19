<?= $this->include('layouts/header') ?>
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" />
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Inicializar AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,   // Solo una vez
        mirror: false // Sin animación al regresar
    });

    // Asignar delays automáticos a elementos con lista repetitiva
    document.addEventListener("DOMContentLoaded", () => {
        const groups = [
            ".programs-grid .program-card",
            ".scholarships-grid .scholarship-card",
            ".directory-grid .contact-card",
            ".faq .faq-item",
            ".steps .step"
        ];

        groups.forEach(selector => {
            document.querySelectorAll(selector).forEach((el, index) => {
                el.setAttribute("data-aos", el.getAttribute("data-aos") || "zoom-in");
                el.setAttribute("data-aos-delay", (index + 1) * 100); // 100, 200, 300...
            });
        });
    });
</script>


<!-- ====================
     NOTICIAS (Bootstrap)
==================== -->
<section id="noticias" class="news py-5">
    <div class="container">
        <h2 class="section-title text-center mb-4">Noticias y Avisos</h2>

        <div id="newsCarousel" class="carousel slide" data-bs-ride="carousel">

            <!-- Paginación (puntitos) -->
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#newsCarousel" data-bs-slide-to="0" class="active"
                    aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#newsCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            </div>

            <!-- Slides -->
            <div class="carousel-inner">

                <!-- Slide 1 -->
                <div class="carousel-item active">
                    <div class="news-card p-4 text-center">
                        <h3>Convocatoria de Becas 2025</h3>
                        <p>Ya está disponible la convocatoria para solicitar becas este semestre.</p>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="carousel-item">
                    <div class="news-card p-4 text-center">
                        <h3>Semana de Ciencia y Tecnología</h3>
                        <p>Del 15 al 20 de octubre, conferencias y talleres abiertos a todo público.</p>
                    </div>
                </div>

            </div>

            <!-- Controles (flechas con FontAwesome) -->
            <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev">
                <i class="fas fa-chevron-left"></i>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel" data-bs-slide="next">
                <i class="fas fa-chevron-right"></i>
                <span class="visually-hidden">Siguiente</span>
            </button>

        </div>
    </div>
</section>



<!-- ====================
     INICIO / HERO
==================== -->
<section id="inicio" class="hero" data-aos="fade-up">
    <div class="container hero-content">
        <div class="hero-text" data-aos="fade-right">
            <span class="hero-badge">Abiertas inscripciones 2025</span>
            <h1>Formación de calidad para el futuro</h1>
            <p>
                Descubre cómo nuestra institución puede transformar tu futuro con programas académicos
                de vanguardia y una comunidad de aprendizaje excepcional.
            </p>
            <div class="hero-buttons" data-aos="zoom-in" data-aos-delay="200">
                <a href="#oferta-educativa" class="btn">Explorar programas</a>
                <a href="<?= base_url('contacto') ?>" class="btn-secondary">Solicitar información</a>
            </div>
        </div>

        <div class="hero-image" data-aos="fade-left">
            <div class="image-placeholder">
                <img src="<?= base_url('assets/img/alumno.jpg') ?>" alt="Estudiante UTSC">
            </div>
        </div>
    </div>
</section>

<!-- ====================
     NOSOTROS
==================== -->
<section id="nosotros" class="features" data-aos="fade-up">
    <div class="container">
        <h2 class="section-title" data-aos="fade-down">Sobre Nosotros</h2>
        <p class="section-desc" data-aos="fade-up" data-aos-delay="150">
            Somos una institución comprometida con la excelencia académica y la formación integral de nuestros
            estudiantes.
            Nuestros valores, misión y visión nos impulsan a transformar vidas a través de la educación.
        </p>
        <div class="video-container" style="margin-top:40px;" data-aos="zoom-in" data-aos-delay="300">
            <iframe width="100%" height="480" src="https://www.youtube.com/embed/rgdTHDdlDhU"
                title="Video institucional" frameborder="0" allowfullscreen>
            </iframe>
        </div>
    </div>
</section>

<!-- ====================
     ADMISIONES
==================== -->
<section id="admisiones" class="admissions" data-aos="fade-up">
    <div class="container">
        <h2 class="section-title" data-aos="fade-down">Proceso de Admisión</h2>
        <div class="steps">
            <div class="step" data-aos="zoom-in" data-aos-delay="100">
                <span class="step-number">1</span>
                <h3>Registro en línea</h3>
                <p>Completa el formulario de preinscripción.</p>
            </div>
            <div class="step" data-aos="zoom-in" data-aos-delay="200">
                <span class="step-number">2</span>
                <h3>Entrega de documentos</h3>
                <p>Presenta tu papelería en servicios escolares.</p>
            </div>
            <div class="step" data-aos="zoom-in" data-aos-delay="300">
                <span class="step-number">3</span>
                <h3>Examen de admisión</h3>
                <p>Demuestra tus conocimientos básicos.</p>
            </div>
            <div class="step" data-aos="zoom-in" data-aos-delay="400">
                <span class="step-number">4</span>
                <h3>Inscripción</h3>
                <p>Formaliza tu lugar y recibe tu matrícula.</p>
            </div>
        </div>
    </div>
</section>

<!-- ====================
     OFERTA EDUCATIVA
==================== -->
<section id="oferta-educativa" class="programs" data-aos="fade-up">
    <div class="container">
        <h2 class="section-title" data-aos="fade-down">Nuestra Oferta Educativa</h2>
        <div class="scholarships-grid">
            <?php foreach ($carreras as $index => $carrera): ?>
                <div class="scholarship-card" data-aos="zoom-in" data-aos-delay="<?= ($index + 1) * 100 ?>">
                    <div class="scholarship-header">
                        <i class="fas fa-university"></i>
                    </div>
                    <!-- Botón info que redirige -->
                    <a href="<?= base_url('carrera/' . $carrera['slug']) ?>" class="info-link" title="Ver detalles">
                        <i class="fas fa-info"></i>
                    </a>
                    <h3><?= esc($carrera['nombre']) ?></h3>
                    <p><?= esc($carrera['descripcion']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ====================
     BECAS
==================== -->
<section id="becas" class="scholarships" data-aos="fade-up">
    <div class="container">
        <h2 class="section-title" data-aos="fade-down">Becas y Apoyos</h2>
        <div class="scholarships-grid">
            <?php if (!empty($becas)): ?>
                <?php $delay = 100; ?>
                <?php foreach ($becas as $beca): ?>
                    <div class="scholarship-card" data-aos="zoom-in" data-aos-delay="<?= $delay ?>">
                        <div class="scholarship-header">
                            <i class="fas fa-graduation-cap"></i>
                        </div>

                        <!-- Botón info con dataset -->
                        <button class="info-btn" data-nombre="<?= esc($beca['nombre']) ?>"
                            data-requisitos="<?= esc($beca['requisitos']) ?>"
                            data-horas="<?= esc($beca['servicio_becario_horas']) ?>">
                            <i class="fas fa-info"></i>
                        </button>


                        <h3><?= esc($beca['nombre']) ?></h3>
                        <p><?= esc($beca['descripcion']) ?></p>
                    </div>
                    <?php $delay += 100; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay becas registradas en este momento.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ====================
     DIRECTORIO
==================== -->
<section id="directorio" class="directory" data-aos="fade-up">
    <div class="container">
        <h2 class="section-title" data-aos="fade-down">Directorio Rápido</h2>
        <div class="directory-grid">
            <div class="contact-card" data-aos="zoom-in" data-aos-delay="100">
                <h3>Escolares</h3>
                <p><i class="fas fa-envelope"></i> escolares@utsc.edu.mx</p>
                <p><i class="fas fa-phone"></i> (123) 456 7890</p>
            </div>
            <div class="contact-card" data-aos="zoom-in" data-aos-delay="200">
                <h3>Finanzas</h3>
                <p><i class="fas fa-envelope"></i> finanzas@utsc.edu.mx</p>
                <p><i class="fas fa-phone"></i> (123) 456 7891</p>
            </div>
        </div>
    </div>
</section>

<!-- ====================
     FAQ
==================== -->


<section id="faq" class="faq" data-aos="fade-up">
    <div class="container">
        <h2 class="section-title" data-aos="fade-down">Preguntas Frecuentes</h2>
        <div class="faq-item" data-aos="zoom-in" data-aos-delay="100">
            <details>
                <summary>¿Cómo solicito una beca?</summary>
                <p>Puedes hacerlo en línea en la sección de Becas o acudiendo al departamento de servicios escolares.
                </p>
            </details>
        </div>
        <div class="faq-item" data-aos="zoom-in" data-aos-delay="200">
            <details>
                <summary>¿Cuándo son las inscripciones?</summary>
                <p>El proceso de inscripción inicia en junio y se extiende hasta agosto.</p>
            </details>
        </div>
    </div>
</section>

<script>
    document.querySelectorAll(".faq-item details").forEach((detail) => {
        const content = detail.querySelector("p");

        detail.addEventListener("toggle", function () {
            if (detail.open) {
                // Abrir con animación
                content.style.maxHeight = content.scrollHeight + "px";
                content.style.opacity = "1";

                // Cerrar otros
                document.querySelectorAll(".faq-item details").forEach((d) => {
                    if (d !== detail) {
                        d.open = false;
                        const otherContent = d.querySelector("p");
                        otherContent.style.maxHeight = null;
                        otherContent.style.opacity = "0";
                    }
                });
            } else {
                // Cerrar con animación
                content.style.maxHeight = null;
                content.style.opacity = "0";
            }
        });
    });

</script>

<!-- ====================
     BOTÓN WHATSAPP
==================== -->
<a href="https://wa.me/521234567890" target="_blank" class="whatsapp-btn" data-aos="zoom-in" data-aos-delay="300">
    <i class="fab fa-whatsapp"></i>
</a>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Solo selecciona los botones de becas
        const becaButtons = document.querySelectorAll(".scholarships-grid .info-btn");

        becaButtons.forEach(btn => {
            btn.addEventListener("click", function (e) {
                e.preventDefault(); // Evita cualquier comportamiento raro de botón

                const nombre = this.dataset.nombre;
                const requisitos = this.dataset.requisitos;
                const horas = this.dataset.horas;

                Swal.fire({
                    title: nombre,
                    html: `
                    <p><strong>Requisitos:</strong></p>
                    <p>${requisitos}</p>
                    <hr>
                    <p><strong>Horas de servicio becario:</strong> ${horas > 0 ? horas : 'No aplica'}</p>
                `,
                    icon: "info",
                    confirmButtonText: "Cerrar",
                    confirmButtonColor: "#ff6600"
                });
            });
        });
    });
</script>



<?= $this->include('layouts/footer') ?>