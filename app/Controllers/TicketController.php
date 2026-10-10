<?php

namespace App\Controllers;

use Ace\Controller;
use Ace\Request;
use App\Services\TicketQrService;

class TicketController extends Controller
{
    protected TicketQrService $qrService;

    public function __construct()
    {
        $this->qrService = new TicketQrService();
    }

    /**
     * Stream a high-resolution, branded ticket QR code with the Eventlyy logo.
     * Features HTTP caching (30 days, ETag, 304 Not Modified) for ultra-fast performance.
     *
     * @param Request $request
     * @param string $code Ticket admission code
     */
    public function qrCode(Request $request, string $code): void
    {
        $code = trim($code);
        if (empty($code)) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo 'Invalid ticket code';
            exit;
        }

        // Optional size query parameter: ?s=260
        $size = (int)($request->getInt('s') ?: 240);
        $size = max(120, min(800, $size));

        // Generate or retrieve cached composited PNG
        $png = $this->qrService->generateQrPng($code, $size);

        if (empty($png)) {
            http_response_code(500);
            header('Content-Type: text/plain');
            echo 'Failed to generate QR code';
            exit;
        }

        $etag = '"' . md5($png) . '"';

        // HTTP Caching: 304 Not Modified if browser/client already has it
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
            http_response_code(304);
            exit;
        }

        // Clean any preceding output buffers to avoid corrupting image bytes
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: image/png');
        header('Content-Length: ' . strlen($png));
        header('Cache-Control: public, max-age=2592000, immutable'); // 30 days
        header('ETag: ' . $etag);
        header('X-Content-Type-Options: nosniff');

        echo $png;
        exit;
    }
}
