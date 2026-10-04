<?php

/**
 * AdminSettingsController
 *
 * This controller handles admin settings-related routes.
 *
 * @package Evently
 * @subpackage Controllers/AdminSettings
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers\Admin;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\AdminMiddleware;

class AdminSettingsController extends Controller
{
    private string $settingsFile;

    /**
     * AdminSettingsController constructor.
     * Registers the AdminMiddleware to restrict access to admin users only.
     */
    public function __construct()
    {
        $this->registerMiddleware(new AdminMiddleware());
        $this->settingsFile = Application::$ROOT_DIR . '/config/settings.json';
    }

    private function getSettings(): array
    {
        $defaults = [
            'site_title' => 'Eventlyy',
            'site_description' => 'Discover and book amazing events near you.',
            'site_logo' => '',
            'site_favicon' => '',
            'support_email' => 'support@eventlyy.com',
            'maintenance_mode' => 'no',
            'enabled_gateways' => ['paystack'],
            'default_gateway' => 'paystack',
            'platform_fee' => '5',
            'extra_charge' => '100.00',
            'custom_domain_fee' => '10000.00',
            'currency' => 'NGN',
            'exchange_rate' => '1500.00',
            'timezone' => 'Africa/Lagos',
            'event_completion_hours' => '12',
			'auto_payout_enabled' => '1',
            'auto_payout_delay_minutes' => '15',
            'custom_domain_enabled' => '0',
            'social_twitter' => '',
            'social_instagram' => '',
            'social_facebook' => '',
            'social_linkedin' => '',
            'social_youtube' => '',
            'social_tiktok' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'paystack_public_key' => '',
            'paystack_secret_key' => '',
            'stripe_public_key' => '',
            'stripe_secret_key' => '',
            'flutterwave_public_key' => '',
            'flutterwave_secret_key' => '',
            'announcement_enabled' => '0',
            'announcement_location' => 'home',
            'announcement_badge' => '🎉 Special Announcement',
            'announcement_title' => '',
            'announcement_message' => '',
            'announcement_image' => '',
            'announcement_link_text' => 'Check It Out →',
            'announcement_link_url' => '',
            'announcement_dismiss_text' => 'Continue to Site',
            'announcement_updated_at' => '0',
        ];

        if (!file_exists($this->settingsFile)) {
            @file_put_contents($this->settingsFile, json_encode($defaults, JSON_PRETTY_PRINT));
            return $defaults;
        }

        $content = file_get_contents($this->settingsFile);
        $data = json_decode($content, true) ?: [];

        return array_merge($defaults, $data);
    }

    public function settings()
    {
        $settings = $this->getSettings();

        // Render the admin settings view
        return $this->render('admin/settings', [
            'settings' => $settings
        ]);
    }

    public function saveSettings(Request $request)
    {
        $user = Application::$app->user;
        if (!$user || !method_exists($user, 'isSuperAdmin') || !$user->isSuperAdmin()) {
            Application::$app->session->setFlash('error', 'Forbidden: Only Super Admins are authorized to modify system configuration settings.');
            return redirect('/admin/settings');
        }

        $data = $request->getBody();
        $action = $data['action'] ?? '';

        if ($action === 'update_settings') {
            $currentSettings = $this->getSettings();

            $settings = [
                'site_title' => trim($data['site_title'] ?? 'Eventlyy'),
                'site_description' => trim($data['site_description'] ?? ''),
                'site_logo' => $currentSettings['site_logo'] ?? '',
                'site_favicon' => $currentSettings['site_favicon'] ?? '',
                'support_email' => trim($data['support_email'] ?? 'support@eventlyy.com'),
                'maintenance_mode' => trim($data['maintenance_mode'] ?? 'no'),
                'enabled_gateways' => is_array($data['enabled_gateways'] ?? null) && !empty($data['enabled_gateways']) ? $data['enabled_gateways'] : ['paystack'],
                'default_gateway' => trim($data['default_gateway'] ?? 'paystack'),
                'platform_fee' => trim($data['platform_fee'] ?? '5'),
                'extra_charge' => trim($data['extra_charge'] ?? '100.00'),
                'custom_domain_fee' => trim($data['custom_domain_fee'] ?? '10000.00'),
                'currency' => trim($data['currency'] ?? 'NGN'),
                'exchange_rate' => trim($data['exchange_rate'] ?? '1500.00'),
                'timezone' => trim($data['timezone'] ?? 'Africa/Lagos'),
                'event_completion_hours' => trim($data['event_completion_hours'] ?? '12'),
				'auto_payout_enabled' => !empty($data['auto_payout_enabled']) ? '1' : '0',
                'auto_payout_delay_minutes' => trim($data['auto_payout_delay_minutes'] ?? '15'),
                'custom_domain_enabled' => !empty($data['custom_domain_enabled']) ? '1' : '0',
                'social_twitter' => trim($data['social_twitter'] ?? ''),
                'social_instagram' => trim($data['social_instagram'] ?? ''),
                'social_facebook' => trim($data['social_facebook'] ?? ''),
                'social_linkedin' => trim($data['social_linkedin'] ?? ''),
                'social_youtube' => trim($data['social_youtube'] ?? ''),
                'social_tiktok' => trim($data['social_tiktok'] ?? ''),
                'meta_description' => trim($data['meta_description'] ?? ''),
                'meta_keywords' => trim($data['meta_keywords'] ?? ''),
                'paystack_public_key' => trim($data['paystack_public_key'] ?? ''),
                'paystack_secret_key' => trim($data['paystack_secret_key'] ?? ''),
                'stripe_public_key' => trim($data['stripe_public_key'] ?? ''),
                'stripe_secret_key' => trim($data['stripe_secret_key'] ?? ''),
                'flutterwave_public_key' => trim($data['flutterwave_public_key'] ?? ''),
                'flutterwave_secret_key' => trim($data['flutterwave_secret_key'] ?? ''),
                'announcement_enabled' => !empty($data['announcement_enabled']) ? '1' : '0',
                'announcement_location' => in_array($data['announcement_location'] ?? '', ['home', 'sitewide']) ? $data['announcement_location'] : 'home',
                'announcement_badge' => trim($data['announcement_badge'] ?? '🎉 Special Announcement'),
                'announcement_title' => trim($data['announcement_title'] ?? ''),
                'announcement_message' => trim($data['announcement_message'] ?? ''),
                'announcement_image' => $currentSettings['announcement_image'] ?? '',
                'announcement_link_text' => trim($data['announcement_link_text'] ?? 'Check It Out →'),
                'announcement_link_url' => trim($data['announcement_link_url'] ?? ''),
                'announcement_dismiss_text' => trim($data['announcement_dismiss_text'] ?? 'Continue to Site'),
                'announcement_updated_at' => (string)time(),
            ];

            // Also update the runtime timezone immediately
            date_default_timezone_set($settings['timezone']);

            if (empty($settings['site_title']) || empty($settings['support_email'])) {
                Application::$app->session->setFlash('error', 'Site Title and Support Email are required.');
                return redirect('/admin/settings');
            }

            if (!filter_var($settings['support_email'], FILTER_VALIDATE_EMAIL)) {
                Application::$app->session->setFlash('error', 'Please enter a valid support email address.');
                return redirect('/admin/settings');
            }

            $fee = (float)$settings['platform_fee'];
            if ($fee < 0 || $fee > 100) {
                Application::$app->session->setFlash('error', 'Platform fee must be between 0% and 100%.');
                return redirect('/admin/settings');
            }

            $extraCharge = (float)$settings['extra_charge'];
            if ($extraCharge < 0) {
                Application::$app->session->setFlash('error', 'Extra charge cannot be less than 0.');
                return redirect('/admin/settings');
            }

            // Upload directory setup
            $uploadService = new \Ace\UploadService();
            $uploadDir = public_dir('assets/img/branding/');
            \Ace\UploadService::ensureDirectory($uploadDir);

            // Helper: delete an old asset file from public/assets/ if it exists
            $deleteOldAsset = function (string $relativePath) {
                if (empty($relativePath) || str_starts_with($relativePath, 'http')) {
                    return;
                }
                $fullPath = public_dir('assets/' . $relativePath);
                if (is_file($fullPath)) {
                    @unlink($fullPath);
                }
            };

            // Process Logo: Option to remove or replace
            if (!empty($data['remove_logo'])) {
                $deleteOldAsset($currentSettings['site_logo'] ?? '');
                $settings['site_logo'] = '';
            }
            if (isset($_FILES['site_logo']) && !empty($_FILES['site_logo']['name']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
                $result = $uploadService->uploadFile($_FILES['site_logo'], $uploadDir, ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif'], 2097152, 600, 200, 85);
                if ($result['success']) {
                    $deleteOldAsset($currentSettings['site_logo'] ?? '');
                    $settings['site_logo'] = 'img/branding/' . $result['filename'];
                } else {
                    Application::$app->session->setFlash('error', 'Site logo upload failed: ' . $result['error']);
                    return redirect('/admin/settings');
                }
            }

            // Process Favicon: Option to remove or replace
            if (!empty($data['remove_favicon'])) {
                $deleteOldAsset($currentSettings['site_favicon'] ?? '');
                $settings['site_favicon'] = '';
            }
            if (isset($_FILES['site_favicon']) && !empty($_FILES['site_favicon']['name']) && $_FILES['site_favicon']['error'] === UPLOAD_ERR_OK) {
                $result = $uploadService->uploadFile($_FILES['site_favicon'], $uploadDir, ['ico', 'png', 'svg', 'jpg', 'jpeg'], 2097152, 128, 128, 85);
                if ($result['success']) {
                    $deleteOldAsset($currentSettings['site_favicon'] ?? '');
                    $settings['site_favicon'] = 'img/branding/' . $result['filename'];
                } else {
                    Application::$app->session->setFlash('error', 'Favicon upload failed: ' . $result['error']);
                    return redirect('/admin/settings');
                }
            }

            // Process Announcement Image: Option to remove or replace
            if (!empty($data['remove_announcement_image'])) {
                $deleteOldAsset($currentSettings['announcement_image'] ?? '');
                $settings['announcement_image'] = '';
            }
            if (isset($_FILES['announcement_image']) && !empty($_FILES['announcement_image']['name']) && $_FILES['announcement_image']['error'] === UPLOAD_ERR_OK) {
                $annUploadDir = public_dir('assets/img/announcements/');
                \Ace\UploadService::ensureDirectory($annUploadDir);
                $result = $uploadService->uploadFile($_FILES['announcement_image'], $annUploadDir, ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif'], 3145728, 1200, 800, 85);
                if ($result['success']) {
                    $deleteOldAsset($currentSettings['announcement_image'] ?? '');
                    $settings['announcement_image'] = 'img/announcements/' . $result['filename'];
                } else {
                    Application::$app->session->setFlash('error', 'Announcement image upload failed: ' . $result['error']);
                    return redirect('/admin/settings');
                }
            } elseif (!empty($data['announcement_image_url'])) {
                $deleteOldAsset($currentSettings['announcement_image'] ?? '');
                $settings['announcement_image'] = trim($data['announcement_image_url']);
            }

            @file_put_contents($this->settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
            // Invalidate compiled views so updated site branding/title takes effect immediately
            if (isset(Application::$app->view)) {
                Application::$app->view->clearCache();
            }
            Application::$app->session->setFlash('success', 'Admin settings updated and view cache refreshed successfully!');
        } else {
            Application::$app->session->setFlash('error', 'Invalid settings action.');
        }

        return redirect('/admin/settings');
    }

    /**
     * Purge all compiled Blade view templates.
     */
    public function clearCache(Request $request)
    {
        $count = 0;
        if (isset(Application::$app->view)) {
            $count = Application::$app->view->clearCache();
        }
        Application::$app->session->setFlash('success', "View cache purged successfully! ({$count} compiled views refreshed)");
        return redirect('/admin/settings');
    }
}