<?php

/**
 * AdminUserController
 *
 * This controller handles admin user-related routes.
 *
 * @package Evently
 * @subpackage Controllers/AdminUser
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers\Admin;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\AdminMiddleware;
use App\Services\AdminUserService;
use App\Actions\ManageUserStatusAction;

class AdminUserController extends Controller
{
    protected AdminUserService $userService;
    protected ManageUserStatusAction $userStatusAction;

    /**
     * AdminUserController constructor.
     * Registers the AdminMiddleware and initializes user service and action.
     */
    public function __construct()
    {
        $this->registerMiddleware(new AdminMiddleware());
        $this->userService = new AdminUserService();
        $this->userStatusAction = new ManageUserStatusAction();
    }

    public function users(Request $request)
    {
        // Query stats via AdminUserService
        $stats = $this->userService->getUserStats();

        // Get filter inputs
        $search = $request->getBody()['q'] ?? '';
        $role = $request->getBody()['role'] ?? '';
        $status = $request->getBody()['status'] ?? '';
        $verification = $request->getBody()['verification'] ?? '';
        $page = (int)($request->getBody()['page'] ?? 1);
        if ($page < 1) $page = 1;
        $limit = 10;

        // Fetch users list via AdminUserService
        $paginated = $this->userService->getFilteredUsers($search, $role, $status, $verification, $page, $limit);
        $users = $paginated['items'];
        $totalPages = $paginated['totalPages'];

        return $this->render('admin/users', array_merge($stats, [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'verification' => $verification,
            'page' => $page,
            'totalPages' => $totalPages
        ]));
    }

    public function postAction(Request $request)
    {
        $user = Application::$app->user;
        $data = $request->getBody();
        $action = $data['action'] ?? '';
        $userId = $data['user_id'] ?? '';

        if (!$userId) {
            Application::$app->session->setFlash('error', 'User ID is missing.');
            return redirect('/admin/users');
        }

        // Execute action via ManageUserStatusAction
        $outcome = $this->userStatusAction->execute($action, $userId, $user->id);

        Application::$app->session->setFlash($outcome['status'], $outcome['message']);

        return redirect('/admin/users');
    }

    public function userDetail(Request $request)
    {
        $userId = $request->getBody()['id'] ?? '';
        if (!$userId) {
            return Application::$app->response->json(['error' => 'User ID is missing.'], 400);
        }

        $detail = $this->userService->getUserDetail($userId);
        if (!$detail) {
            return Application::$app->response->json(['error' => 'User not found.'], 404);
        }

        return Application::$app->response->json($detail);
    }
}