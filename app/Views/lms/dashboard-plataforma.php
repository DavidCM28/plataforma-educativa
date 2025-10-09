<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard principal</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <main class="content-dark">
        <section class="welcome-card">
            <h1>👋 Bienvenido, <?= esc(session('nombre')) ?>!</h1>
            <p>Rol actual: <strong><?= esc(session('rol')) ?></strong></p>
        </section>

        <section class="cards-grid">
            <div class="card-metric">
                <i class="fas fa-users"></i>
                <h2>Usuarios</h2>
                <p>+24 nuevos</p>
            </div>

            <div class="card-metric">
                <i class="fas fa-graduation-cap"></i>
                <h2>Materias</h2>
                <p>12 activas</p>
            </div>

            <div class="card-metric">
                <i class="fas fa-tasks"></i>
                <h2>Tareas</h2>
                <p>6 pendientes</p>
            </div>
            <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Alumno</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/alumno.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <main class="content-dark">
        <section class="welcome-card">
            <h1>👋 Bienvenido, <?= esc(session('nombre')) ?>!</h1>
            <p>Rol actual: <strong>Alumno</strong> | Curso: <strong>4to Año</strong></p>
        </section>

        <div class="dashboard-alumno">
            <!-- MIS MATERIAS -->
            <h2 class="seccion-titulo">
                <i class="fas fa-book-open"></i> Mis Materias
            </h2>
            
            <div class="materias-grid">
                <div class="materia-card">
                    <div class="materia-header">
                        <h3>Matemáticas</h3>
                        <span class="materia-status active">En Curso</span>
                    </div>
                    <div class="materia-info">
                        <p><i class="fas fa-user-graduate"></i> Prof. María González</p>
                        <p><i class="fas fa-clock"></i> Lunes y Miércoles 10:00-12:00</p>
                    </div>
                    <div class="materia-actions">
                        <button class="btn-materiales">
                            <i class="fas fa-folder"></i> Materiales
                        </button>
                        <button class="btn-tareas">
                            <i class="fas fa-tasks"></i> Tareas
                        </button>
                    </div>
                </div>

                <div class="materia-card">
                    <div class="materia-header">
                        <h3>Historia</h3>
                        <span class="materia-status active">En Curso</span>
                    </div>
                    <div class="materia-info">
                        <p><i class="fas fa-user-graduate"></i> Prof. Carlos López</p>
                        <p><i class="fas fa-clock"></i> Martes y Jueves 14:00-16:00</p>
                    </div>
                    <div class="materia-actions">
                        <button class="btn-materiales">
                            <i class="fas fa-folder"></i> Materiales
                        </button>
                        <button class="btn-tareas">
                            <i class="fas fa-tasks"></i> Tareas
                        </button>
                    </div>
                </div>

                <div class="materia-card">
                    <div class="materia-header">
                        <h3>Programación</h3>
                        <span class="materia-status active">En Curso</span>
                    </div>
                    <div class="materia-info">
                        <p><i class="fas fa-user-graduate"></i> Prof. Ana Martínez</p>
                        <p><i class="fas fa-clock"></i> Viernes 08:00-11:00</p>
                    </div>
                    <div class="materia-actions">
                        <button class="btn-materiales">
                            <i class="fas fa-folder"></i> Materiales
                        </button>
                        <button class="btn-tareas">
                            <i class="fas fa-tasks"></i> Tareas
                        </button>
                    </div>
                </div>
            </div>

            <!-- TAREAS PENDIENTES -->
            <h2 class="seccion-titulo">
                <i class="fas fa-tasks"></i> Tareas Pendientes
            </h2>
            
            <div class="tareas-lista">
                <div class="tarea-item urgente">
                    <div class="tarea-info">
                        <h4>Proyecto Final - Matemáticas</h4>
                        <p>Entrega: 15 Dic 2024 | Puntos: 100</p>
                        <span class="tarea-materia">Matemáticas</span>
                    </div>
                    <div class="tarea-actions">
                        <button class="btn-entregar">
                            <i class="fas fa-paper-plane"></i> Entregar
                        </button>
                        <span class="tarea-estado">Urgente</span>
                    </div>
                </div>

                <div class="tarea-item">
                    <div class="tarea-info">
                        <h4>Ensayo Histórico</h4>
                        <p>Entrega: 20 Dic 2024 | Puntos: 80</p>
                        <span class="tarea-materia">Historia</span>
                    </div>
                    <div class="tarea-actions">
                        <button class="btn-entregar">
                            <i class="fas fa-paper-plane"></i> Entregar
                        </button>
                        <span class="tarea-estado">Pendiente</span>
                    </div>
                </div>

                <div class="tarea-item">
                    <div class="tarea-info">
                        <h4>Aplicación Web CRUD</h4>
                        <p>Entrega: 18 Dic 2024 | Puntos: 150</p>
                        <span class="tarea-materia">Programación</span>
                    </div>
                    <div class="tarea-actions">
                        <button class="btn-entregar">
                            <i class="fas fa-paper-plane"></i> Entregar
                        </button>
                        <span class="tarea-estado">Pendiente</span>
                    </div>
                </div>
            </div>

            <!-- CALIFICACIONES RECIENTES -->
            <h2 class="seccion-titulo">
                <i class="fas fa-chart-line"></i> Calificaciones Recientes
            </h2>
            
            <div class="calificaciones-grid">
                <div class="calificacion-card">
                    <div class="calificacion-header">
                        <h3>Examen Parcial</h3>
                        <span class="calificacion-nota excelente">9.2</span>
                    </div>
                    <p class="calificacion-materia">Matemáticas</p>
                    <p class="calificacion-fecha">05/12/2024</p>
                </div>

                <div class="calificacion-card">
                    <div class="calificacion-header">
                        <h3>Investigación</h3>
                        <span class="calificacion-nota buena">8.5</span>
                    </div>
                    <p class="calificacion-materia">Historia</p>
                    <p class="calificacion-fecha">01/12/2024</p>
                </div>

                <div class="calificacion-card">
                    <div class="calificacion-header">
                        <h3>Proyecto PHP</h3>
                        <span class="calificacion-nota excelente">9.8</span>
                    </div>
                    <p class="calificacion-materia">Programación</p>
                    <p class="calificacion-fecha">28/11/2024</p>
                </div>
            </div>

            <!-- HORARIO SEMANAL -->
            <h2 class="seccion-titulo">
                <i class="fas fa-calendar-alt"></i> Horario Semanal
            </h2>
            
            <div class="horario-container">
                <div class="horario-grid">
                    <div class="horario-dia">
                        <h4>Lunes</h4>
                        <div class="horario-clase matematica">
                            <span class="hora">10:00-12:00</span>
                            <span class="materia">Matemáticas</span>
                            <span class="aula">Aula 204</span>
                        </div>
                    </div>
                    
                    <div class="horario-dia">
                        <h4>Martes</h4>
                        <div class="horario-clase historia">
                            <span class="hora">14:00-16:00</span>
                            <span class="materia">Historia</span>
                            <span class="aula">Aula 105</span>
                        </div>
                    </div>
                    
                    <div class="horario-dia">
                        <h4>Miércoles</h4>
                        <div class="horario-clase matematica">
                            <span class="hora">10:00-12:00</span>
                            <span class="materia">Matemáticas</span>
                            <span class="aula">Aula 204</span>
                        </div>
                    </div>
                    
                    <div class="horario-dia">
                        <h4>Jueves</h4>
                        <div class="horario-clase historia">
                            <span class="hora">14:00-16:00</span>
                            <span class="materia">Historia</span>
                            <span class="aula">Aula 105</span>
                        </div>
                    </div>
                    
                    <div class="horario-dia">
                        <h4>Viernes</h4>
                        <div class="horario-clase programacion">
                            <span class="hora">08:00-11:00</span>
                            <span class="materia">Programación</span>
                            <span class="aula">Lab. Computación</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script src="<?= base_url('assets/js/alumno.js') ?>"></script>
