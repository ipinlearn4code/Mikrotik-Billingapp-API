<?php

use App\Controllers\PlanController;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');

// clientRoute
// $routes->get('client/(:num)', 'clientController::show/$1');
// $routes->get('client/(:num)/details', 'ClientController::getClientDetails/$1');
// $routes->post('client/register', 'ClientController::createClient');
// $routes->get('client', 'ClientController::index');

// $routes->put('client/update/(:num)', 'clientController::update/$1');
// $routes->delete('client/delete/(:num)', 'clientController::delete/$1');

// $routes->post('auth/register', 'AuthController::register');
// $routes->post('auth/login', 'AuthController::login');
// $routes->post('auth/logout', 'AuthController::logout');
// Authentication routes (no authentication required)
$routes->post('auth/register', 'AuthController::register');
$routes->post('auth/login', 'AuthController::login');
$routes->post('auth/logout', 'AuthController::logout');

// Client routes (protected by tokenAuth filter)
$routes->group(
    'client',
    ['filter' => 'tokenAuth'],
    function ($routes) {

        $routes->get('/', 'ClientController::index'); //Read all
        $routes->get('(:num)/details', 'ClientController::getClientDetails/$1'); //Read one with all details
        $routes->post('register', 'ClientController::createClient'); //Create
        $routes->put('update/(:num)', 'ClientController::update/$1'); //Update
        $routes->delete('delete/(:num)', 'ClientController::delete/$1'); //Delete
        $routes->get('search', 'ClientController::searchClient'); //Search by name, ppp_secret_name, or phone_number
    }
);
$routes->group(
    'plan',
    ['filter' => 'tokenAuth'],
    function ($routes) {
        $routes->get('/', 'PlanController::index');
        $routes->get('(:num)/details', 'PlanController::getPlanDetails/$1');
        $routes->post('add', 'PlanController::createPlan');
        $routes->put('update/(:num)', 'PlanController::update/$1');
        $routes->delete('delete/(:num)', 'PlanController::delete/$1');
    }
);

$routes->group(
    'invoice',
    ['filter' => 'tokenAuth'],
    function ($routes) {
        $routes->get('/', 'InvoiceController::index');
        $routes->get('(:num)/details', 'InvoiceController::getInvoiceDetails/$1');
        $routes->post('add', 'InvoiceController::createInvoice');
        $routes->put('update/(:num)', 'InvoiceController::update/$1');
        $routes->delete('delete/(:num)', 'InvoiceController::delete/$1');
    }
);

