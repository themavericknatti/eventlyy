<?php

namespace App\Models;

use Ace\Model;
use Ace\Application;

class LayawayCredit extends Model
{
    public static function tableName(): string
    {
        return 'layaway_credits';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'original_ticket_id' => 'required',
            'original_event_id' => 'required|exists:events,id',
            'amount' => 'required|numeric',
            'remaining_amount' => 'required|numeric',
            'status' => 'required'
        ];
    }

    /**
     * Fetch active credits available for an email / user
     */
    public static function findAvailableByEmail(string $email, ?int $userId = null): array
    {
        $db = Application::$app->db->pdo;
        $sql = "
            SELECT lc.*, e.name AS original_event_name
            FROM layaway_credits lc
            LEFT JOIN events e ON lc.original_event_id = e.id
            WHERE (LOWER(lc.email) = LOWER(:email) " . ($userId ? "OR lc.user_id = :user_id" : "") . ")
              AND lc.status IN ('available', 'partially_used')
              AND lc.remaining_amount > 0
              AND (lc.expires_at IS NULL OR lc.expires_at > NOW())
            ORDER BY lc.created_at DESC
        ";
        $stmt = $db->prepare($sql);
        $params = ['email' => $email];
        if ($userId) {
            $params['user_id'] = $userId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
