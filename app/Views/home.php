<?= $this->include('layouts/header') ?>

<!-- Hero -->
<section class="hero">
    <div class="container hero-content">
        <div class="hero-text">
            <span class="hero-badge" id="heroBadge">Abiertas inscripciones 2025</span>
            <h1 id="heroTitle">Formación de calidad para el futuro</h1>
            <p id="heroDescription">Descubre cómo nuestra institución puede transformar tu futuro con programas académicos de vanguardia y una comunidad de aprendizaje excepcional. Únete a más de 5,000 estudiantes que han elegido UTSC para su formación profesional.</p>
            
            <div class="hero-buttons">
                <a href="#programas" class="btn">Explorar programas</a>
                <a href="#contacto-rapido" class="btn-secondary">Solicitar información</a>
            </div>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <h3>+5K</h3>
                    <p>Estudiantes</p>
                </div>
                <div class="stat-item">
                    <h3>98%</h3>
                    <p>Egresados empleados</p>
                </div>
                <div class="stat-item">
                    <h3>25+</h3>
                    <p>Programas académicos</p>
                </div>
                <div class="stat-item">
                    <h3>15+</h3>
                    <p>Laboratorios especializados</p>
                </div>
            </div>
        </div>
        
        <div class="hero-image">
            <div class="image-placeholder">
                <img src="<?= base_url('assets/img/alumno.jpg') ?>" alt="Estudiante UTSC">
            </div>
            <div class="floating-element">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="floating-element">
                <i class="fas fa-flask"></i>
            </div>
        </div>
    </div>
</section>

<!-- Quick stats extras -->
<section class="quick-stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-box">
                <i class="fas fa-university"></i>
                <h3>5</h3>
                <p>Carreras disponibles</p>
            </div>
            <div class="stat-box">
                <i class="fas fa-globe"></i>
                <h3>10</h3>
                <p>Convenios internacionales</p>
            </div>
            <div class="stat-box">
                <i class="fas fa-briefcase"></i>
                <h3>95%</h3>
                <p>Tasa de empleabilidad</p>
            </div>
            <div class="stat-box">
                <i class="fas fa-flask"></i>
                <h3>10</h3>
                <p>Laboratorios especializados</p>
            </div>
        </div>
    </div>
</section>

<!-- Video institucional -->
<section class="video-section">
    <div class="container">
        <h2 class="section-title">Conoce nuestra universidad</h2>
        <div class="video-container">
            <iframe width="100%" height="480" src="https://www.youtube.com/embed/rgdTHDdlDhU?si=bgv6hFZ1Pdyp2_x-"
                    title="Video institucional" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
</section>

<!-- Features -->
<section class="features">
    <div class="container">
        <h2 class="section-title">¿Por qué elegir UTSC?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3>Profesores expertos</h3>
                <p>Nuestro equipo docente está compuesto por profesionales con amplia experiencia en la industria y certificaciones internacionales.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <h3>Tecnología de punta</h3>
                <p>Laboratorios con la última tecnología para tu formación práctica en entornos reales de trabajo.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>Vinculación laboral</h3>
                <p>Convenios con más de 100 empresas líderes para prácticas profesionales y empleabilidad.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-globe-americas"></i>
                </div>
                <h3>Intercambios internacionales</h3>
                <p>Programas de intercambio con universidades de más de 20 países.</p>
            </div>
        </div>
    </div>
</section>

<!-- Programas -->
<section class="programs" id="programas">
    <div class="container">
        <h2 class="section-title">Nuestros Programas Académicos</h2>
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

<!-- Testimonios -->
<section class="testimonials">
    <div class="container">
        <h2 class="section-title">Testimonios de Nuestros Egresados</h2>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    "UTSC me dio las herramientas necesarias para destacar en el campo tecnológico."
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">MC</div>
                    <div class="author-info">
                        <h4>María Cortés</h4>
                        <p>Ingeniera en Software, Google</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-content">
                    "La formación en UTSC fue integral, no solo académica sino también en valores."
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">JL</div>
                    <div class="author-info">
                        <h4>Javier López</h4>
                        <p>Gerente de Proyectos, Microsoft</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Eventos -->
<section class="events">
    <div class="container">
        <h2 class="section-title">Próximos Eventos</h2>
        <div class="events-grid">
            <div class="event-card">
                <div class="event-date">
                    <span class="day">15</span>
                    <span class="month">Sept</span>
                </div>
                <div class="event-content">
                    <h3>Jornada de Puertas Abiertas</h3>
                    <p>Conoce instalaciones, programas y becas disponibles.</p>
                    <a href="#" class="btn">Registrarse</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA section -->
<section class="cta">
    <div class="container">
        <h2>¿Listo para transformar tu futuro?</h2>
        <p>Únete a nuestra comunidad estudiantil y forma parte de la nueva generación de profesionales.</p>
        <div class="cta-buttons">
            <a href="#contacto-rapido" class="btn-light">Registrarse ahora</a>
            <a href="#" class="btn-secondary">Agendar tour virtual</a>
        </div>
    </div>
</section>

<!-- Contacto rápido -->
<section class="contact-cta" id="contacto-rapido">
    <div class="container">
        <h2>¿Tienes dudas? Contáctanos</h2>
        <p>Estamos listos para resolver tus preguntas y brindarte toda la información que necesites.</p>
        <a href="<?= base_url('contacto') ?>" class="btn">Ir a la sección de contacto</a>
    </div>
</section>


<!-- Botón flotante de WhatsApp -->
<a href="https://wa.me/521234567890" target="_blank" class="whatsapp-btn">
    <i class="fab fa-whatsapp"></i>
</a>

<?= $this->include('layouts/footer') ?>
