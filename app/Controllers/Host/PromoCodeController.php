<?php

namespace App\Controllers\Host;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\HostMiddleware;

class PromoCodeController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new HostMiddleware());
    }

    /**
     * List and manage promo codes for host events
     */
    public function index(Request $request): string
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');

        if ($isAdmin) {
            // Admin: fetch all events with organizer info and all promo codes with host info
            $stmt = $db->query("
                SELECT e.id, e.name, e.organizer_id, u.name as organizer_name 
                FROM events e 
                INNER JOIN users u ON e.organizer_id = u.id 
                ORDER BY e.created_at DESC
            ");
            $events = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $promoStmt = $db->query("
                SELECT p.*, e.name as event_name, e.organizer_id, u.name as host_name, u.email as host_email 
                FROM promo_codes p
                LEFT JOIN events e ON p.event_id = e.id
                LEFT JOIN users u ON (p.host_id = u.id OR (p.host_id IS NULL AND e.organizer_id = u.id))
                ORDER BY p.created_at DESC
            ");
            $promoCodes = $promoStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } else {
            // Host: fetch only owned events and promo codes
            $stmt = $db->prepare("SELECT id, name, organizer_id FROM events WHERE organizer_id = :organizer_id ORDER BY created_at DESC");
            $stmt->execute(['organizer_id' => $user->id]);
            $events = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $eventIds = array_column($events, 'id');
            $promoCodes = [];

            if (!empty($eventIds)) {
                $inClause = implode(',', array_fill(0, count($eventIds), '?'));
                $promoStmt = $db->prepare("
                    SELECT p.*, e.name as event_name 
                    FROM promo_codes p
                    LEFT JOIN events e ON p.event_id = e.id
                    WHERE p.event_id IN ($inClause) OR p.host_id = ?
                    ORDER BY p.created_at DESC
                ");
                $params = $eventIds;
                $params[] = $user->id;
                $promoStmt->execute($params);
                $promoCodes = $promoStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            } else {
                $promoStmt = $db->prepare("
                    SELECT p.*, NULL as event_name 
                    FROM promo_codes p
                    WHERE p.host_id = ?
                    ORDER BY p.created_at DESC
                ");
                $promoStmt->execute([$user->id]);
                $promoCodes = $promoStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            }
        }

        return $this->render('host/promo-codes', [
            'events' => $events,
            'promoCodes' => $promoCodes,
            'isAdmin' => $isAdmin,
            'currentUserId' => $user->id
        ]);
    }

    /**
     * Store new promo code
     */
    public function store(Request $request)
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;
        $data = $request->getBody();
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');

        $code = strtoupper(trim($data['code'] ?? ''));
        $eventId = !empty($data['event_id']) ? (int)$data['event_id'] : null;
        $type = in_array($data['discount_type'] ?? '', ['percentage', 'fixed']) ? $data['discount_type'] : 'percentage';
        $value = (float)($data['discount_value'] ?? 0);
        $maxUses = (int)($data['max_uses'] ?? 100);
        $expiresAt = !empty($data['expires_at']) ? $data['expires_at'] : null;

        if (empty($code) || $value <= 0) {
            Application::$app->session->setFlash('error', 'Please enter a valid coupon code and discount value.');
            return back();
        }

        // Verify event ownership if specific event chosen (unless admin)
        if ($eventId && !$isAdmin) {
            $check = $db->prepare("SELECT id FROM events WHERE id = :id AND organizer_id = :organizer_id");
            $check->execute(['id' => $eventId, 'organizer_id' => $user->id]);
            if (!$check->fetch()) {
                Application::$app->session->setFlash('error', 'Invalid event selection.');
                return back();
            }
        }

        try {
            $stmt = $db->prepare("
                INSERT INTO promo_codes (host_id, event_id, code, discount_type, discount_value, max_uses, expires_at, status)
                VALUES (:host_id, :event_id, :code, :discount_type, :discount_value, :max_uses, :expires_at, 'active')
            ");
            $stmt->execute([
                'host_id' => $user->id,
                'event_id' => $eventId,
                'code' => $code,
                'discount_type' => $type,
                'discount_value' => $value,
                'max_uses' => $maxUses,
                'expires_at' => $expiresAt
            ]);

            Application::$app->session->setFlash('success', "Promo code '{$code}' created successfully!");
        } catch (\Exception $e) {
            Application::$app->session->setFlash('error', 'Failed to create promo code: ' . $e->getMessage());
        }

        return back();
    }

    /**
     * Delete promo code
     */
    public function delete(Request $request)
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;
        $id = (int)($request->getBody()['id'] ?? 0);
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');

        if ($id > 0) {
            if ($isAdmin) {
                $stmt = $db->prepare("DELETE FROM promo_codes WHERE id = :id");
                $stmt->execute(['id' => $id]);
            } else {
                $stmt = $db->prepare("DELETE FROM promo_codes WHERE id = :id AND host_id = :host_id");
                $stmt->execute(['id' => $id, 'host_id' => $user->id]);
            }
            Application::$app->session->setFlash('success', 'Promo code deleted.');
        }

        return back();
    }
}
