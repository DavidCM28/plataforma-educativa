<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');
$routes->get('contacto', 'Home::contacto');
$routes->get('carrera/(:segment)', 'Home::carrera/$1');
$routes->get('test', 'Home::test');

// API contacto
$routes->post('api/contacto/guardar', 'Contacto::guardar');
$routes->post('contacto/enviar', 'Contacto::enviar');

// Login / Logout
$routes->get('auth/login', 'Auth::login');
$routes->post('auth/doLogin', 'Auth::doLogin'); // ✅ ruta que usa el fetch
$routes->get('auth/logout', 'Auth::logout');

// Alias cortos (opcional)
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::doLogin');
$routes->get('logout', 'Auth::logout');

// Dashboard
$routes->get('dashboard', 'Dashboard::index');


//Perfil
$routes->group('perfil', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Perfil::index');
    $routes->post('actualizarFoto', 'Perfil::actualizarFoto');
    $routes->post('subirFotoCloud', 'Perfil::subirFotoCloud');
    $routes->post('actualizarPassword', 'Perfil::actualizarPassword');
    $routes->post('guardarDetalles', 'Perfil::guardarDetalles'); // ✅ nueva ruta
});


//Datos Personales
$routes->get('usuarios-detalles', 'Admin\UsuariosDetalles::index');
$routes->get('usuarios-detalles/buscar', 'Admin\UsuariosDetalles::buscarUsuario');



// Módulo de administración (solo superusuario)
$routes->group('admin', function ($routes) {
    $routes->get('usuarios', 'Admin\Usuarios::index');   // Listado de usuarios
    $routes->get('usuarios/detalle/(:num)', 'Admin\Usuarios::detalle/$1');
    $routes->get('usuarios/nuevo', 'Admin\Usuarios::create');
    $routes->post('usuarios/guardar', 'Admin\Usuarios::store');
    $routes->get('usuarios/editar/(:num)', 'Admin\Usuarios::edit/$1');
    $routes->post('usuarios/actualizar/(:num)', 'Admin\Usuarios::update/$1');
    $routes->get('usuarios/eliminar/(:num)', 'Admin\Usuarios::delete/$1');
    $routes->delete('usuarios/eliminar/(:num)', 'Admin\Usuarios::delete/$1');
    $routes->get('usuarios/plantilla', 'Admin\Usuarios::plantilla');
    $routes->post('usuarios/importar', 'Admin\Usuarios::importar');
    $routes->get('usuarios/descargar-credenciales', 'Admin\Usuarios::descargarCredenciales');
    $routes->get('usuarios-detalles/ver/(:num)', 'Admin\UsuariosDetalles::ver/$1');
    $routes->post('usuarios-detalles/guardar', 'Admin\UsuariosDetalles::guardar');
    // 🔹 Nueva ruta: obtener carreras vía AJAX
    $routes->get('usuarios/obtenerCarreras', 'Admin\Usuarios::obtenerCarreras');

    // 🔹 Nuevas rutas: descargar plantillas de Excel
    $routes->get('usuarios/plantilla-alumnos', 'Admin\Usuarios::plantillaAlumnos');
    $routes->get('usuarios/plantilla-empleados', 'Admin\Usuarios::plantillaEmpleados');
});

// Módulo de gestión de Carreras
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('carreras', 'CarrerasController::index');
    $routes->post('carreras/crear', 'CarrerasController::crear');
    $routes->post('carreras/actualizar/(:num)', 'CarrerasController::actualizar/$1');
    $routes->get('carreras/eliminar/(:num)', 'CarrerasController::eliminar/$1');
});


// Módulo de gestión de Materias
$routes->group('admin', function ($routes) {
    $routes->get('materias', 'Admin\MateriasController::index');
    $routes->post('materias/crear', 'Admin\MateriasController::crear');
    $routes->post('materias/actualizar/(:num)', 'Admin\MateriasController::actualizar/$1');
    $routes->get('materias/eliminar/(:num)', 'Admin\MateriasController::eliminar/$1');
    $routes->get('materias/verificar-clave', 'Admin\MateriasController::verificarClave');

});

// Módulo de gestión de Planes de Estudio
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('planes-estudio', 'PlanesEstudioController::index');
    $routes->post('planes-estudio/crear', 'PlanesEstudioController::crear');
    $routes->post('planes-estudio/actualizar/(:num)', 'PlanesEstudioController::actualizar/$1');
    $routes->get('planes-estudio/eliminar/(:num)', 'PlanesEstudioController::eliminar/$1');
    $routes->post('planes-estudio/agregarMateria', 'PlanesEstudioController::agregarMateria');
    $routes->get('planes-estudio/eliminar-materia/(:num)', 'PlanesEstudioController::eliminarMateria/$1');
    $routes->get('planes-estudio/materias-por-plan/(:num)', 'PlanesEstudioController::materiasPorPlan/$1');

});

