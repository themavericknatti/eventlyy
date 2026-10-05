<?php

namespace App\Controllers;

use Ace\Controller;
use Ace\Request;

class HomeController extends Controller
{
    /**
     * Display the application welcome page.
     */
    public function index(Request $request): string
    {
        return $this->render('home', [
            'appName' => 'Ace PHP MVC Framework',
            'version' => '1.0.0',
            'phpVersion' => PHP_VERSION,
        ]);
    }
}
