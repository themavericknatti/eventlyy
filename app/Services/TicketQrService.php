<?php

namespace App\Services;

use Ace\Application;

class TicketQrService
{
    /**
     * Generate or fetch a cached high-contrast QR code composited with the Eventlyy brand logo.
     * Uses Level H (30%) Reed-Solomon Error Correction and a protective white rounded badge
     * to guarantee optical gate scanning reliability.
     *
     * @param string $ticketCode Unique ticket admission code
     * @param int $size Dimension in pixels (default 240, clamped between 120 and 800)
     * @return string Binary PNG data
     */
    public function generateQrPng(string $ticketCode, int $size = 240): string
    {
        $code = trim($ticketCode);
        $size = max(120, min(800, $size));

        $cacheDir = (Application::$ROOT_DIR ?? dirname(__DIR__, 2)) . '/storage/cache/qr';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        $logoPath = $this->resolveLogoPath();
        $logoMtime = ($logoPath && file_exists($logoPath)) ? (string)filemtime($logoPath) : 'no-logo';
        $cacheKey = md5("qr_v1_{$code}_{$size}_{$logoMtime}");
        $cacheFile = $cacheDir . '/' . $cacheKey . '.png';

        // Serve from disk cache if already generated
        if (file_exists($cacheFile) && filesize($cacheFile) > 0) {
            $cached = @file_get_contents($cacheFile);
            if ($cached !== false && strlen($cached) > 0) {
                return $cached;
            }
        }

        // Fetch high-error-correction base QR code
        $baseQrPng = $this->fetchBaseQrCode($code, $size);
        if (!$baseQrPng) {
            // Fallback emergency blank/simple image if external API is unreachable
            return $this->generateEmergencyFallback($code, $size);
        }

        // If no logo available or GD not loaded, return base QR directly
        if (!$logoPath || !file_exists($logoPath) || !extension_loaded('gd')) {
            @file_put_contents($cacheFile, $baseQrPng);
            return $baseQrPng;
        }

        // Composite the logo onto the center
        $compositedPng = $this->compositeLogo($baseQrPng, $logoPath, $size);
        if ($compositedPng) {
            @file_put_contents($cacheFile, $compositedPng);
            return $compositedPng;
        }

        return $baseQrPng;
    }

