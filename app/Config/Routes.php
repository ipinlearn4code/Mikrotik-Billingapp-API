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

// SubscriptionRoute
$routes->post('subscription/create', 'SubscriptionController::create');
$routes->put('subscription/update/(:num)', 'SubscriptionController::update/$1');
$routes->delete('subscription/delete/(:num)', 'SubscriptionController::delete/$1');
$routes->get('subscription/(:num)', 'SubscriptionController::show/$1');
$routes->get('subscription', 'SubscriptionController::index');

// InvoiceRoute
$routes->post('invoice/create', 'InvoiceController::create');
$routes->put('invoice/update/(:num)', 'InvoiceController::update/$1');
$routes->delete('invoice/delete/(:num)', 'InvoiceController::delete/$1');
$routes->get('invoice/(:num)', 'InvoiceController::show/$1');
$routes->get('invoice', 'InvoiceController::index');

// PaymentRoute
$routes->post('payment/create', 'PaymentController::create');
$routes->put('payment/update/(:num)', 'PaymentController::update/$1');
$routes->delete('payment/delete/(:num)', 'PaymentController::delete/$1');
$routes->get('payment/(:num)', 'PaymentController::show/$1');
$routes->get('payment', 'PaymentController::index');

// PlanController
$routes->get('plan', 'PlanController::index');
$routes->get('plan/(:num)', 'PlanController::show/$1');
