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
    public ?string $promoter_email = null;
    public ?string $promoter_phone = null;
    public string $referral_code = '';
    public string $commission_type = 'percentage'; // 'percentage' or 'fixed'
    public float $commission_rate = 0.00;
    public int $visits_count = 0;
    public int $unique_visits = 0;
    public int $tickets_sold = 0;
    public float $revenue_generated = 0.00;
    public float $commission_earned = 0.00;
    public float $commission_paid = 0.00;
    public string $payout_status = 'unpaid'; // 'unpaid', 'partial', 'paid'
    public ?string $notes = null;
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
     * Record a visit/click on the referral link with unique deduplication.
     */
    public static function recordVisit(int $referralId, bool $isUnique = true): void
    {
        try {
            $db = Application::$app->db->pdo;
            if ($isUnique) {
                $stmt = $db->prepare("
                    UPDATE event_referrals 
                    SET visits_count = visits_count + 1,
                        unique_visits = unique_visits + 1
                    WHERE id = :id AND status = 'active'
                ");
            } else {
                $stmt = $db->prepare("
                    UPDATE event_referrals 
                    SET visits_count = visits_count + 1
                    WHERE id = :id AND status = 'active'
                ");
            }
            $stmt->execute(['id' => $referralId]);
        } catch (\Exception $e) {
            // Non-blocking
        }
    }

    /**
     * Record a ticket sale on the referral link and compute commission accurately.
     */
    public static function recordSale(int $referralId, int $ticketCount, float $subtotal): void
    {
        try {
            $db = Application::$app->db->pdo;

            // Fetch current commission settings for this referral
            $stmt = $db->prepare("SELECT commission_type, commission_rate, commission_earned, commission_paid FROM event_referrals WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $referralId]);
            $ref = $stmt->fetch();

            if (!$ref) {
                return;
            }

            $rate = (float)($ref['commission_rate'] ?? 0.00);
            $type = $ref['commission_type'] ?? 'percentage';

            // Calculate commission for this sale
            $newCommission = 0.00;
            if ($rate > 0) {
                if ($type === 'fixed') {
                    // Fixed fee per ticket (e.g. 500 NGN per ticket)
                    $newCommission = round($ticketCount * $rate, 2);
                } else {
                    // Percentage of ticket subtotal
                    $newCommission = round(($subtotal * $rate) / 100, 2);
                }
            }

            $totalCommissionEarned = (float)($ref['commission_earned'] ?? 0.00) + $newCommission;
            $totalCommissionPaid = (float)($ref['commission_paid'] ?? 0.00);

            $payoutStatus = 'unpaid';
            if ($totalCommissionPaid >= $totalCommissionEarned && $totalCommissionEarned > 0) {
                $payoutStatus = 'paid';
            } elseif ($totalCommissionPaid > 0) {
                $payoutStatus = 'partial';
            }

            $upStmt = $db->prepare("
                UPDATE event_referrals 
                SET tickets_sold = tickets_sold + :qty,
                    revenue_generated = revenue_generated + :revenue,
                    commission_earned = commission_earned + :commission,
                    payout_status = :payout_status
                WHERE id = :id
            ");
            $upStmt->execute([
                'id' => $referralId,
                'qty' => $ticketCount,
                'revenue' => $subtotal,
                'commission' => $newCommission,
                'payout_status' => $payoutStatus
            ]);
        } catch (\Exception $e) {
            // Non-blocking
        }
    }

    /**
     * Record a payout to a promoter.
     */
    public static function recordPayout(int $referralId, float $amount, string $note = ''): bool
    {
        try {
            $db = Application::$app->db->pdo;
            $stmt = $db->prepare("SELECT commission_earned, commission_paid, notes FROM event_referrals WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $referralId]);
            $ref = $stmt->fetch();

            if (!$ref) {
                return false;
            }

            $earned = (float)$ref['commission_earned'];
            $newPaid = (float)$ref['commission_paid'] + $amount;

            $status = 'unpaid';
            if ($newPaid >= $earned && $earned > 0) {
                $status = 'paid';
            } elseif ($newPaid > 0) {
                $status = 'partial';
            }

            $timestamp = date('Y-m-d H:i');
            $appendNote = trim($note);
            $existingNotes = trim($ref['notes'] ?? '');
            $updatedNotes = $existingNotes;
            if (!empty($appendNote)) {
                $entry = "[{$timestamp}] Paid " . number_format($amount, 2) . ": {$appendNote}";
                $updatedNotes = $existingNotes ? ($existingNotes . "\n" . $entry) : $entry;
            }

            $upStmt = $db->prepare("
                UPDATE event_referrals 
                SET commission_paid = :paid,
                    payout_status = :status,
                    notes = :notes
                WHERE id = :id
            ");
            return $upStmt->execute([
                'paid' => $newPaid,
                'status' => $status,
                'notes' => $updatedNotes,
                'id' => $referralId
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }
}
