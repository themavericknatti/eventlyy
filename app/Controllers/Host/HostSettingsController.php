<?php

/**
 * HostSettingsController
 *
 * This controller handles host settings routes.
 *
 * @package Evently
 * @subpackage Controllers/HostSettings
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers\Host;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\HostMiddleware;
use App\Services\HostService;
use App\Actions\SaveHostSettingsAction;

class HostSettingsController extends Controller
{
    protected HostService $hostService;
    protected SaveHostSettingsAction $saveHostSettingsAction;

    public function __construct()
    {
        // Register the HostMiddleware for all methods in this controller
        $this->registerMiddleware(new HostMiddleware());
        $this->hostService = new HostService();
        $this->saveHostSettingsAction = new SaveHostSettingsAction();
    }

    public function settings(Request $request)
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;

        // Fetch host profile
        $stmt = $db->prepare("SELECT * FROM host_profiles WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user->id]);
        $profile = $stmt->fetch() ?: [];

        // Fetch stats via HostService
        $stats = $this->hostService->getSettingsStats($user->id);

        // Fetch supported banks for payout dropdown
        $supportedBanks = \Ace\PaystackService::getSupportedBanks();

        return $this->render('host/settings', array_merge($stats, [
            'user' => $user,
            'profile' => $profile,
            'supportedBanks' => $supportedBanks
        ]));
    }

    public function saveSettings(Request $request)
    {
        $user = Application::$app->user;
        $data = $request->getBody();

        if ($this->saveHostSettingsAction->execute($data, $user)) {
            Application::$app->session->setFlash('success', 'Settings saved successfully!');
        } else {
            Application::$app->session->setFlash('error', 'Failed to save settings.');
        }

        return Application::$app->response->redirect('/host/settings');
    }
}