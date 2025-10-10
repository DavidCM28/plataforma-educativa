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
    $routes->get('usuarios/plantilla', 'Admin\Usuarios::plantilla');
    $routes->post('usuarios/importar', 'Admin\Usuarios::importar');
    $routes->get('usuarios/descargar-credenciales', 'Admin\Usuarios::descargarCredenciales');
    $routes->get('usuarios-detalles/ver/(:num)', 'Admin\UsuariosDetalles::ver/$1');
    $routes->post('usuarios-detalles/guardar', 'Admin\UsuariosDetalles::guardar');
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
    $routes->get('eliminar/(:num)', 'GruposController::eliminar/$1');
});

// Módulo de Asignaciones
$routes->group('admin/asignaciones', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'AsignacionesController::index');

    // Profesores ↔ Grupo/Materia
    $routes->post('asignar-profesor', 'AsignacionesController::asignarProfesor');
    $routes->get('eliminar-profesor/(:num)', 'AsignacionesController::eliminarProfesor/$1');

    // Alumnos ↔ Grupo
    $routes->post('asignar-alumno', 'AsignacionesController::asignarAlumno');
    $routes->get('eliminar-alumno/(:num)', 'AsignacionesController::eliminarAlumno/$1');
});


