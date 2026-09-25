<?php

class m2026_09_01_000001_update_team_roles_and_add_staff_checkin
{
    public function up(): void
    {
        $db = \Ace\Application::$app->db->pdo;

        // 1. Add check-in columns to event_staff_assignments if not exists
        try {
            $cols = $db->query("SHOW COLUMNS FROM event_staff_assignments LIKE 'checked_in'")->fetchAll();
            if (empty($cols)) {
                $db->exec("ALTER TABLE event_staff_assignments 
                    ADD COLUMN checked_in TINYINT(1) NOT NULL DEFAULT 0,
                    ADD COLUMN checked_in_at DATETIME NULL,
                    ADD COLUMN checked_in_by BIGINT(20) NULL,
                    ADD COLUMN notes VARCHAR(255) NULL
                ");
            }
        } catch (\Exception $e) {
            // Already present
        }

        // 2. Update 'event-organizer' role name & description
        $db->exec("
            UPDATE roles 
            SET name = 'Event Organiser (Box Office/Finance)', 
                description = 'Manages door ticket sales, box office, financials, event schedule, and staff check-ins.'
            WHERE slug = 'event-organizer'
        ");

        // 3. Migrate any assignments from 'organizer-staff' to 'event-organizer'
        $orgStaffRole = $db->query("SELECT id FROM roles WHERE slug = 'organizer-staff'")->fetch();
        $eventOrgRole = $db->query("SELECT id FROM roles WHERE slug = 'event-organizer'")->fetch();
        
        if ($orgStaffRole && $eventOrgRole) {
            $db->exec("
                UPDATE event_staff_assignments 
                SET role_id = " . (int)$eventOrgRole['id'] . " 
                WHERE role_id = " . (int)$orgStaffRole['id']
            );
            // Remove redundant organizer-staff role
            $db->exec("DELETE FROM role_permissions WHERE role_id = " . (int)$orgStaffRole['id']);
            $db->exec("DELETE FROM roles WHERE id = " . (int)$orgStaffRole['id']);
        }

        // 4. Update 'technical-ops' description
        $db->exec("
            UPDATE roles 
            SET description = 'Manages sounds, lighting, stage equipment, AV systems, and technical infrastructure.'
            WHERE slug = 'technical-ops'
        ");

        // 5. Add 'Usher / Floor Guide' role if not exists
        $usherRole = $db->query("SELECT id FROM roles WHERE slug = 'usher'")->fetch();
        if (!$usherRole) {
            $db->exec("
                INSERT INTO roles (name, slug, description) 
                VALUES ('Usher / Floor Guide', 'usher', 'Manages seating rows, guest direction, VIP sections, and crowd flow at the event.')
            ");
        }
    }

    public function down(): void
    {
        $db = \Ace\Application::$app->db->pdo;
        // Reversible if needed
    }
}
