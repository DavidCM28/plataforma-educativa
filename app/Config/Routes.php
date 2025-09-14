<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('contacto', 'Home::contacto');
$routes->post('contacto/guardar', 'Contacto::guardar');
$routes->post('contacto/enviar', 'Contacto::enviar');