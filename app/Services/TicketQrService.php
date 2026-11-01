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
     * Render a branded QR code with matrix-aware logo space reservation and center emblem.
     *
     * Uses chillerlan/php-qrcode to generate the QR matrix with a 7×7 module quiet zone
     * in the center (via addLogoSpace), then composites a teal circle with a cursive italic
     * "E" character rendered in the InstrumentSerif-Italic font.
     *
     * The approach ensures:
     * 1. The QR matrix algorithm reserves empty space — no Reed-Solomon codewords destroyed
     * 2. ECC Level H (30%) provides ample error correction headroom
     * 3. The emblem is size-proportional and stays within the safe zone
     * 4. Output is resampled to the exact requested pixel dimension
     *
     * @param string $code The ticket code text to encode
     * @param int $targetSize Final output dimension in pixels
     * @return string Binary PNG data
     */
    protected function renderBrandedQr(string $code, int $targetSize): string
    {
        $scale = 10;          // High-res internal rendering scale
        $logoModules = 7;     // 7×7 modules reserved in center (safe for ECC-H)

        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'eccLevel'        => EccLevel::H,
            'scale'           => $scale,
            'outputBase64'    => false,
            'addLogoSpace'    => true,
            'logoSpaceWidth'  => $logoModules,
            'logoSpaceHeight' => $logoModules,
            'quietzoneSize'   => 4,
        ]);

        $qr = new QRCode($options);
        $rawPng = $qr->render($code);

        // If GD is not available, return the raw (un-badged) QR code
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
        $centerX = (int)($w / 2);
        $centerY = (int)($h / 2);

        // Badge fits snugly inside the reserved 7×7 module quiet zone
        $badgeSize = $logoModules * $scale;

        // Brand colors: Deep Emerald Teal (#0B7366) and Pure White (#FFFFFF)
        $white = imagecolorallocate($qrImg, 255, 255, 255);
        $teal  = imagecolorallocate($qrImg, 11, 115, 102);

        // 1. Outer protective white circular isolation halo
        imagefilledellipse($qrImg, $centerX, $centerY, $badgeSize, $badgeSize, $white);

        // 2. Inner brand teal circular badge (84% of outer = 2px white ring at scale 10)
        $innerBadge = (int)round($badgeSize * 0.84);
        imagefilledellipse($qrImg, $centerX, $centerY, $innerBadge, $innerBadge, $teal);

        // 3. Bold cursive italic "E" character
        $fontPath = $this->resolveFontPath();
        if ($fontPath && function_exists('imagettftext')) {
            $fontSize = (int)round($innerBadge * 0.58);
            $bbox = @imagettfbbox($fontSize, 0, $fontPath, 'E');
            if ($bbox) {
                $textWidth  = abs($bbox[4] - $bbox[0]);
                $textHeight = abs($bbox[5] - $bbox[1]);
                $tx = (int)round($centerX - ($textWidth / 2)) - $bbox[0] - 1;
                $ty = (int)round($centerY + ($textHeight / 2)) - 3;
                imagettftext($qrImg, $fontSize, 0, $tx, $ty, $white, $fontPath, 'E');
            }
        }

        // 4. Resample to the exact requested target dimension
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
