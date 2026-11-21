<?php

namespace App\Controllers\Admin;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\AdminMiddleware;
use App\Services\SponsorService;

/**
 * AdminSponsorController
 *
 * Handles administrative management of platform sponsors, partners,
 * and the continuous marquee / sponsorship tape.
 *
 * @package Eventlyy\Controllers\Admin
 * @version 1.0.0
 */
class AdminSponsorController extends Controller
{
    protected SponsorService $sponsorService;

    public function __construct()
    {
        $this->registerMiddleware(new AdminMiddleware());
        $this->sponsorService = new SponsorService();
    }

    /**
     * Display the sponsor management dashboard.
     */
    public function index(Request $request)
    {
        $sponsors = $this->sponsorService->getAllSponsors();

        $activeCount = 0;
        $textCount = 0;
        $imageCount = 0;
        foreach ($sponsors as $s) {
            if (!empty($s['is_active'])) {
                $activeCount++;
            }
            if (($s['type'] ?? '') === 'text') {
                $textCount++;
            } elseif (($s['type'] ?? '') === 'image') {
                $imageCount++;
            }
        }

        return $this->render('admin/sponsors', [
            'title' => 'Sponsorship & Partners Tape Management — Admin',
            'sponsors' => $sponsors,
            'activeCount' => $activeCount,
            'textCount' => $textCount,
            'imageCount' => $imageCount,
            'tapeSettings' => [
                'enabled' => site_setting('sponsors_tape_enabled', '1'),
                'title' => site_setting('sponsors_tape_title', 'OFFICIAL PARTNERS & PATRONS'),
                'speed' => site_setting('sponsors_tape_speed', 'normal'), // slow, normal, fast
            ]
        ]);
    }

    /**
     * Create a new sponsor.
     */
    public function store(Request $request)
    {
        $data = $request->getBody();
        $file = $_FILES['logo_file'] ?? null;

        $res = $this->sponsorService->createSponsor($data, $file);

        if ($request->isAjax() || str_contains($request->getHeader('accept') ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode($res);
            exit;
        }

        if ($res['success']) {
            Application::$app->session->setFlash('success', $res['message']);
        } else {
            Application::$app->session->setFlash('error', $res['message']);
        }

        return redirect('/admin/sponsors');
    }

    /**
     * Update an existing sponsor.
     */
    public function update(Request $request, int|string $id)
    {
        $data = $request->getBody();
        $file = $_FILES['logo_file'] ?? null;

        $res = $this->sponsorService->updateSponsor((int)$id, $data, $file);

        if ($request->isAjax() || str_contains($request->getHeader('accept') ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode($res);
            exit;
        }

        if ($res['success']) {
            Application::$app->session->setFlash('success', $res['message']);
        } else {
            Application::$app->session->setFlash('error', $res['message']);
        }

        return redirect('/admin/sponsors');
    }

    /**
     * Delete a sponsor.
     */
    public function delete(Request $request, int|string $id)
    {
        $res = $this->sponsorService->deleteSponsor((int)$id);

        if ($request->isAjax() || str_contains($request->getHeader('accept') ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode($res);
            exit;
        }

        if ($res['success']) {
            Application::$app->session->setFlash('success', $res['message']);
        } else {
            Application::$app->session->setFlash('error', $res['message']);
        }

        return redirect('/admin/sponsors');
    }

    /**
     * Toggle sponsor active status.
     */
    public function toggle(Request $request, int|string $id)
    {
        $res = $this->sponsorService->toggleStatus((int)$id);

        if ($request->isAjax() || str_contains($request->getHeader('accept') ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode($res);
            exit;
        }

        Application::$app->session->setFlash('info', $res['message']);
        return redirect('/admin/sponsors');
    }

    /**
     * Reorder sponsors via drag & drop.
     */
    public function reorder(Request $request)
    {
        $data = $request->getBody();
        $order = $data['order'] ?? [];

        if (is_array($order) && !empty($order)) {
            $this->sponsorService->reorder($order);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Update global tape settings.
     */
    public function saveSettings(Request $request)
    {
        $data = $request->getBody();
        $settingsFile = config_dir('settings.json');
        $currentSettings = file_exists($settingsFile) ? (json_decode(file_get_contents($settingsFile), true) ?: []) : [];

        $currentSettings['sponsors_tape_enabled'] = !empty($data['sponsors_tape_enabled']) ? '1' : '0';
        $currentSettings['sponsors_tape_title'] = trim($data['sponsors_tape_title'] ?? 'OFFICIAL PARTNERS & PATRONS');
        $currentSettings['sponsors_tape_speed'] = in_array($data['sponsors_tape_speed'] ?? '', ['slow', 'normal', 'fast']) ? $data['sponsors_tape_speed'] : 'normal';

        @file_put_contents($settingsFile, json_encode($currentSettings, JSON_PRETTY_PRINT));

        Application::$app->session->setFlash('success', 'Sponsorship tape settings updated successfully!');
        return redirect('/admin/sponsors');
    }
}
