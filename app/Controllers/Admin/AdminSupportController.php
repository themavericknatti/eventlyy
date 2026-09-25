<?php

/**
 * AdminSupportController
 *
 * Handles admin management of customer support inquiries and messages.
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

class AdminSupportController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new AdminMiddleware());
    }

    private function ensureTableExists(\PDO $db): void
    {
        $db->exec("
            CREATE TABLE IF NOT EXISTS support_inquiries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                subject VARCHAR(100) NOT NULL DEFAULT 'general',
                message TEXT NOT NULL,
                status ENUM('new', 'read', 'resolved') NOT NULL DEFAULT 'new',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function inquiries(Request $request)
    {
        $db = Application::$app->db->pdo;
        $this->ensureTableExists($db);

        $params = $request->getBody();
        $status = trim($params['status'] ?? 'all');
        $search = trim($params['q'] ?? '');

        $conditions = ["1=1"];
        $binds = [];

        if ($status !== 'all' && in_array($status, ['new', 'read', 'resolved'])) {
            $conditions[] = "status = :status";
            $binds['status'] = $status;
        }

        if (!empty($search)) {
            $conditions[] = "(name LIKE :s1 OR email LIKE :s2 OR subject LIKE :s3 OR message LIKE :s4)";
            $binds['s1'] = "%$search%";
            $binds['s2'] = "%$search%";
            $binds['s3'] = "%$search%";
            $binds['s4'] = "%$search%";
        }

        $whereSql = implode(' AND ', $conditions);

        $stmt = $db->prepare("SELECT * FROM support_inquiries WHERE {$whereSql} ORDER BY created_at DESC");
        $stmt->execute($binds);
        $items = $stmt->fetchAll() ?: [];

        // Count totals
        $countStmt = $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
                SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count
            FROM support_inquiries
        ");
        $counts = $countStmt->fetch() ?: ['total' => 0, 'new_count' => 0, 'read_count' => 0, 'resolved_count' => 0];

        return $this->render('admin/support', [
            'inquiries' => $items,
            'counts' => $counts,
            'currentStatus' => $status,
            'search' => $search
        ]);
    }

    public function postAction(Request $request)
    {
        $db = Application::$app->db->pdo;
        $this->ensureTableExists($db);

        $data = $request->getBody();
        $id = (int)($data['id'] ?? 0);
        $action = $data['action'] ?? '';

        if ($id <= 0) {
            Application::$app->session->setFlash('error', 'Invalid inquiry ID.');
            return redirect('/admin/support');
        }

        if ($action === 'mark_read') {
            $stmt = $db->prepare("UPDATE support_inquiries SET status = 'read' WHERE id = :id");
            $stmt->execute(['id' => $id]);
            Application::$app->session->setFlash('success', 'Inquiry marked as read.');
        } elseif ($action === 'mark_resolved') {
            $stmt = $db->prepare("UPDATE support_inquiries SET status = 'resolved' WHERE id = :id");
            $stmt->execute(['id' => $id]);
            Application::$app->session->setFlash('success', 'Inquiry marked as resolved.');
        } elseif ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM support_inquiries WHERE id = :id");
            $stmt->execute(['id' => $id]);
            Application::$app->session->setFlash('success', 'Inquiry deleted successfully.');
        } else {
            Application::$app->session->setFlash('error', 'Invalid action.');
        }

        return redirect('/admin/support');
    }
}
