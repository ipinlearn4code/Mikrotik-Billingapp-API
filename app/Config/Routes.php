<?php

use App\Controllers\PlanController;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// UserRoute
// $routes->get('user/(:num)', 'UserController::show/$1');
$routes->get('user/(:num)/details', 'UserController::getUserDetails/$1');
$routes->post('user/register', 'UserController::createUser');
$routes->get('user', 'UserController::index');

$routes->put('user/update/(:num)', 'UserController::update/$1');
$routes->delete('user/delete/(:num)', 'UserController::delete/$1');