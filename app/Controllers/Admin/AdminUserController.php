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

        // Handle permanent record purge & NDPA erasure
        if ($action === 'delete_user_data') {
            $force = !empty($data['force']);
            $outcome = $this->userService->deleteUserData($userId, $user->id, $force);
            Application::$app->session->setFlash($outcome['status'], $outcome['message']);
            return redirect('/admin/users');
        }

        // Execute standard action via ManageUserStatusAction
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

    /**
     * Download comprehensive user data dossier as a JSON file.
     * Complies with NDPA 2023 & GDPR Right of Access / Data Subject Access Request.
     */
    public function exportUserData(Request $request)
    {
        $userId = $request->getBody()['id'] ?? $request->getString('id');
        if (!$userId) {
            Application::$app->session->setFlash('error', 'User ID is missing for data export.');
            return redirect('/admin/users');
        }

        $dossier = $this->userService->getFullUserData($userId);
        if (!$dossier) {
            Application::$app->session->setFlash('error', 'User not found for data export.');
            return redirect('/admin/users');
        }

        $userEmail = $dossier['user']['email'] ?? 'user';
        $safeEmail = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $userEmail);
        $filename = "eventlyy-user-data-export-{$userId}-{$safeEmail}-" . date('Ymd-His') . ".json";

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo json_encode($dossier, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Return comprehensive user data dossier as JSON for inspection in admin UI.
     */
    public function userDossier(Request $request)
    {
        $userId = $request->getBody()['id'] ?? $request->getString('id');
        if (!$userId) {
            return Application::$app->response->json(['error' => 'User ID is missing.'], 400);
        }

        $dossier = $this->userService->getFullUserData($userId);
        if (!$dossier) {
            return Application::$app->response->json(['error' => 'User not found.'], 404);
        }

        return Application::$app->response->json($dossier);
    }
}
