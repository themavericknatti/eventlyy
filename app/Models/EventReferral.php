<?php

namespace App\Models;

use Ace\Application;
use Ace\Model;

class EventReferral extends Model
{
    public ?int $id = null;
    public ?int $event_id = null;
    public ?int $host_id = null;
    public string $promoter_name = '';
    public string $referral_code = '';
    public float $commission_rate = 0.00;
    public int $visits_count = 0;
    public int $tickets_sold = 0;
    public float $revenue_generated = 0.00;
    public string $status = 'active';

    public static function tableName(): string
    {
        return 'event_referrals';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'event_id' => 'required|numeric',
            'host_id' => 'required|numeric',
            'promoter_name' => 'required',
            'referral_code' => 'required|unique:event_referrals,referral_code',
        ];
    }

    /**
     * Generate a unique, clean referral code slug.
     */
    public static function generateCode(string $promoterName, int $eventId): string
    {
        $base = strtolower(trim($promoterName));
        $base = preg_replace('/[^a-z0-9]+/i', '', $base);
        if (empty($base)) {
            $base = 'ref';
        }
        $base = substr($base, 0, 12);

        $db = Application::$app->db->pdo;
        $candidate = $base;
        $attempt = 1;

        while (true) {
            $stmt = $db->prepare("SELECT id FROM event_referrals WHERE referral_code = :code LIMIT 1");
            $stmt->execute(['code' => $candidate]);
            if (!$stmt->fetch()) {
                return $candidate;
            }
            $candidate = $base . ($attempt === 1 ? rand(10, 99) : rand(100, 999));
            $attempt++;
        }
    }

    /**
     * Record a visit/click on the referral link.
     */
    public static function recordVisit(int $referralId): void
    {
        try {
            $db = Application::$app->db->pdo;
            $stmt = $db->prepare("UPDATE event_referrals SET visits_count = visits_count + 1 WHERE id = :id AND status = 'active'");
            $stmt->execute(['id' => $referralId]);
        } catch (\Exception $e) {
            // Non-blocking
        }
    }

    /**
     * Record a ticket sale on the referral link.
     */
    public static function recordSale(int $referralId, int $ticketCount, float $subtotal): void
    {
        try {
            $db = Application::$app->db->pdo;
            $stmt = $db->prepare("
                UPDATE event_referrals 
                SET tickets_sold = tickets_sold + :qty,
                    revenue_generated = revenue_generated + :revenue
                WHERE id = :id
            ");
            $stmt->execute([
                'id' => $referralId,
                'qty' => $ticketCount,
                'revenue' => $subtotal
            ]);
        } catch (\Exception $e) {
            // Non-blocking
        }
    }
}
