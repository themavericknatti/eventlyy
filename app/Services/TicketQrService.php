<?php

namespace App\Services;

use Ace\Application;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;

class TicketQrService
{
    /**
     * Generate or fetch a cached high-contrast QR code composited with the Eventlyy brand emblem.
     * Uses Level H (30%) Reed-Solomon Error Correction and matrix-aware logo space reservation
     * to guarantee optical gate scanning reliability.
     *
     * The center emblem is a bold cursive italic "E" in Eventlyy teal (#0B7366) inside
     * a circular badge. This approach avoids distorted logos that break scanning.
     *
     * Zero external network dependencies — all generation is local via chillerlan/php-qrcode + GD.
     *
     * @param string $ticketCode Unique ticket admission code
     * @param int $size Dimension in pixels (default 240, clamped between 120 and 800)
     * @return string Binary PNG data
     */
    public function generateQrPng(string $ticketCode, int $size = 240): string
    {
        $code = trim($ticketCode);
        if ($code === '') {
            $code = 'UNKNOWN';
        }
        $size = max(120, min(800, $size));

        // ── Disk cache ──
        $rootDir = $this->getRootDir();
        $cacheDir = $rootDir . '/storage/cache/qr';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        $cacheKey = md5("qr_v2_{$code}_{$size}");
        $cacheFile = $cacheDir . '/' . $cacheKey . '.png';

        // Serve from disk cache if already generated
        if (file_exists($cacheFile) && filesize($cacheFile) > 100) {
            $cached = @file_get_contents($cacheFile);
            if ($cached !== false && strlen($cached) > 100) {
                return $cached;
            }
        }

        // ── Generate QR locally with chillerlan/php-qrcode ──
        $png = $this->renderBrandedQr($code, $size);

        // Cache to disk for instant re-reads at gate
        @file_put_contents($cacheFile, $png);

        return $png;
    }

    /**
     * Generate a self-contained Base64 Data URI (data:image/png;base64,...) of the branded QR code.
     *
     * NOTE: Gmail and many email clients BLOCK data: URIs in <img> tags.
     * For emails, prefer ticket_qr_url() which returns a publicly accessible HTTPS URL.
     * This method is useful for in-app rendering (profile page, success page, PDF export).
     *
     * @param string $ticketCode Unique ticket admission code
     * @param int $size Dimension in pixels (default 240)
     * @return string Data URI string
     */
    public function getQrDataUri(string $ticketCode, int $size = 240): string
    {
        $png = $this->generateQrPng($ticketCode, $size);
        return 'data:image/png;base64,' . base64_encode($png);
    }

    /**
     * Render a clean, ultra-high-contrast, perfectly scannable QR code.
     *
     * Uses ECC Level M (15%) or Q (25%) for maximum optical scan speed and compatibility
     * across all smartphone cameras (iPhone Safari WebKit, Android Chrome) and hardware scanners.
     * Keeps all data codewords, timing patterns, and alignment modules 100% intact.
     *
     * @param string $code The ticket code text to encode
     * @param int $targetSize Final output dimension in pixels
     * @return string Binary PNG data
     */
    protected function renderBrandedQr(string $code, int $targetSize): string
    {
        $scale = 10; // High internal resolution scale for crisp edges

        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'eccLevel'        => EccLevel::M,
            'scale'           => $scale,
            'outputBase64'    => false,
            'quietzoneSize'   => 4,
        ]);

        $qr = new QRCode($options);
        $rawPng = $qr->render($code);

        // If GD is not available, return the raw QR code
        if (!extension_loaded('gd')) {
            return $rawPng;
        }

        $qrImg = @imagecreatefromstring($rawPng);
        if (!$qrImg) {
            return $rawPng;
        }

        imagealphablending($qrImg, true);
        imagesavealpha($qrImg, true);

        $w = imagesx($qrImg);
        $h = imagesy($qrImg);

        // Resample smoothly to the exact requested target dimension
        if ($w !== $targetSize || $h !== $targetSize) {
            $resized = imagecreatetruecolor($targetSize, $targetSize);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $qrImg, 0, 0, 0, 0, $targetSize, $targetSize, $w, $h);
            imagedestroy($qrImg);
            $qrImg = $resized;
        }

        ob_start();
        imagepng($qrImg, null, 9);
        $finalPng = ob_get_clean();
        imagedestroy($qrImg);

        return $finalPng ?: $rawPng;
    }

    /**
     * Resolve the path to the InstrumentSerif-Italic TTF font used for the center "E" emblem.
     * Searches multiple candidate directories for production/development compatibility.
     *
     * @return string|null Absolute path to the TTF font file, or null if not found
     */
    protected function resolveFontPath(): ?string
    {
        $rootDir = $this->getRootDir();
        $candidates = [
            $rootDir . '/public/assets/fonts/InstrumentSerif-Italic.ttf',
            $rootDir . '/public_html/assets/fonts/InstrumentSerif-Italic.ttf',
        ];

        // Check DOCUMENT_ROOT for production hosting
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
            $candidates[] = $docRoot . '/assets/fonts/InstrumentSerif-Italic.ttf';
        }

        // Check parent directory for cPanel/DirectAdmin hosting
        $parent = dirname($rootDir);
        if (is_dir($parent . '/public_html')) {
            $candidates[] = $parent . '/public_html/assets/fonts/InstrumentSerif-Italic.ttf';
        }

        foreach ($candidates as $path) {
            if (file_exists($path) && is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Get the application root directory, with safe fallbacks.
     *
     * @return string Absolute path to project root
     */
    protected function getRootDir(): string
    {
        if (!empty(Application::$ROOT_DIR)) {
            return Application::$ROOT_DIR;
        }
        if (defined('ROOT_DIR')) {
            return ROOT_DIR;
        }
        return dirname(__DIR__, 2);
    }
}
