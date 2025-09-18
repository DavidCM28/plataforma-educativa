<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('contacto', 'Home::contacto');
$routes->post('api/contacto/guardar', 'Contacto::guardar');
$routes->post('contacto/enviar', 'Contacto::enviar');
$routes->get('auth/login', 'Auth::login');
$routes->post('auth/login', 'Auth::doLogin');
$routes->get('auth/logout', 'Auth::logout');