// Módulo de Grupos
$routes->group('admin/grupos', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('/', 'GruposController::index');
    $routes->post('crear', 'GruposController::crear');
    $routes->post('actualizar/(:num)', 'GruposController::actualizar/$1'); // ✅ nueva ruta para editar
    $routes->get('eliminar/(:num)', 'GruposController::eliminar/$1');
});

// Módulo de Asignaciones
// ===============================================
// DOCENTES (materia ↔ grupo ↔ profesor)
// ===============================================
$routes->group('admin/asignaciones', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'AsignacionesController::index');
    $routes->post('asignar-profesor', 'AsignacionesController::asignarProfesor');
    $routes->post('actualizar/(:num)', 'AsignacionesController::actualizarAsignacion/$1');
    $routes->get('detalle/(:num)', 'AsignacionesController::detalle/$1');
    $routes->get('eliminar-profesor/(:num)', 'AsignacionesController::eliminarProfesor/$1');
    $routes->get('materias-por-grupo/(:num)', 'AsignacionesController::materiasPorGrupo/$1');
    $routes->get('horario-grupo/(:num)', 'AsignacionesController::horarioGrupo/$1');
    $routes->get('frecuencias-restantes/(:num)/(:num)', 'AsignacionesController::frecuenciasRestantes/$1/$2');
    $routes->post('eliminar-frecuencia/(:num)', 'AsignacionesController::eliminarFrecuencia/$1');
    $routes->post('actualizar-frecuencia/(:num)', 'AsignacionesController::actualizarFrecuencia/$1');
});

// ===============================================
// ALUMNOS (carrera ↔ grupo ↔ alumno)
// ===============================================
$routes->group('admin/asignaciones-alumnos', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'AsignacionesAlumnosController::index');

    // 🔹 Acciones principales
    $routes->post('vincular-alumno-carrera', 'AsignacionesAlumnosController::vincularAlumnoCarrera');
    $routes->post('asignar-alumno', 'AsignacionesAlumnosController::asignarAlumno');
    $routes->post('promover-grupo/(:num)', 'AsignacionesAlumnosController::promoverGrupo/$1');

    // 🔹 Consultas dinámicas
    $routes->get('buscar-alumno', 'AsignacionesAlumnosController::buscarAlumno');
    $routes->get('alumnos-por-carrera/(:num)', 'AsignacionesAlumnosController::alumnosPorCarrera/$1');
    $routes->get('alumnos-inscritos/(:num)', 'AsignacionesAlumnosController::alumnosInscritos/$1');
    $routes->get('grupos-por-carrera/(:num)', 'AsignacionesAlumnosController::gruposPorCarrera/$1');
    $routes->post('crear-grupo-extra/(:num)', 'AsignacionesAlumnosController::crearGrupoExtra/$1');
    $routes->post('eliminar-multiples', 'AsignacionesAlumnosController::eliminarMultiples');


    // 🔹 Eliminación
    $routes->delete('eliminar-alumno/(:num)', 'AsignacionesAlumnosController::eliminarAlumno/$1');
});




// Módulo de Configuración de Ciclos
$routes->group('admin/ciclos', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'CiclosController::index');
    $routes->post('crear', 'CiclosController::crear');
    $routes->get('eliminar/(:num)', 'CiclosController::eliminar/$1');
    $routes->get('estado/(:num)', 'CiclosController::cambiarEstado/$1');
});

// Módulo de Criterios
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    // Gestión de criterios
    $routes->get('criterios', 'CriteriosController::index');
    $routes->post('criterios/crear', 'CriteriosController::crear');
    $routes->get('criterios/eliminar/(:num)', 'CriteriosController::eliminar/$1');
    $routes->get('criterios/estado/(:num)', 'CriteriosController::cambiarEstado/$1');

    // Ponderaciones
    $routes->post('criterios/ponderaciones/guardar', 'CriteriosController::guardarPonderacion');
    $routes->get('criterios/ponderaciones/eliminar/(:num)', 'CriteriosController::eliminarPonderacion/$1');
    $routes->get('criterios/ponderaciones/total/(:num)/(:num)', 'CriteriosController::totalPonderacion/$1/$2');
    $routes->get('criterios/ciclo-parciales/(:num)', 'CriteriosController::getParcialesPorCiclo/$1');
    $routes->get('criterios/ponderaciones/listar/(:num)/(:num)', 'CriteriosController::listarPonderaciones/$1/$2');
});

// Módulo de Profesores
$routes->group('profesor', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Profesor\Dashboard::index');
});

$routes->group('profesor', ['namespace' => 'App\Controllers\Profesor'], function ($routes) {
    $routes->get('grupos/ver/(:num)', 'Grupos::ver/$1');
});
// Módulo de Profesores
$routes->group('profesor', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Profesor\Dashboard::index');
    $routes->get('grupos', 'Profesor\Grupos::index'); // ✅ RUTA PARA LISTAR GRUPOS
});

$routes->group('profesor', ['namespace' => 'App\Controllers\Profesor'], function ($routes) {
    $routes->get('grupos/ver/(:num)', 'Grupos::ver/$1');
});


