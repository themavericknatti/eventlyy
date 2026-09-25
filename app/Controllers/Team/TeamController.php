<?php

/**
 * TeamController
 *
 * Handles staff workstation entry, authentication, and role-specific task pages.
 *
 * @package Evently
 * @subpackage Controllers/Team
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers\Team;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\TeamMiddleware;
use App\Services\TeamService;

class TeamController extends Controller
{
    protected TeamService $teamService;

    public function __construct()
    {
        $this->teamService = new TeamService();
        $this->registerMiddleware(new TeamMiddleware([
            'securityScanner',
            'verifyTicketApi',
            'coordinatorDashboard',
            'supportDesk',
            'supportAction',
            'techOps',
            'financeDesk',
            'usherGuide',
            'issueGateTicket',
            'toggleStaffCheckin',
            'switchEvent',
        ]));
    }

    /**
     * Staff Login & Workstation Entry View.
     */
    public function loginView(Request $request)
    {
        $sessionStaff = Application::$app->session->get('team_staff');
        $assignments = [];

        if ($sessionStaff) {
            $assignments = $this->teamService->getStaffAssignments($sessionStaff['id']);
        }

        return $this->render('teams/login', [
            'staff' => $sessionStaff,
            'assignments' => $assignments,
        ]);
    }

    /**
     * Process Staff Login / Role Entry.
     */
    public function loginPost(Request $request)
    {
        $data = $request->getBody();
        $email = trim($data['email'] ?? '');
        $staffId = $data['staff_id'] ?? null;
        $eventId = $data['event_id'] ?? null;
        $targetPage = $data['target_page'] ?? null;

        $sessionStaff = Application::$app->session->get('team_staff');

        $rateLimiter = new \App\Services\RateLimiterService();
        $ip = \App\Services\RateLimiterService::getClientIp();

        if (!empty($email)) {
            $remainingSeconds = $rateLimiter->getRemainingLockoutSeconds($ip, $email);
            if ($remainingSeconds > 0) {
                $minutes = (int)ceil($remainingSeconds / 60);
                Application::$app->session->setFlash('error', "Too many failed login attempts. Temporarily locked out. Please try again in {$minutes} minute(s).");
                return redirect('/teams/login');
            }

            $staff = $this->teamService->getStaffByEmail($email);
            if (!$staff) {
                $hitResult = $rateLimiter->hit($ip, $email);
                if ($hitResult['locked']) {
                    Application::$app->session->setFlash('error', 'Too many failed login attempts. Temporarily locked out for 15 minutes.');
                } else {
                    $attemptsLeft = \App\Services\RateLimiterService::MAX_ATTEMPTS - $hitResult['attempts'];
                    Application::$app->session->setFlash('error', "Staff member email not found in registry. ({$attemptsLeft} attempt(s) remaining)");
                }
                return redirect('/teams/login');
            }
            if ($staff['status'] === 'inactive') {
                Application::$app->session->setFlash('error', 'Your staff account is currently inactive.');
                return redirect('/teams/login');
            }
            $rateLimiter->clear($ip, $email);
            $staffId = $staff['id'];
        } elseif ($sessionStaff) {
            $staff = $sessionStaff;
            $staffId = $staff['id'];
        } elseif ($staffId) {
            $db = Application::$app->db->pdo;
            $stmt = $db->prepare("SELECT * FROM event_staff WHERE id = :id AND status = 'active'");
            $stmt->execute(['id' => $staffId]);
            $staff = $stmt->fetch();
        } else {
            Application::$app->session->setFlash('error', 'Please enter your registered staff email address to log in.');
            return redirect('/teams/login');
        }

        // Get assignments for this staff member
        $assignments = $this->teamService->getStaffAssignments($staffId);

        if (empty($assignments)) {
            Application::$app->session->setFlash('error', 'No active event assignments found for ' . e($staff['name']) . '.');
            return redirect('/teams/login');
        }

        // Match selected event or pick first
        $selectedAssignment = $assignments[0];
        if ($eventId) {
            foreach ($assignments as $asg) {
                if ($asg['event_id'] == $eventId) {
                    $selectedAssignment = $asg;
                    break;
                }
            }
        }

        // Audit staff login
        $this->teamService->updateStaffLoginAudit($staffId);

        // Store workspace session
        Application::$app->session->set('team_staff', $staff);
        Application::$app->session->set('team_event', [
            'id' => $selectedAssignment['event_id'],
            'name' => $selectedAssignment['event_name'],
            'date' => $selectedAssignment['event_date'],
            'location' => $selectedAssignment['event_location'],
        ]);
        Application::$app->session->set('team_role', [
            'id' => $selectedAssignment['role_id'],
            'name' => $selectedAssignment['role_name'],
            'slug' => $selectedAssignment['role_slug'],
        ]);

        Application::$app->session->setFlash('success', 'Welcome to your workstation, ' . e($staff['name']) . '!');

        // Redirect to appropriate task workstation
        if ($targetPage) {
            return redirect('/teams/' . ltrim($targetPage, '/'));
        }

        $roleSlug = strtolower($selectedAssignment['role_slug']);
        if (str_contains($roleSlug, 'usher') || str_contains($roleSlug, 'floor') || str_contains($roleSlug, 'guide')) {
            return redirect('/teams/usher-guide');
        } elseif (str_contains($roleSlug, 'scanner') || str_contains($roleSlug, 'security')) {
            return redirect('/teams/security-scanner');
        } elseif (str_contains($roleSlug, 'support') || str_contains($roleSlug, 'customer')) {
            return redirect('/teams/support-desk');
        } elseif (str_contains($roleSlug, 'tech') || str_contains($roleSlug, 'technical')) {
            return redirect('/teams/tech-ops');
        } elseif (str_contains($roleSlug, 'finance') || str_contains($roleSlug, 'payout')) {
            return redirect('/teams/finance-desk');
        } else {
            return redirect('/teams/coordinator-dashboard');
        }
    }

    /**
     * Staff Logout.
     */
    public function logout(Request $request)
    {
        $staff = Application::$app->session->get('team_staff');
        if ($staff && !empty($staff['id'])) {
            $this->teamService->updateStaffLogoutAudit((int)$staff['id']);
        }

        Application::$app->session->remove('team_staff');
        Application::$app->session->remove('team_event');
        Application::$app->session->remove('team_role');
        Application::$app->session->setFlash('success', 'Logged out of staff workstation.');
        return redirect('/teams/login');
    }

    /**
     * Switch Active Event Assignment.
     */
    public function switchEvent(Request $request)
    {
        $context = $this->ensureStaffContext();
        if (!$context) return redirect('/teams/login');

        $data = $request->getBody();
        $eventId = $data['event_id'] ?? null;

        if ($eventId) {
            $assignments = $this->teamService->getStaffAssignments($context['staff']['id']);
            foreach ($assignments as $asg) {
                if ($asg['event_id'] == $eventId) {
                    Application::$app->session->set('team_event', [
                        'id' => $asg['event_id'],
                        'name' => $asg['event_name'],
                        'date' => $asg['event_date'],
                        'location' => $asg['event_location'],
                    ]);
                    Application::$app->session->set('team_role', [
                        'id' => $asg['role_id'],
                        'name' => $asg['role_name'],
                        'slug' => $asg['role_slug'],
                    ]);
                    Application::$app->session->setFlash('success', 'Switched active workspace to ' . e($asg['event_name']));

                    $roleSlug = strtolower($asg['role_slug']);
                    if (str_contains($roleSlug, 'usher') || str_contains($roleSlug, 'floor') || str_contains($roleSlug, 'guide')) {
                        return redirect('/teams/usher-guide');
                    } elseif (str_contains($roleSlug, 'scanner') || str_contains($roleSlug, 'security')) {
                        return redirect('/teams/security-scanner');
                    } elseif (str_contains($roleSlug, 'support') || str_contains($roleSlug, 'customer')) {
                        return redirect('/teams/support-desk');
                    } elseif (str_contains($roleSlug, 'tech') || str_contains($roleSlug, 'technical')) {
                        return redirect('/teams/tech-ops');
                    } elseif (str_contains($roleSlug, 'finance') || str_contains($roleSlug, 'payout')) {
                        return redirect('/teams/finance-desk');
                    } else {
                        return redirect('/teams/coordinator-dashboard');
                    }
                }
            }
        }

        return redirect('/teams/login');
    }

    /**
     * Gate Security Scanner Workstation Task View.
     */
    public function securityScanner(Request $request)
    {
        $context = $this->ensureStaffContext();
        if (!$context) return redirect('/teams/login');

        $stats = $this->teamService->getCheckinStats($context['event']['id']);
        $assignments = $this->teamService->getStaffAssignments($context['staff']['id']);

        return $this->render('teams/security-scanner', array_merge($context, [
            'stats' => $stats,
            'assignments' => $assignments,
        ]));
    }

    /**
     * API: Verify & Check in Ticket Code.
     */
    public function verifyTicketApi(Request $request)
    {
        $context = $this->ensureStaffContext();
        if (!$context) {
            return Application::$app->response->json(['success' => false, 'message' => 'Staff session expired. Please log in.'], 401);
        }

        $data = $request->getBody();
        $code = trim($data['code'] ?? $data['ticket_code'] ?? '');

        if (empty($code)) {
            return Application::$app->response->json(['success' => false, 'message' => 'Ticket code is required.'], 400);
        }

        $result = $this->teamService->verifyAndCheckInTicket($code, $context['event']['id'], $context['staff']['id']);

        // Check if JSON request
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (str_contains($accept, 'application/json')) {
            return Application::$app->response->json($result);
        }

        if ($result['success']) {
            Application::$app->session->setFlash('success', $result['message']);
        } else {
            Application::$app->session->setFlash('error', $result['message']);
        }

        return redirect('/teams/security-scanner');
    }

    /**
     * Event Organiser (Box Office/Finance) Dashboard Workstation Task View.
     */
    public function coordinatorDashboard(Request $request)
    {
        $context = $this->ensureStaffContext();
        if (!$context) return redirect('/teams/login');

        $db = Application::$app->db->pdo;
        $eventId = (int)$context['event']['id'];

        $stats = $this->teamService->getCheckinStats($eventId);
        $metrics = $this->teamService->getCoordinatorMetrics($eventId);
        $staffRoster = $this->teamService->getAssignedStaffRoster($eventId);

        // Fetch all ticket types for gate box office sales
        $ttStmt = $db->prepare("
            SELECT tt.*, 
                   (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.status IN ('Paid', 'Comped')) as sold_count
            FROM ticket_types tt 
            WHERE tt.event_id = :event_id 
            ORDER BY tt.price ASC
        ");
        $ttStmt->execute(['event_id' => $eventId]);
        $allTicketTypes = $ttStmt->fetchAll() ?: [];

        return $this->render('teams/coordinator-dashboard', array_merge($context, [
            'stats' => $stats,
            'ticket_types' => $metrics['ticket_types'],
            'recent_activity' => $metrics['recent_activity'],
            'staff_roster' => $staffRoster,
            'all_ticket_types' => $allTicketTypes,
        ]));
    }

    /**
     * Usher / Floor Guide Workstation Task View.
     */
    public function usherGuide(Request $request)
    {
        $context = $this->ensureStaffContext();
        if (!$context) return redirect('/teams/login');

        $queryParams = $request->getBody();
        $search = trim($queryParams['q'] ?? '');

        $usherData = $this->teamService->getUsherFloorGuideData((int)$context['event']['id'], $search);
        $stats = $this->teamService->getCheckinStats((int)$context['event']['id']);
        $assignments = $this->teamService->getStaffAssignments((int)$context['staff']['id']);

        return $this->render('teams/usher-guide', array_merge($context, [
            'sections' => $usherData['sections'],
            'attendees' => $usherData['attendees'],
            'total_seated' => $usherData['total_seated'],
            'total_expected' => $usherData['total_expected'],
            'search' => $search,
            'stats' => $stats,
            'assignments' => $assignments,
        ]));
    }

    /**
     * API / Action: Issue tickets at the gate / box office (Cash, POS, Transfer, Comp).
     */
    public function issueGateTicket(Request $request)
    {
        $context = $this->ensureStaffContext();
        if (!$context) {
            return Application::$app->response->json(['success' => false, 'message' => 'Staff session expired.'], 401);
        }

        $data = $request->getBody();
        $ticketTypeId = (int)($data['ticket_type_id'] ?? 0);
        $name = trim($data['attendee_name'] ?? '');
        $email = trim($data['attendee_email'] ?? '');
        $qty = max(1, (int)($data['quantity'] ?? 1));
        $method = trim($data['payment_method'] ?? 'cash');
        $notes = trim($data['notes'] ?? '');
        $markCheckedIn = !empty($data['mark_checked_in']) && $data['mark_checked_in'] == '1';

        $res = $this->teamService->issueGateTicket(
            (int)$context['event']['id'],
            $ticketTypeId,
            $name,
            $email,
            $qty,
            $method,
            $markCheckedIn,
            $notes,
            (int)$context['staff']['id']
        );

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (str_contains($accept, 'application/json') || $request->isPost()) {
            return Application::$app->response->json($res);
        }

        Application::$app->session->setFlash($res['success'] ? 'success' : 'error', $res['message']);
        return redirect('/teams/coordinator-dashboard');
    }

    /**
     * API / Action: Toggle staff check-in status (by Event Organiser).
     */
    public function toggleStaffCheckin(Request $request)
    {
        $context = $this->ensureStaffContext();
        if (!$context) {
            return Application::$app->response->json(['success' => false, 'message' => 'Staff session expired.'], 401);
        }

        $data = $request->getBody();
        $assignmentId = (int)($data['assignment_id'] ?? 0);

        $res = $this->teamService->toggleStaffCheckin($assignmentId, (int)$context['staff']['id']);
        return Application::$app->response->json($res);
    }

    /**
     * Guest Support Desk Workstation Task View.
     */
    public function supportDesk(Request $request)
    {
        $context = $this->ensureStaffContext();
        if (!$context) return redirect('/teams/login');

        $queryParams = $request->getBody();
        $query = trim($queryParams['q'] ?? '');

        $tickets = $this->teamService->searchAttendees($context['event']['id'], $query);
        $stats = $this->teamService->getCheckinStats($context['event']['id']);

        $otherEventMatch = null;
        if (empty($tickets) && !empty($query)) {
            $otherEventMatch = $this->teamService->findTicketInOtherAssignedEvents($context['staff']['id'], $context['event']['id'], $query);
        }

        $assignments = $this->teamService->getStaffAssignments($context['staff']['id']);

        $db = Application::$app->db->pdo;
        $ttStmt = $db->prepare("SELECT * FROM ticket_types WHERE event_id = :event_id ORDER BY price ASC");
        $ttStmt->execute(['event_id' => (int)$context['event']['id']]);
        $allTicketTypes = $ttStmt->fetchAll() ?: [];

        return $this->render('teams/support-desk', array_merge($context, [
            'tickets' => $tickets,
            'query' => $query,
            'stats' => $stats,
            'assignments' => $assignments,
            'other_match' => $otherEventMatch,
            'all_ticket_types' => $allTicketTypes,
        ]));
    }

    /**
     * Support Desk Action: Override check-in status.
     */
    public function supportAction(Request $request)
    {
        $context = $this->ensureStaffContext();
        if (!$context) return redirect('/teams/login');

        $data = $request->getBody();
        $ticketId = $data['ticket_id'] ?? '';
        $action = $data['action'] ?? '';

        if ($action === 'override_checkin') {
            $newState = ($data['check_in'] ?? '1') === '1';
            $res = $this->teamService->overrideCheckinStatus($ticketId, $newState, $context['staff']['id']);
            Application::$app->session->setFlash($res['success'] ? 'success' : 'error', $res['message']);
        }

        return redirect('/teams/support-desk');
    }

    /**
     * Technical Operations Workstation Task View.
     */
    public function techOps(Request $request)
    {
        $context = $this->ensureStaffContext();
        if (!$context) return redirect('/teams/login');

        $diagnostics = $this->teamService->getTechOpsDiagnostics($context['event']['id']);
        $stats = $this->teamService->getCheckinStats($context['event']['id']);

        return $this->render('teams/tech-ops', array_merge($context, [
            'team_members' => $diagnostics['team_members'] ?? [],
            'network' => $diagnostics['network'] ?? [],
            'stats' => $stats,
        ]));
    }

    /**
     * Finance Manager Workstation Task View.
     */
    public function financeDesk(Request $request)
    {
        $context = $this->ensureStaffContext();
        if (!$context) return redirect('/teams/login');

        $financeMetrics = $this->teamService->getFinanceMetrics($context['event']['id']);
        $stats = $this->teamService->getCheckinStats($context['event']['id']);

        return $this->render('teams/finance-desk', array_merge($context, [
            'finance' => $financeMetrics,
            'stats' => $stats,
        ]));
    }

    /**
     * Helper to retrieve active staff session & context.
     */
    protected function ensureStaffContext(): ?array
    {
        $staff = Application::$app->session->get('team_staff');
        $event = Application::$app->session->get('team_event');
        $role = Application::$app->session->get('team_role');

        if (!$staff || !$event) {
            return null;
        }

        // Touch last_active_at timestamp for online session audit
        if (!empty($staff['id'])) {
            $this->teamService->touchStaffActive((int)$staff['id']);
        }

        return [
            'staff' => $staff,
            'event' => $event,
            'role' => $role,
        ];
    }
}
