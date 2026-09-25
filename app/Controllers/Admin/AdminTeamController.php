<?php

/**
 * AdminTeamController
 *
 * This controller handles admin team-related routes.
 *
 * @package Evently
 * @subpackage Controllers/AdminTeam
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers\Admin;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\AdminMiddleware;
use App\Services\AdminTeamService;
use App\Actions\ManageTeamMemberAction;

class AdminTeamController extends Controller
{
    protected AdminTeamService $teamService;
    protected ManageTeamMemberAction $teamMemberAction;

    /**
     * AdminTeamController constructor.
     * Registers the AdminMiddleware and initializes team service and action.
     */
    public function __construct()
    {
        $this->registerMiddleware(new AdminMiddleware());
        $this->teamService = new AdminTeamService();
        $this->teamMemberAction = new ManageTeamMemberAction();
    }

    public function teams(Request $request)
    {
        $queryParams = $request->getBody();
        $page = (int)($queryParams['page'] ?? 1);
        $search = trim($queryParams['search'] ?? '');
        $roleId = trim($queryParams['role'] ?? '');
        $status = trim($queryParams['status'] ?? '');
        $eventId = trim($queryParams['event'] ?? '');

        // Fetch stats via AdminTeamService
        $stats = $this->teamService->getStaffStats();

        // Fetch paginated staff members
        $paginated = $this->teamService->getStaffPaginated($page, 10, $search, $roleId, $status, $eventId);

        // Fetch upcoming events & RBAC roles for forms
        $events = $this->teamService->getAllEvents();
        $roles = $this->teamService->getAllRoles();
        $pendingStaffRequests = $this->teamService->getPendingStaffRequests();

        return $this->render('admin/teams', array_merge($stats, [
            'teams' => $paginated['staff'],
            'total_items' => $paginated['total_items'],
            'current_page' => $paginated['current_page'],
            'total_pages' => $paginated['total_pages'],
            'per_page' => $paginated['per_page'],
            'events' => $events,
            'roles' => $roles,
            'pending_staff_requests' => $pendingStaffRequests,
            'search' => $search,
            'role' => $roleId,
            'status' => $status,
            'event_filter' => $eventId,
        ]));
    }

    public function staffDetail(Request $request)
    {
        $queryParams = $request->getBody();
        $staffId = $queryParams['id'] ?? $queryParams['staff_id'] ?? null;

        if (!$staffId) {
            return Application::$app->response->json(['success' => false, 'message' => 'Staff ID is required.'], 400);
        }

        $detail = $this->teamService->getStaffDetail($staffId);

        if (!$detail) {
            return Application::$app->response->json(['success' => false, 'message' => 'Staff member not found.'], 404);
        }

        return Application::$app->response->json([
            'success' => true,
            'data' => $detail
        ]);
    }

    /**
     * Export matching staff members and assignments as CSV.
     */
    public function exportCsv(Request $request)
    {
        $queryParams = $request->getBody();
        $search = trim($queryParams['search'] ?? '');
        $roleId = trim($queryParams['role'] ?? '');
        $status = trim($queryParams['status'] ?? '');
        $eventId = trim($queryParams['event'] ?? '');

        $staffList = $this->teamService->getAllStaffForExport($search, $roleId, $status, $eventId);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="evently_staff_team_export_' . date('Y-m-d_H-i') . '.csv"');

        $output = fopen('php://output', 'w');
        // Write UTF-8 BOM for Microsoft Excel compatibility
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'Staff ID',
            'Full Name',
            'Email Address',
            'Status',
            'Active Event Assignments Count',
            'Assignments Detail',
            'Registered Date'
        ]);

        foreach ($staffList as $s) {
            fputcsv($output, [
                '#' . $s['id'],
                $s['name'] ?? 'N/A',
                $s['email'] ?? 'N/A',
                ucfirst($s['status'] ?? 'Active'),
                (int)($s['assignment_count'] ?? 0),
                $s['assignments_summary'] ?: 'None (Unassigned)',
                !empty($s['created_at']) ? date('Y-m-d H:i:s', strtotime($s['created_at'])) : 'N/A'
            ]);
        }

        fclose($output);
        exit;
    }

    public function postAction(Request $request)
    {
        $data = $request->getBody();
        $action = $data['action'] ?? '';

        // Execute action via ManageTeamMemberAction
        $outcome = $this->teamMemberAction->execute($action, $data);

        // Check if JSON request
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_ACCEPT'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            return Application::$app->response->json([
                'success' => ($outcome['status'] === 'success'),
                'message' => $outcome['message'],
                'data' => $outcome
            ], ($outcome['status'] === 'success' ? 200 : 400));
        }

        Application::$app->session->setFlash($outcome['status'], $outcome['message']);

        return redirect('/admin/teams');
    }
}