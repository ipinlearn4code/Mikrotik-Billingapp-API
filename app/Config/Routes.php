<?php

use App\Controllers\PlanController;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');

$routes->post('auth/register', 'AuthController::register');
$routes->post('auth/login', 'AuthController::login');
$routes->post('auth/logout', 'AuthController::logout');

// Client routes (protected by tokenAuth filter)
$routes->group(
    'client',
    ['filter' => 'tokenAuth'],
    function ($routes) {

        $routes->get('/', 'ClientController::index'); //Read all
        $routes->get('(:num)', 'ClientController::getClientDetails/$1'); //Read one with all details
        $routes->post('/', 'ClientController::createClient'); //Create   
        $routes->put('(:num)', 'ClientController::update/$1'); //Update
        $routes->delete('(:num)', 'ClientController::delete/$1'); //Delete
        $routes->get('search', 'ClientController::searchClient'); //Search by name, ppp_secret_name, or phone_number
        $routes->add('(:segment)/connection/(:segment)', 'ClientController::toggleClientStatus/$1/$2');

        $routes->get('(:num)/subscriptions', 'ClientController::getClientSubscriptions/$1');
    }
);

$routes->group(
    'subscription',
    ['filter' => 'tokenAuth'],
    function ($routes) {
        $routes->get('/', 'SubscriptionController::index');
        $routes->get('(:num)', 'SubscriptionController::show/$1');
        $routes->post('/', 'SubscriptionController::create'); //add subscription for new client
        $routes->put('(:num)', 'SubscriptionController::update/$1');
        $routes->delete('(:num)', 'SubscriptionController::delete/$1');
    }
);


$routes->group(
    'invoice',
    ['filter' => 'tokenAuth'],
    function ($routes) {
        $routes->get('/', 'InvoiceController::index');
        $routes->get('(:num)', 'InvoiceController::show/$1');
        $routes->get('auto-generate', 'InvoiceController::generateAutoInvoices');
        $routes->post('/', 'InvoiceController::create'); //create
        $routes->put('(:num)', 'InvoiceController::update/$1');
        $routes->delete('delete/(:num)', 'InvoiceController::delete/$1');

    }
);

$routes->group(
    'plan',
    ['filter' => 'tokenAuth'],
    function ($routes) {
        $routes->get('/', 'PlanController::index'); //Read all
        $routes->get('(:num)/details', 'PlanController::getPlanDetails/$1'); //Read one
        $routes->post('/', 'PlanController::createPlan'); //Create
        $routes->put('(:num)', 'PlanController::update/$1'); //Update
        $routes->delete('(:num)', 'PlanController::delete/$1'); //Delete
    }
);