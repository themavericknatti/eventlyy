<?php

/**
 * AdminController
 *
 * This controller handles admin-related routes.
 *
 * @package Evently
 * @subpackage Controllers/Admin
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers\Admin;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\AdminMiddleware;
use App\Services\AdminService;

class AdminController extends Controller
{
    protected AdminService $adminService;

    /**
     * AdminController constructor.
     * Registers the AdminMiddleware and initializes AdminService.
     */
    public function __construct()
    {
        $this->registerMiddleware(new AdminMiddleware());
        $this->adminService = new AdminService();
    }

    public function dashboard()
    {
        // Automatically synchronize event auto-completions and pending payout requests
        (new \App\Services\AutoPayoutService())->processAutoPayouts();

        // Fetch dashboard data via AdminService
        $data = $this->adminService->getDashboardData();

        // Render the admin dashboard view with data
        return $this->render('admin/dashboard', $data);
    }
}