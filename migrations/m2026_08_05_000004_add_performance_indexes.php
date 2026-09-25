<?php

use Ace\Migration;

/**
 * Migration: Add Performance Indexes for Instant Database Queries & View Rendering
 */
class m2026_08_05_000004_add_performance_indexes extends Migration
{
    public function up(): void
    {
        $indexes = [
            // Events Table Indexes
            'events' => [
                'idx_events_status_date' => '`status`, `date`',
                'idx_events_org_status' => '`organizer_id`, `status`',
                'idx_events_featured_status' => '`is_featured`, `status`',
            ],
            // Tickets Table Indexes
            'tickets' => [
                'idx_tickets_event_status' => '`event_id`, `status`',
                'idx_tickets_user_status' => '`user_id`, `status`',
                'idx_tickets_purchase_date' => '`purchase_date`',
            ],
            // Transactions Table Indexes
            'transactions' => [
                'idx_transactions_event_status' => '`event_id`, `status`',
                'idx_transactions_created_at' => '`created_at`, `status`',
            ],
            // Payouts Table Indexes
            'payouts' => [
                'idx_payouts_org_status' => '`organizer_id`, `status`',
                'idx_payouts_event_status' => '`event_id`, `status`',
            ],
        ];

        foreach ($indexes as $table => $tableIndexes) {
            // Check existing indexes
            $existing = $this->pdo->query("SHOW INDEX FROM `$table`")->fetchAll(\PDO::FETCH_ASSOC);
            $existingIndexNames = array_column($existing, 'Key_name');

            foreach ($tableIndexes as $indexName => $columns) {
                if (!in_array($indexName, $existingIndexNames)) {
                    try {
                        $this->pdo->exec("ALTER TABLE `$table` ADD INDEX `$indexName` ($columns)");
                    } catch (\Exception $e) {
                        // Skip if index already exists under different name or constraint
                    }
                }
            }
        }
    }

    public function down(): void
    {
        $indexes = [
            'events' => ['idx_events_status_date', 'idx_events_org_status', 'idx_events_featured_status'],
            'tickets' => ['idx_tickets_event_status', 'idx_tickets_user_status', 'idx_tickets_purchase_date'],
            'transactions' => ['idx_transactions_event_status', 'idx_transactions_created_at'],
            'payouts' => ['idx_payouts_org_status', 'idx_payouts_event_status'],
        ];

        foreach ($indexes as $table => $indexList) {
            foreach ($indexList as $indexName) {
                try {
                    $this->pdo->exec("ALTER TABLE `$table` DROP INDEX `$indexName`");
                } catch (\Exception $e) {
                    // Ignore drop errors
                }
            }
        }
    }
}
