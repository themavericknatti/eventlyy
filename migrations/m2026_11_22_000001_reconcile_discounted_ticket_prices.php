<?php

use Ace\Migration;

class m2026_11_22_000001_reconcile_discounted_ticket_prices extends Migration
{
    public function up(): void
    {
        // Reconcile any past tickets created before this fix where a transaction had a promo code discount
        // but tickets were recorded with the full undiscounted price.
        try {
            $stmt = $this->pdo->query("
                SELECT reference, event_id, subtotal_amount, discount_amount 
                FROM transactions 
                WHERE status = 'completed' AND discount_amount > 0 AND subtotal_amount > 0
            ");
            $discountedTxs = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($discountedTxs as $tx) {
                $ref = $tx['reference'];
                $discountAmount = (float)$tx['discount_amount'];
                $subtotal = (float)$tx['subtotal_amount'];
                $expectedNetSubtotal = max(0.00, round($subtotal - $discountAmount, 2));

                $ticketStmt = $this->pdo->prepare("
                    SELECT id, price 
                    FROM tickets 
                    WHERE status_detail LIKE :refPattern
                ");
                $ticketStmt->execute(['refPattern' => '%' . $ref . '%']);
                $tickets = $ticketStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

                if (empty($tickets)) {
                    continue;
                }

                $currentTicketTotal = 0.00;
                foreach ($tickets as $tk) {
                    $currentTicketTotal += (float)$tk['price'];
                }

                if (round($currentTicketTotal, 2) > $expectedNetSubtotal) {
                    $discountRatio = min(1.0, $discountAmount / $subtotal);
                    $remainingExpected = $expectedNetSubtotal;
                    $ticketCount = count($tickets);

                    $updStmt = $this->pdo->prepare("UPDATE tickets SET price = :price WHERE id = :id");
                    foreach ($tickets as $idx => $tk) {
                        if ($idx === $ticketCount - 1) {
                            $newPrice = max(0.00, round($remainingExpected, 2));
                        } else {
                            $origPrice = (float)$tk['price'];
                            $newPrice = max(0.00, round($origPrice * (1.0 - $discountRatio), 2));
                            $remainingExpected -= $newPrice;
                        }
                        $updStmt->execute([
                            'price' => $newPrice,
                            'id' => $tk['id']
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Log or ignore if tables do not exist during initial setup
        }
    }

    public function down(): void
    {
        // Irreversible price normalization
    }
}
