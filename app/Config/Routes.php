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