</body>
</html>
        </section>
    </main>


    <script src="<?= base_url('assets/js/sidebar.js') ?>">
        // Funcionalidades específicas para interfaz alumno
document.addEventListener('DOMContentLoaded', function() {
    
    // Botones de materiales
    const btnMateriales = document.querySelectorAll('.btn-materiales');
    btnMateriales.forEach(btn => {
        btn.addEventListener('click', function() {
            const materia = this.closest('.materia-card').querySelector('h3').textContent;
            Swal.fire({
                title: `Materiales - ${materia}`,
                text: 'Accediendo a los materiales de la materia...',
                icon: 'info',
                confirmButtonText: 'Continuar'
            });
        });
    });
    
    // Botones de tareas
    const btnTareas = document.querySelectorAll('.btn-tareas');
    btnTareas.forEach(btn => {
        btn.addEventListener('click', function() {
            const materia = this.closest('.materia-card').querySelector('h3').textContent;
            Swal.fire({
                title: `Tareas - ${materia}`,
                text: 'Cargando lista de tareas...',
                icon: 'info',
                confirmButtonText: 'Ver Tareas'
            });
        });
    });
    
    // Botones de entregar tarea
    const btnEntregar = document.querySelectorAll('.btn-entregar');
    btnEntregar.forEach(btn => {
        btn.addEventListener('click', function() {
            const tarea = this.closest('.tarea-item').querySelector('h4').textContent;
            Swal.fire({
                title: `Entregar: ${tarea}`,
                html: `
                    <input type="file" class="swal2-file" accept=".pdf,.doc,.docx,.zip">
                    <textarea class="swal2-textarea" placeholder="Comentarios adicionales..."></textarea>
                `,
                showCancelButton: true,
                confirmButtonText: 'Enviar Entrega',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    // Aquí iría la lógica de subida de archivos
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('¡Éxito!', 'Tu tarea ha sido entregada correctamente.', 'success');
                }
            });
        });
    });
    
    // Efectos hover para tarjetas interactivas
    const cards = document.querySelectorAll('.materia-card, .calificacion-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Simular carga de datos
    function simularCargaDatos() {
        console.log('Cargando datos del alumno...');
        // Aquí irían las llamadas AJAX para cargar datos reales
    }
    
    simularCargaDatos();
    
    // Notificación de tareas pendientes
    const tareasPendientes = document.querySelectorAll('.tarea-item').length;
    if (tareasPendientes > 0) {
        console.log(`Tienes ${tareasPendientes} tareas pendientes`);
    }
});
    </script>
</body>

</html>