<?php

use Ace\Application;
use App\Controllers\HomeController;

/** @var \Ace\Router $router */

// Default Welcome Route
$router->get('/', [HomeController::class, 'index'])->name('home');
