<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Candidatos;
use App\Controllers\Usuarios;
use App\Controllers\Empresas;

/**
 * @var RouteCollection $routes
 */

$routes->resource('usuarios');
$routes->resource('candidatos');
$routes->resource('empresas');
$routes->resource('sectores');
$routes->resource('ofertas');

$routes->post('usuarios/login', 'Usuarios::login');
