<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
// LOGIN
$routes->get('/login', 'Auth::login');
$routes->post('/auth/doLogin', 'Auth::doLogin');

// Forgot Password
$routes->get('/forgot-password', 'Auth::forgotPassword');
$routes->post('/auth/sendResetLink', 'Auth::sendResetLink');
$routes->get('/reset-password/(:any)', 'Auth::resetPassword/$1');
$routes->post('/auth/doResetPassword', 'Auth::doResetPassword');

// REGISTER
$routes->get('/register', 'Auth::register');
$routes->post('/auth/doRegister', 'Auth::doRegister');

// LOGOUT
$routes->get('/logout', 'Auth::logout');
