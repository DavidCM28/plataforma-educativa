<?= $this->include('layouts/header') ?>
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" />
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>
<script>
    document.querySelectorAll(".faq-item summary").forEach((summary) => {
        summary.addEventListener("click", function () {
            const open = this.parentNode.open;
            document.querySelectorAll(".faq-item details").forEach((d) => d.removeAttribute("open"));
            if (!open) this.parentNode.setAttribute("open", true);
        });
    });
</script>

<!-- ====================
     NOTICIAS
==================== -->
<section id="noticias" class="news">
    <div class="container">
        <h2 class="section-title">Noticias y Avisos</h2>
        <div class="swiper news-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="news-card">
                        <h3>Convocatoria de Becas 2025</h3>
                        <p>Ya está disponible la convocatoria para solicitar becas este semestre.</p>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="news-card">
                        <h3>Semana de Ciencia y Tecnología</h3>
                        <p>Del 15 al 20 de octubre, conferencias y talleres abiertos a todo público.</p>
                    </div>
                </div>
            </div>
            <div class="swiper-button-prev"><i class="fas fa-chevron-left"></i></div>
            <div class="swiper-button-next"><i class="fas fa-chevron-right"></i></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>

<!-- SwiperJS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
<script>
    new Swiper(".news-slider", {
        loop: true,
        pagination: { el: ".swiper-pagination", clickable: true },
        autoplay: { delay: 5000 },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
    });
</script>


<!-- ====================
     INICIO / HERO
==================== -->
<section id="inicio" class="hero">
    <div class="container hero-content">
        <div class="hero-text">
            <span class="hero-badge">Abiertas inscripciones 2025</span>
            <h1>Formación de calidad para el futuro</h1>
            <p>
                Descubre cómo nuestra institución puede transformar tu futuro con programas académicos
                de vanguardia y una comunidad de aprendizaje excepcional.
            </p>

            <div class="hero-buttons">
                <a href="#oferta-educativa" class="btn">Explorar programas</a>
                <a href="<?= base_url('contacto') ?>" class="btn-secondary">Solicitar información</a>
            </div>
        </div>

        <div class="hero-image">
            <div class="image-placeholder">
                <img src="<?= base_url('assets/img/alumno.jpg') ?>" alt="Estudiante UTSC">
            </div>
        </div>
    </div>
</section>


<!-- ====================
     NOSOTROS
==================== -->
<section id="nosotros" class="features">
    <div class="container">
        <h2 class="section-title">Sobre Nosotros</h2>
        <p class="section-desc">
            Somos una institución comprometida con la excelencia académica y la formación integral de nuestros
            estudiantes.
            Nuestros valores, misión y visión nos impulsan a transformar vidas a través de la educación.
        </p>

        <!-- Opcional: Video institucional -->
        <div class="video-container" style="margin-top:40px;">
            <iframe width="100%" height="480" src="https://www.youtube.com/embed/rgdTHDdlDhU"
                title="Video institucional" frameborder="0" allowfullscreen>
            </iframe>
        </div>
    </div>
</section>

<!-- ====================
     ADMISIONES
==================== -->
<section id="admisiones" class="admissions">
    <div class="container">
        <h2 class="section-title">Proceso de Admisión</h2>
        <div class="steps">
            <div class="step" data-aos="fade-up" data-aos-delay="100">
                <span class="step-number">1</span>
                <h3>Registro en línea</h3>
                <p>Completa el formulario de preinscripción.</p>
            </div>
            <div class="step" data-aos="fade-up" data-aos-delay="100">
                <span class="step-number">2</span>
                <h3>Entrega de documentos</h3>
                <p>Presenta tu papelería en servicios escolares.</p>
            </div>
            <div class="step" data-aos="fade-up" data-aos-delay="100">
                <span class="step-number">3</span>
                <h3>Examen de admisión</h3>
                <p>Demuestra tus conocimientos básicos.</p>
            </div>
            <div class="step" data-aos="fade-up" data-aos-delay="100">
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
<section id="oferta-educativa" class="programs">
    <div class="container">
        <h2 class="section-title">Nuestra Oferta Educativa</h2>

        <div class="programs-grid">
            <div class="program-card">
                <div class="program-image">
                    <i class="fas fa-microchip"></i>
                </div>
                <div class="program-content">
                    <h3>Ingeniería en Sistemas Computacionales</h3>
                    <p>Formación en desarrollo de software, redes y tecnologías emergentes.</p>
                    <a href="#" class="btn">Más información</a>
                </div>
            </div>

            <div class="program-card">
                <div class="program-image">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="program-content">
                    <h3>Administración de Empresas</h3>
                    <p>Desarrolla habilidades directivas y emprendedoras para el mundo global.</p>
                    <a href="#" class="btn">Más información</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====================
     BECAS
==================== -->
<section id="becas" class="scholarships">
    <div class="container">
        <h2 class="section-title">Becas y Apoyos</h2>
        <div class="scholarships-grid">

            <div class="scholarship-card">
                <div class="scholarship-header">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <button class="info-btn"><i class="fas fa-info"></i></button>
                <h3>Beca Académica</h3>
                <p>Apoyo a estudiantes con alto rendimiento académico.</p>
            </div>

            <div class="scholarship-card">
                <div class="scholarship-header">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <button class="info-btn"><i class="fas fa-info"></i></button>
                <h3>Beca Deportiva</h3>
                <p>Para alumnos destacados en disciplinas deportivas.</p>
            </div>

            <div class="scholarship-card">
                <div class="scholarship-header">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <button class="info-btn"><i class="fas fa-info"></i></button>
                <h3>Beca Socioeconómica</h3>
                <p>Apoyo a familias con bajos ingresos.</p>
            </div>

        </div>
    </div>
</section>



<!-- ====================
     DIRECTORIO
==================== -->
<section id="directorio" class="directory">
    <div class="container">
        <h2 class="section-title">Directorio Rápido</h2>
        <div class="directory-grid">
            <div class="contact-card">
                <h3>Escolares</h3>
                <p><i class="fas fa-envelope"></i> escolares@utsc.edu.mx</p>
                <p><i class="fas fa-phone"></i> (123) 456 7890</p>
            </div>
            <div class="contact-card">
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
<section id="faq" class="faq">
    <div class="container">
        <h2 class="section-title">Preguntas Frecuentes</h2>
        <div class="faq-item">
            <details>
                <summary>¿Cómo solicito una beca?</summary>
                <p>Puedes hacerlo en línea en la sección de Becas o acudiendo al departamento de servicios escolares.
                </p>
            </details>
        </div>
        <div class="faq-item">
            <details>
                <summary>¿Cuándo son las inscripciones?</summary>
                <p>El proceso de inscripción inicia en junio y se extiende hasta agosto.</p>
            </details>
        </div>
    </div>
</section>





<!-- ====================
     BOTÓN WHATSAPP
==================== -->
<a href="https://wa.me/521234567890" target="_blank" class="whatsapp-btn">
    <i class="fab fa-whatsapp"></i>
</a>

<?= $this->include('layouts/footer') ?>