    /**
     * Fetch the raw QR code with Level H Error Correction from QR Server API.
     */
    protected function fetchBaseQrCode(string $code, int $size): ?string
    {
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&margin=4&ecc=H&color=0-0-0&bgcolor=255-255-255&data=" . urlencode($code);

        if (function_exists('curl_init')) {
            $ch = curl_init($qrUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Eventlyy-Ticket-Engine/1.0');
            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && !empty($data)) {
                return $data;
            }
        }

        $context = stream_context_create([
            'http' => ['timeout' => 8, 'user_agent' => 'Eventlyy-Ticket-Engine/1.0'],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        $data = @file_get_contents($qrUrl, false, $context);
        return $data ?: null;
    }

    /**
     * Composite the Eventlyy logo onto the center of the QR code using PHP GD.
     */
    protected function compositeLogo(string $qrPngData, string $logoPath, int $size): ?string
    {
        $qrImage = @imagecreatefromstring($qrPngData);
        if (!$qrImage) {
            return null;
        }

        $logoInfo = @getimagesize($logoPath);
        if (!$logoInfo) {
            imagedestroy($qrImage);
            return null;
        }

        $logoImage = null;
        switch ($logoInfo['mime'] ?? '') {
            case 'image/png':
                $logoImage = @imagecreatefrompng($logoPath);
                break;
            case 'image/jpeg':
                $logoImage = @imagecreatefromjpeg($logoPath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $logoImage = @imagecreatefromwebp($logoPath);
                }
                break;
        }

        if (!$logoImage) {
            imagedestroy($qrImage);
            return null;
        }

        imagealphablending($qrImage, true);
        imagesavealpha($qrImage, true);

        $qrWidth = imagesx($qrImage);
        $qrHeight = imagesy($qrImage);

        // Target logo size: 20% of QR dimensions (safe for ECC Level H)
        $logoTargetSize = (int)round($qrWidth * 0.20);
        $badgePadding = (int)round($qrWidth * 0.025); // Protective white border
        $badgeSize = $logoTargetSize + ($badgePadding * 2);

        $badgeX1 = (int)round(($qrWidth - $badgeSize) / 2);
        $badgeY1 = (int)round(($qrHeight - $badgeSize) / 2);
        $badgeX2 = $badgeX1 + $badgeSize;
        $badgeY2 = $badgeY1 + $badgeSize;
        $radius = (int)round($badgeSize * 0.22); // Smooth modern rounded corner

        $white = imagecolorallocate($qrImage, 255, 255, 255);
        $borderColor = imagecolorallocate($qrImage, 226, 232, 240); // Soft subtle slate border (#e2e8f0)

        // Draw outer 1px border
        $this->drawRoundedRect($qrImage, $badgeX1 - 1, $badgeY1 - 1, $badgeX2 + 1, $badgeY2 + 1, $radius + 1, $borderColor);

        // Draw solid white protective background
        $this->drawRoundedRect($qrImage, $badgeX1, $badgeY1, $badgeX2, $badgeY2, $radius, $white);

        // Copy and resample logo with alpha transparency
        $logoX = $badgeX1 + $badgePadding;
        $logoY = $badgeY1 + $badgePadding;

        imagealphablending($logoImage, true);
        imagecopyresampled(
            $qrImage,
            $logoImage,
            $logoX,
            $logoY,
            0,
            0,
            $logoTargetSize,
            $logoTargetSize,
            imagesx($logoImage),
            imagesy($logoImage)
        );

        ob_start();
        imagepng($qrImage, null, 9);
        $finalPng = ob_get_clean();

        imagedestroy($qrImage);
        imagedestroy($logoImage);

        return $finalPng ?: null;
    }

    /**
     * Draw a smooth rounded rectangle filled with a given color.
     */
    protected function drawRoundedRect($img, int $x1, int $y1, int $x2, int $y2, int $r, int $color): void
    {
        $r = max(1, $r);
        imagefilledrectangle($img, $x1 + $r, $y1, $x2 - $r, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $r, $x2, $y2 - $r, $color);
        imagefilledellipse($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $color);
    }

    /**
     * Locate the physical image path of the Eventlyy brand logo.
     * Fully compatible with:
     * - Live production cPanel / DirectAdmin (domains/eventlyy.com/public_html/assets/...)
     * - Local development (public/assets/...)
     * - Web server DOCUMENT_ROOT
     */
    protected function resolveLogoPath(): ?string
    {
        $baseDirs = $this->getPublicBaseDirs();

        // 1. Check configured site_logo in settings
        $configuredLogo = function_exists('site_setting') ? site_setting('site_logo') : null;
        if (!empty($configuredLogo)) {
            $path = $this->findFileInBaseDirs($configuredLogo, $baseDirs);
            if ($path) {
                return $path;
            }
        }

        // 2. Check site_favicon as crisp icon mark
        $configuredFavicon = function_exists('site_setting') ? site_setting('site_favicon') : null;
        if (!empty($configuredFavicon)) {
            $path = $this->findFileInBaseDirs($configuredFavicon, $baseDirs);
            if ($path) {
                return $path;
            }
        }

        // 3. Fallback scan in branding directories (public_html/assets/img/branding, public/assets/img/branding, etc.)
        foreach ($baseDirs as $dir) {
            $scanDirs = [
                $dir . '/assets/img/branding',
                $dir . '/img/branding',
                $dir . '/assets/img',
            ];
            foreach ($scanDirs as $sDir) {
                if (is_dir($sDir)) {
                    $files = glob($sDir . '/*.png');
                    if (!empty($files)) {
                        return $files[0];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Search for an image file across all candidate public root directories.
     */
    protected function findFileInBaseDirs(string $relativePath, array $baseDirs): ?string
    {
        $clean = ltrim($relativePath, '/\\');
        $raw = preg_replace('#^assets/#i', '', $clean);

        foreach ($baseDirs as $dir) {
            $candidates = [
                $dir . '/assets/' . $raw,
                $dir . '/' . $clean,
                $dir . '/' . $raw,
                $dir . '/assets/' . $clean,
                $dir . '/uploads/' . $clean,
            ];

            foreach ($candidates as $cand) {
                if (file_exists($cand) && is_file($cand) && is_readable($cand)) {
                    return $cand;
                }
            }
        }

        return null;
    }

    /**
     * Detect all candidate public directories on disk across local and live production hosting.
     * Supports cPanel, DirectAdmin (domains/eventlyy.com/public_html), Docker, and local XAMPP.
     */
    protected function getPublicBaseDirs(): array
    {
        $rootDir = Application::$ROOT_DIR ?? (defined('ROOT_DIR') ? ROOT_DIR : dirname(__DIR__, 2));
        $dirs = [];

        // 1. Web server DOCUMENT_ROOT (e.g. /home/user/domains/eventlyy.com/public_html)
        if (!empty($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT'])) {
            $dirs[] = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        }

        // 2. Explicit ACE_PUBLIC_DIR if defined by index.php
        if (defined('ACE_PUBLIC_DIR') && is_dir(ACE_PUBLIC_DIR)) {
            $dirs[] = rtrim(str_replace('\\', '/', ACE_PUBLIC_DIR), '/');
        }

        // 3. Application public_dir() helper if loaded
        if (function_exists('public_dir')) {
            $p = public_dir();
            if (is_dir($p)) {
                $dirs[] = rtrim(str_replace('\\', '/', $p), '/');
            }
        }

        // 4. Production public_html under application root (e.g. root/public_html)
        if (is_dir($rootDir . '/public_html')) {
            $dirs[] = rtrim(str_replace('\\', '/', $rootDir . '/public_html'), '/');
        }

        // 5. Production public_html in parent folder (common in cPanel / DirectAdmin: ../public_html)
        $parent = dirname($rootDir);
        if (is_dir($parent . '/public_html')) {
            $dirs[] = rtrim(str_replace('\\', '/', $parent . '/public_html'), '/');
        }

        // 6. DirectAdmin / cPanel domain specific path checks (domains/*/public_html)
        if (str_contains($rootDir, 'domains')) {
            $parts = explode('/domains/', str_replace('\\', '/', $rootDir));
            if (!empty($parts[1])) {
                $domainPart = explode('/', $parts[1])[0];
                $domPublicHtml = $parts[0] . '/domains/' . $domainPart . '/public_html';
                if (is_dir($domPublicHtml)) {
                    $dirs[] = rtrim($domPublicHtml, '/');
                }
            }
        }

        // 7. Local development public/ folder
        if (is_dir($rootDir . '/public')) {
            $dirs[] = rtrim(str_replace('\\', '/', $rootDir . '/public'), '/');
        }

        // 8. Application root directory itself
        if (is_dir($rootDir)) {
            $dirs[] = rtrim(str_replace('\\', '/', $rootDir), '/');
        }

        return array_values(array_unique($dirs));
    }

    /**
     * Emergency fallback image if external QR service is unreachable.
     */
    protected function generateEmergencyFallback(string $code, int $size): string
    {
        $im = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($im, 255, 255, 255);
        $dark = imagecolorallocate($im, 15, 23, 42);
        imagefilledrectangle($im, 0, 0, $size, $size, $white);
        imagerectangle($im, 2, 2, $size - 3, $size - 3, $dark);
        $text = "ADMISSION PASS\n" . substr($code, 0, 16);
        imagestring($im, 3, 10, (int)($size / 2) - 10, $text, $dark);
        ob_start();
        imagepng($im);
        $fallback = ob_get_clean();
        imagedestroy($im);
        return $fallback;
    }
}
