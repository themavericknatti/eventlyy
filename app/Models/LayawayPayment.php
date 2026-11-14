<?php

namespace App\Models;

use Ace\Model;
use Ace\Application;

class LayawayPayment extends Model
{
    public static function tableName(): string
    {
        return 'layaway_payments';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'layaway_ticket_id' => 'required',
            'event_id' => 'required|exists:events,id',
            'payer_email' => 'required|email',
            'amount' => 'required|numeric',
            'reference' => 'required'
        ];
    }

    /**
     * Get all payments made for a specific ticket code.
     */
    public static function findByTicketId(string $ticketId): array
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT * FROM layaway_payments 
            WHERE layaway_ticket_id = :ticket_id 
            ORDER BY created_at ASC
        ");
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get total completed amount paid for a specific ticket code.
     */
    public static function getTotalPaid(string $ticketId): float
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total 
            FROM layaway_payments 
            WHERE layaway_ticket_id = :ticket_id AND status = 'completed'
        ");
        $stmt->execute(['ticket_id' => $ticketId]);
        return (float)$stmt->fetchColumn();
    }
}
