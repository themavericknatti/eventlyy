<?php

use Ace\Migration;

class m2026_08_01_000001_add_audit_fields_to_event_staff extends Migration
{
    public function up(): void
    {
        $cols = $this->pdo->query("SHOW COLUMNS FROM event_staff")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('last_login_at', $cols)) {
            $this->pdo->exec("ALTER TABLE event_staff ADD COLUMN last_login_at DATETIME NULL");
        }
        if (!in_array('last_ip', $cols)) {
            $this->pdo->exec("ALTER TABLE event_staff ADD COLUMN last_ip VARCHAR(45) NULL");
        }
        if (!in_array('user_agent', $cols)) {
            $this->pdo->exec("ALTER TABLE event_staff ADD COLUMN user_agent VARCHAR(500) NULL");
        }
        if (!in_array('is_online', $cols)) {
            $this->pdo->exec("ALTER TABLE event_staff ADD COLUMN is_online TINYINT(1) DEFAULT 0");
        }
        if (!in_array('last_logout_at', $cols)) {
            $this->pdo->exec("ALTER TABLE event_staff ADD COLUMN last_logout_at DATETIME NULL");
        }
        if (!in_array('last_active_at', $cols)) {
            $this->pdo->exec("ALTER TABLE event_staff ADD COLUMN last_active_at DATETIME NULL");
        }

        $tcols = $this->pdo->query("SHOW COLUMNS FROM tickets")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('checked_in_by', $tcols)) {
            $this->pdo->exec("ALTER TABLE tickets ADD COLUMN checked_in_by BIGINT NULL");
        }
    }

    public function down(): void
    {
        $this->pdo->exec("ALTER TABLE event_staff DROP COLUMN IF EXISTS last_login_at, DROP COLUMN IF EXISTS last_ip, DROP COLUMN IF EXISTS user_agent");
        $this->pdo->exec("ALTER TABLE tickets DROP COLUMN IF EXISTS checked_in_by");
    }
}
