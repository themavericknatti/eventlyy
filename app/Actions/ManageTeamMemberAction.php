<?php

namespace App\Actions;

use Ace\Application;

class ManageTeamMemberAction
{
    /**
     * Perform staff management operations.
     */
    public function execute(string $action, array $data): array
    {
        $db = Application::$app->db->pdo;

        if ($action === 'add_member') {
            $name = trim($data['name'] ?? '');
            $email = trim($data['email'] ?? '');

            if (empty($name) || empty($email)) {
                return ['status' => 'error', 'message' => 'Full name and email address are required.'];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['status' => 'error', 'message' => 'Please enter a valid email address.'];
            }

            // Check email uniqueness
            $stmt = $db->prepare("SELECT COUNT(*) FROM event_staff WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                return ['status' => 'error', 'message' => 'A team member with this email already exists.'];
            }

            $stmt = $db->prepare("INSERT INTO event_staff (name, email, status) VALUES (:name, :email, 'active')");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
            ]);
            return ['status' => 'success', 'message' => 'Team member registered successfully.'];

        } elseif ($action === 'assign_member') {
            $memberId = $data['member_id'] ?? $data['staff_id'] ?? '';
            $eventId = $data['event_id'] ?? '';
            $roleId = $data['role_id'] ?? '';

            if (empty($memberId) || empty($eventId) || empty($roleId)) {
                return ['status' => 'error', 'message' => 'Please select staff member, event, and RBAC role.'];
            }

            // Verify staff exists and is active
            $staffStmt = $db->prepare("SELECT status FROM event_staff WHERE id = :id");
            $staffStmt->execute(['id' => $memberId]);
            $staff = $staffStmt->fetch();

            if (!$staff) {
                return ['status' => 'error', 'message' => 'Staff member not found.'];
            }
            if ($staff['status'] === 'inactive') {
                return ['status' => 'error', 'message' => 'Cannot assign an inactive staff member to an event.'];
            }

            // Verify event is active and not completed
            $eventStmt = $db->prepare("SELECT status, date FROM events WHERE id = :id");
            $eventStmt->execute(['id' => $eventId]);
            $targetEvent = $eventStmt->fetch();
            if (!$targetEvent) {
                return ['status' => 'error', 'message' => 'Selected event not found.'];
            }
            $eventDate = !empty($targetEvent['date']) ? strtotime($targetEvent['date']) : 0;
            $liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
            if (in_array($targetEvent['status'], ['completed', 'past']) || ($eventDate && time() > $liveWindowEnd)) {
                return ['status' => 'error', 'message' => 'Cannot assign team members to a completed event.'];
            }

            // Verify role exists and is not admin, super-admin, or general user
            $roleStmt = $db->prepare("SELECT slug FROM roles WHERE id = :id");
            $roleStmt->execute(['id' => $roleId]);
            $roleSlug = $roleStmt->fetchColumn();
            if (!$roleSlug || in_array($roleSlug, ['admin', 'super-admin', 'user'])) {
                return ['status' => 'error', 'message' => 'Invalid or restricted role selected. Admin and general User roles cannot be assigned to event staff.'];
            }

            // Insert or update assignment
            $stmt = $db->prepare("
                INSERT INTO event_staff_assignments (staff_id, event_id, role_id)
                VALUES (:staff_id, :event_id, :role_id)
                ON DUPLICATE KEY UPDATE role_id = VALUES(role_id), assigned_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([
                'staff_id' => $memberId,
                'event_id' => $eventId,
                'role_id' => $roleId,
            ]);

            return ['status' => 'success', 'message' => 'Staff member assigned to event successfully.'];

        } elseif ($action === 'unassign_member') {
            $memberId = $data['member_id'] ?? $data['staff_id'] ?? '';
            $eventId = $data['event_id'] ?? '';
            $assignmentId = $data['assignment_id'] ?? '';

            if (!empty($assignmentId)) {
                $stmt = $db->prepare("DELETE FROM event_staff_assignments WHERE id = :id");
                $stmt->execute(['id' => $assignmentId]);
                return ['status' => 'success', 'message' => 'Event assignment removed.'];
            }

            if (!empty($memberId) && !empty($eventId)) {
                $stmt = $db->prepare("DELETE FROM event_staff_assignments WHERE staff_id = :staff_id AND event_id = :event_id");
                $stmt->execute([
                    'staff_id' => $memberId,
                    'event_id' => $eventId,
                ]);
                return ['status' => 'success', 'message' => 'Staff member unassigned from event.'];
            }

            return ['status' => 'error', 'message' => 'Missing member or event specification.'];

        } elseif ($action === 'remove_member') {
            $memberId = $data['member_id'] ?? $data['staff_id'] ?? '';

            if (empty($memberId)) {
                return ['status' => 'error', 'message' => 'Member ID is missing.'];
            }

            $stmt = $db->prepare("DELETE FROM event_staff WHERE id = :id");
            $stmt->execute(['id' => $memberId]);
            return ['status' => 'success', 'message' => 'Team member removed from registry.'];

        } elseif ($action === 'toggle_status') {
            $memberId = $data['member_id'] ?? $data['staff_id'] ?? '';

            if (empty($memberId)) {
                return ['status' => 'error', 'message' => 'Member ID is missing.'];
            }

            $stmt = $db->prepare("SELECT status FROM event_staff WHERE id = :id");
            $stmt->execute(['id' => $memberId]);
            $currentStatus = $stmt->fetchColumn();

            if (!$currentStatus) {
                return ['status' => 'error', 'message' => 'Staff member not found.'];
            }

            $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';
            $updateStmt = $db->prepare("UPDATE event_staff SET status = :status WHERE id = :id");
            $updateStmt->execute(['status' => $newStatus, 'id' => $memberId]);

            return [
                'status' => 'success',
                'message' => "Staff status updated to {$newStatus}.",
                'new_status' => $newStatus
            ];
        }

        return ['status' => 'error', 'message' => 'Invalid team action.'];
    }
}
