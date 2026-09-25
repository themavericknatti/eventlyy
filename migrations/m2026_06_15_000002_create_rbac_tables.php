<?php

use Ace\Migration;

class m2026_06_15_000002_create_rbac_tables extends Migration
{
    public function up(): void
    {
        // 1. Create tables
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `roles` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `slug` VARCHAR(100) NOT NULL UNIQUE,
                `description` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `permissions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `slug` VARCHAR(100) NOT NULL UNIQUE,
                `description` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `role_permissions` (
                `role_id` INT NOT NULL,
                `permission_id` INT NOT NULL,
                PRIMARY KEY (`role_id`, `permission_id`),
                INDEX (`role_id`),
                INDEX (`permission_id`),
                FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `user_roles` (
                `user_id` BIGINT NOT NULL,
                `role_id` INT NOT NULL,
                PRIMARY KEY (`user_id`, `role_id`),
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 2. Seed default roles
        $defaultRoles = [
            [
                'name' => 'Super Administrator',
                'slug' => 'super-admin',
                'description' => 'Full control over the platform.'
            ],
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Manages platform operations.'
            ],
            [
                'name' => 'Event Organizer',
                'slug' => 'event-organizer',
                'description' => 'Creates and manages events.'
            ],
            [
                'name' => 'Organizer Staff',
                'slug' => 'organizer-staff',
                'description' => 'Assists event organizers.'
            ],
            [
                'name' => 'Ticket Scanner',
                'slug' => 'ticket-scanner',
                'description' => 'Verifies tickets and checks in attendees.'
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Purchases tickets and attends events.'
            ],
            [
                'name' => 'Finance Manager',
                'slug' => 'finance-manager',
                'description' => 'Manages payments, refunds and payouts.'
            ],
            [
                'name' => 'Customer Support',
                'slug' => 'customer-support',
                'description' => 'Handles customer inquiries and support.'
            ],
            [
                'name' => 'Technical Operations',
                'slug' => 'technical-ops',
                'description' => 'Monitors gate scanner hardware, latency, and system operations.'
            ]
        ];

        $stmt = $this->pdo->prepare("INSERT IGNORE INTO `roles` (`name`, `slug`, `description`) VALUES (:name, :slug, :description)");
        foreach ($defaultRoles as $role) {
            $stmt->execute($role);
        }

        // 3. Seed default permissions
        $defaultPermissions = [

            // Dashboard
            [
                'name' => 'View Dashboard',
                'slug' => 'view-dashboard',
                'description' => 'Access dashboard'
            ],

            // Users
            [
                'name' => 'View Users',
                'slug' => 'users-view',
                'description' => 'View users'
            ],
            [
                'name' => 'Create Users',
                'slug' => 'users-create',
                'description' => 'Create users'
            ],
            [
                'name' => 'Edit Users',
                'slug' => 'users-edit',
                'description' => 'Edit users'
            ],
            [
                'name' => 'Delete Users',
                'slug' => 'users-delete',
                'description' => 'Delete users'
            ],
            [
                'name' => 'Ban Users',
                'slug' => 'users-ban',
                'description' => 'Ban users'
            ],

            // Roles
            [
                'name' => 'View Roles',
                'slug' => 'roles-view',
                'description' => 'View roles'
            ],
            [
                'name' => 'Create Roles',
                'slug' => 'roles-create',
                'description' => 'Create roles'
            ],
            [
                'name' => 'Edit Roles',
                'slug' => 'roles-edit',
                'description' => 'Edit roles'
            ],
            [
                'name' => 'Delete Roles',
                'slug' => 'roles-delete',
                'description' => 'Delete roles'
            ],

            // Permissions
            [
                'name' => 'View Permissions',
                'slug' => 'permissions-view',
                'description' => 'View permissions'
            ],
            [
                'name' => 'Create Permissions',
                'slug' => 'permissions-create',
                'description' => 'Create permissions'
            ],
            [
                'name' => 'Edit Permissions',
                'slug' => 'permissions-edit',
                'description' => 'Edit permissions'
            ],
            [
                'name' => 'Delete Permissions',
                'slug' => 'permissions-delete',
                'description' => 'Delete permissions'
            ],

            // Events
            [
                'name' => 'View Events',
                'slug' => 'events-view',
                'description' => 'View events'
            ],
            [
                'name' => 'Create Events',
                'slug' => 'events-create',
                'description' => 'Create events'
            ],
            [
                'name' => 'Edit Events',
                'slug' => 'events-edit',
                'description' => 'Edit events'
            ],
            [
                'name' => 'Delete Events',
                'slug' => 'events-delete',
                'description' => 'Delete events'
            ],
            [
                'name' => 'Publish Events',
                'slug' => 'events-publish',
                'description' => 'Publish events'
            ],
            [
                'name' => 'Unpublish Events',
                'slug' => 'events-unpublish',
                'description' => 'Unpublish events'
            ],
            [
                'name' => 'Approve Events',
                'slug' => 'events-approve',
                'description' => 'Approve events'
            ],
            [
                'name' => 'Reject Events',
                'slug' => 'events-reject',
                'description' => 'Reject events'
            ],

            // Ticket Types
            [
                'name' => 'View Ticket Types',
                'slug' => 'ticket-types-view',
                'description' => 'View ticket types'
            ],
            [
                'name' => 'Create Ticket Types',
                'slug' => 'ticket-types-create',
                'description' => 'Create ticket types'
            ],
            [
                'name' => 'Edit Ticket Types',
                'slug' => 'ticket-types-edit',
                'description' => 'Edit ticket types'
            ],
            [
                'name' => 'Delete Ticket Types',
                'slug' => 'ticket-types-delete',
                'description' => 'Delete ticket types'
            ],

            // Tickets
            [
                'name' => 'View Tickets',
                'slug' => 'tickets-view',
                'description' => 'View tickets'
            ],
            [
                'name' => 'Sell Tickets',
                'slug' => 'tickets-sell',
                'description' => 'Sell tickets'
            ],
            [
                'name' => 'Refund Tickets',
                'slug' => 'tickets-refund',
                'description' => 'Refund tickets'
            ],
            [
                'name' => 'Cancel Tickets',
                'slug' => 'tickets-cancel',
                'description' => 'Cancel tickets'
            ],
            [
                'name' => 'Scan Tickets',
                'slug' => 'tickets-scan',
                'description' => 'Scan tickets'
            ],
            [
                'name' => 'Check In Attendees',
                'slug' => 'tickets-checkin',
                'description' => 'Check attendees into events'
            ],

            // Attendees
            [
                'name' => 'View Attendees',
                'slug' => 'attendees-view',
                'description' => 'View attendees'
            ],
            [
                'name' => 'Export Attendees',
                'slug' => 'attendees-export',
                'description' => 'Export attendees'
            ],

            // Orders
            [
                'name' => 'View Orders',
                'slug' => 'orders-view',
                'description' => 'View orders'
            ],
            [
                'name' => 'Manage Orders',
                'slug' => 'orders-manage',
                'description' => 'Manage orders'
            ],

            // Payments
            [
                'name' => 'View Payments',
                'slug' => 'payments-view',
                'description' => 'View payments'
            ],
            [
                'name' => 'Refund Payments',
                'slug' => 'payments-refund',
                'description' => 'Refund payments'
            ],

            // Payouts
            [
                'name' => 'View Payouts',
                'slug' => 'payouts-view',
                'description' => 'View payouts'
            ],
            [
                'name' => 'Process Payouts',
                'slug' => 'payouts-process',
                'description' => 'Process payouts'
            ],

            // Coupons
            [
                'name' => 'View Coupons',
                'slug' => 'coupons-view',
                'description' => 'View coupons'
            ],
            [
                'name' => 'Create Coupons',
                'slug' => 'coupons-create',
                'description' => 'Create coupons'
            ],
            [
                'name' => 'Edit Coupons',
                'slug' => 'coupons-edit',
                'description' => 'Edit coupons'
            ],
            [
                'name' => 'Delete Coupons',
                'slug' => 'coupons-delete',
                'description' => 'Delete coupons'
            ],

            // Reviews
            [
                'name' => 'View Reviews',
                'slug' => 'reviews-view',
                'description' => 'View reviews'
            ],
            [
                'name' => 'Delete Reviews',
                'slug' => 'reviews-delete',
                'description' => 'Delete reviews'
            ],

            // Reports
            [
                'name' => 'View Reports',
                'slug' => 'reports-view',
                'description' => 'View reports'
            ],
            [
                'name' => 'Export Reports',
                'slug' => 'reports-export',
                'description' => 'Export reports'
            ],

            // Settings
            [
                'name' => 'View Settings',
                'slug' => 'settings-view',
                'description' => 'View settings'
            ],
            [
                'name' => 'Edit Settings',
                'slug' => 'settings-edit',
                'description' => 'Edit settings'
            ],

            // Logs
            [
                'name' => 'View Logs',
                'slug' => 'logs-view',
                'description' => 'View system logs'
            ]
        ];

        $stmt = $this->pdo->prepare("INSERT IGNORE INTO `permissions` (`name`, `slug`, `description`) VALUES (:name, :slug, :description)");
        foreach ($defaultPermissions as $perm) {
            $stmt->execute($perm);
        }

        // 4. Assign all permissions to Super Admin
        $superAdminRole = $this->pdo->query("SELECT id FROM roles WHERE slug = 'super-admin'")->fetch();

        $allPerms = $this->pdo->query("SELECT id FROM permissions")->fetchAll();

        if ($superAdminRole) {
            $stmt = $this->pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (:role_id, :perm_id)");

            foreach ($allPerms as $perm) {
                $stmt->execute([
                    'role_id' => $superAdminRole['id'],
                    'perm_id' => $perm['id']
                ]);
            }
        }

        // 5. Assign permissions to Administrator
        $adminRole = $this->pdo->query("SELECT id FROM roles WHERE slug = 'admin'")->fetch();

        if ($adminRole) {

            $adminPerms = $this->pdo->query(" SELECT id FROM permissions WHERE slug IN (
        'view-dashboard',

        'users-view',
        'users-create',
        'users-edit',
        'users-delete',
        'users-ban',

        'events-view',
        'events-create',
        'events-edit',
        'events-delete',
        'events-publish',
        'events-unpublish',
        'events-approve',
        'events-reject',

        'ticket-types-view',
        'ticket-types-create',
        'ticket-types-edit',
        'ticket-types-delete',

        'tickets-view',
        'tickets-sell',
        'tickets-refund',
        'tickets-cancel',

        'tickets-scan',
        'tickets-checkin',

        'attendees-view',
        'attendees-export',

        'orders-view',
        'orders-manage',

        'payments-view',
        'payments-refund',

        'payouts-view',
        'payouts-process',

        'coupons-view',
        'coupons-create',
        'coupons-edit',
        'coupons-delete',

        'reviews-view',
        'reviews-delete',

        'reports-view',
        'reports-export',

        'settings-view',
        'settings-edit',

        'logs-view'
    ) ")->fetchAll();

            $stmt = $this->pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (:role_id, :perm_id) ");

            foreach ($adminPerms as $perm) {
                $stmt->execute([
                    'role_id' => $adminRole['id'],
                    'perm_id' => $perm['id']
                ]);
            }
        }

        // 6. Seed default Super Admin account
        $superAdminEmail = 'celionatti@gmail.com';
        $existingAdmin = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
        $existingAdmin->execute(['email' => $superAdminEmail]);
        $adminUser = $existingAdmin->fetch();

        if (!$adminUser) {
            // Create the Super Admin user
            $hashedPassword = password_hash('password', PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("
                INSERT INTO `users` (`name`, `email`, `password`, `status`, `created_at`)
                VALUES (:name, :email, :password, 'active', NOW())
            ");
            $stmt->execute([
                'name' => 'Celio Natti',
                'email' => $superAdminEmail,
                'password' => $hashedPassword
            ]);
            $adminUserId = $this->pdo->lastInsertId();
        } else {
            $adminUserId = $adminUser['id'];
        }

        // Assign super-admin role to the Super Admin user
        if ($superAdminRole && $adminUserId) {
            $stmt = $this->pdo->prepare("INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`) VALUES (:user_id, :role_id)");
            $stmt->execute(['user_id' => $adminUserId, 'role_id' => $superAdminRole['id']]);
        }

        // 7. Assign 'user' role to any remaining users without a role
        $userRole = $this->pdo->query("SELECT id FROM roles WHERE slug = 'user'")->fetch();

        if ($userRole) {
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO user_roles (user_id, role_id)
                SELECT u.id, :role_id
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                WHERE ur.user_id IS NULL
            ");
            $stmt->execute([
                'role_id' => $userRole['id']
            ]);
        }
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `user_roles`");
        $this->pdo->exec("DROP TABLE IF EXISTS `role_permissions`");
        $this->pdo->exec("DROP TABLE IF EXISTS `permissions`");
        $this->pdo->exec("DROP TABLE IF EXISTS `roles`");
    }
}
