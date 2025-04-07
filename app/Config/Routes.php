<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Candidatos;
use App\Controllers\Usuarios;
use App\Controllers\Empresas;

/**
 * @var RouteCollection $routes
 */

$routes->get('usuarios/foto/(:num)', 'Usuarios::getFoto/$1');
$routes->post('usuarios/guardarfoto/(:num)', 'Usuarios::saveFoto/$1');
$routes->post('candidatos/guardarCV/(:num)', 'Candidatos::saveCV/$1');
$routes->resource('usuarios');
$routes->resource('candidatos');
$routes->resource('empresas');
$routes->resource('sectores');
$routes->resource('ofertas');

$routes->post('usuarios/login', 'Usuarios::login');
