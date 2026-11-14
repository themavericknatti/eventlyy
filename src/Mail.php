<?php

namespace Ace;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{
    private static ?string $lastError = null;

    /**
     * Get the last error message encountered during mail operations.
     */
    public static function getLastError(): ?string
    {
        return self::$lastError;
    }

    /**
     * Configure a PHPMailer instance with robust environment settings.
     * Fully compatible with port 587 (STARTTLS), port 465 (SMTPS), and custom relays.
     */
    private static function configureMailer(PHPMailer $mail): void
    {
        $mail->isSMTP();
        $mail->Timeout = (int) env('MAIL_TIMEOUT', 15);
        $mail->Host    = trim((string) env('MAIL_HOST', 'mail.eventlyy.com'));

        $mail->SMTPAuth = !empty(env('MAIL_USERNAME'));
        if ($mail->SMTPAuth) {
            $mail->Username = trim((string) env('MAIL_USERNAME'));
            $mail->Password = (string) env('MAIL_PASSWORD', '');
        }

        $port = (int) env('MAIL_PORT', 587);
        $mail->Port = $port;

        $encryption = strtolower(trim((string) env('MAIL_ENCRYPTION', '')));
        if ($encryption === 'ssl' || $encryption === 'smtps' || ($encryption === '' && $port === 465)) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($encryption === 'tls' || $encryption === 'starttls' || ($encryption === '' && in_array($port, [587, 25, 2525]))) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'none' || $encryption === 'null' || $encryption === 'false') {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        }

        // SSL options for cPanel, self-signed certificates & shared hosting compatibility
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        // Resolving from address
        $fromAddress = trim((string) env('MAIL_FROM_ADDRESS', ''));
        if (empty($fromAddress)) {
            $fromAddress = site_setting('support_email', env('MAIL_USERNAME', 'support@eventlyy.com'));
        }
        $fromName = trim((string) env('MAIL_FROM_NAME', ''));
        if (empty($fromName)) {
            $fromName = site_setting('site_title', 'Eventlyy');
        }

        $mail->setFrom($fromAddress, $fromName);
        $mail->CharSet  = PHPMailer::CHARSET_UTF8;
        $mail->Encoding = PHPMailer::ENCODING_BASE64;
        $mail->isHTML(true);
    }

    /**
     * Automatically scan the rendered HTML body for ticket QR codes and branding logos,
     * embed their PNG bytes as inline MIME attachments (CID), and rewrite their img src to cid:
     * This guarantees 100% visibility in Gmail, Outlook, Apple Mail, Yahoo, Mailtrap,
     * even on localhost / local virtual hosts / offline / behind firewalls!
     *
     * @param PHPMailer $mail The PHPMailer instance
     * @param string $body The rendered HTML email body
     * @return string The rewritten HTML body with cid: references
     */
    public static function processInlineAttachments(PHPMailer $mail, string $body): string
    {
        $embeddedCids = [];

        // 1. Match any /ticket/qr/{code} URLs and convert them to inline CID attachments
        if (preg_match_all('#src=["\']([^"\']*\/ticket\/qr\/([a-zA-Z0-9_\-]+)(?:\?[^"\']*)?)["\']#i', $body, $matches, PREG_SET_ORDER)) {
            $qrService = new \App\Services\TicketQrService();
            foreach ($matches as $match) {
                $fullImgSrc = $match[0];
                $ticketCode = $match[2];
                $cleanCid = 'qr_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $ticketCode);

                if (!isset($embeddedCids[$cleanCid])) {
                    try {
                        $png = $qrService->generateQrPng($ticketCode, 260);
                        if (!empty($png)) {
                            $mail->addStringEmbeddedImage($png, $cleanCid, $ticketCode . '.png', PHPMailer::ENCODING_BASE64, 'image/png');
                            $embeddedCids[$cleanCid] = true;
                        }
                    } catch (\Throwable $e) {
                        Logger::error("Failed to embed QR code for {$ticketCode}: " . $e->getMessage());
                    }
                }

                if (isset($embeddedCids[$cleanCid])) {
                    $body = str_replace($fullImgSrc, 'src="cid:' . $cleanCid . '"', $body);
                }
            }
        }

        // 2. Match any explicit cid:qr_{code} references in the HTML
        if (preg_match_all('#cid:(qr_([a-zA-Z0-9_\-]+))#i', $body, $matches, PREG_SET_ORDER)) {
            $qrService = new \App\Services\TicketQrService();
            foreach ($matches as $match) {
                $fullCid = $match[1];
                $ticketCode = $match[2];

                if (!isset($embeddedCids[$fullCid])) {
                    try {
                        $png = $qrService->generateQrPng($ticketCode, 260);
                        if (!empty($png)) {
                            $mail->addStringEmbeddedImage($png, $fullCid, $ticketCode . '.png', PHPMailer::ENCODING_BASE64, 'image/png');
                            $embeddedCids[$fullCid] = true;
                        }
                    } catch (\Throwable $e) {
                        Logger::error("Failed to embed QR code for {$ticketCode}: " . $e->getMessage());
                    }
                }
            }
        }

        // 3. Match and embed site logo
        if (str_contains($body, 'cid:site_logo') || preg_match('#src=["\'](?:data:image\/[^;]+;base64,[^"\']+|[^"\']*(?:assets\/img\/branding\/|uploads\/branding\/)[^"\']*)["\']#i', $body)) {
            $logoPath = function_exists('resolve_site_logo_path') ? resolve_site_logo_path() : null;
            if ($logoPath && file_exists($logoPath) && !isset($embeddedCids['site_logo'])) {
                try {
                    $logoBytes = @file_get_contents($logoPath);
                    if ($logoBytes !== false && strlen($logoBytes) > 50) {
                        $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                        $mime = match($ext) {
                            'png' => 'image/png',
                            'jpg', 'jpeg' => 'image/jpeg',
                            'gif' => 'image/gif',
                            'svg' => 'image/svg+xml',
                            'webp' => 'image/webp',
                            default => 'image/png'
                        };
                        $mail->addStringEmbeddedImage($logoBytes, 'site_logo', 'brand-logo.' . $ext, PHPMailer::ENCODING_BASE64, $mime);
                        $embeddedCids['site_logo'] = true;

                        // Rewrite any remaining raw data URIs or local file paths in img src to cid:site_logo
                        $body = preg_replace(
                            '#src=["\'](?:data:image\/[^;]+;base64,[^"\']+|[^"\']*(?:assets\/img\/branding\/|uploads\/branding\/)[^"\']*)["\']#i',
                            'src="cid:site_logo"',
                            $body
                        );
                    }
                } catch (\Throwable $e) {
                    Logger::error("Failed to embed site logo: " . $e->getMessage());
                }
            }
        }

        return $body;
    }

    /**
     * Send an email using a view template for the body with automated retries and fallback.
     *
     * @param string|array $to Single email address or array of addresses
     * @param string $subject The email subject
     * @param string $view The view template path for the HTML body
     * @param array $params Variables to pass to the view
     * @param int $maxRetries Maximum retry attempts on transient network/SMTP failure
     * @return bool True if sent, false otherwise
     */
    public static function send(string|array $to, string $subject, string $view, array $params = [], int $maxRetries = 2): bool
    {
        self::$lastError = null;

        // Render view content once
        try {
            $body = Application::$app->view->render($view, $params);
            $altBody = strip_tags($body);
        } catch (\Throwable $renderErr) {
            self::$lastError = "Template render failed for {$view}: " . $renderErr->getMessage();
            Logger::error(self::$lastError);
            return false;
        }

        $addresses = is_array($to) ? array_filter(array_map('trim', $to)) : [trim($to)];
        if (empty($addresses)) {
            self::$lastError = 'No recipient address provided.';
            return false;
        }

        // 1. Attempt delivery via configured SMTP (with automated retries on transient failures)
        $attempts = 0;
        $maxAttempts = max(1, $maxRetries + 1);

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                $mail = new PHPMailer(true);
                self::configureMailer($mail);

                // Auto-embed inline images (QR codes and logo)
                $finalBody = self::processInlineAttachments($mail, $body);

                foreach ($addresses as $address) {
                    $mail->addAddress($address);
                }

                $mail->Subject = $subject;
                $mail->Body    = $finalBody;
                $mail->AltBody = $altBody;

                if ($mail->send()) {
                    return true;
                }

                self::$lastError = $mail->ErrorInfo ?: 'Unknown SMTP failure';
            } catch (\Throwable $e) {
                self::$lastError = $e->getMessage();
                Logger::warning("Mail send attempt {$attempts}/{$maxAttempts} failed for " . implode(', ', $addresses) . ": {$e->getMessage()}");
            }

            if ($attempts < $maxAttempts) {
                // Exponential backoff: 300ms, 600ms
                usleep(300000 * $attempts);
            }
        }

        // 2. Fallback: If external SMTP relay fails or is unreachable, attempt delivery via PHP native mail()
        try {
            $fallbackMail = new PHPMailer(true);
            $fallbackMail->isMail();
            $fallbackMail->CharSet = PHPMailer::CHARSET_UTF8;
            $fallbackMail->isHTML(true);

            $finalBody = self::processInlineAttachments($fallbackMail, $body);

            $fromAddress = trim((string) env('MAIL_FROM_ADDRESS', ''));
            if (empty($fromAddress)) {
                $fromAddress = site_setting('support_email', env('MAIL_USERNAME', 'support@eventlyy.com'));
            }
            $fromName = trim((string) env('MAIL_FROM_NAME', ''));
            if (empty($fromName)) {
                $fromName = site_setting('site_title', 'Eventlyy');
            }
            $fallbackMail->setFrom($fromAddress, $fromName);

            foreach ($addresses as $address) {
                $fallbackMail->addAddress($address);
            }

            $fallbackMail->Subject = $subject;
            $fallbackMail->Body    = $finalBody;
            $fallbackMail->AltBody = $altBody;

            if ($fallbackMail->send()) {
                Logger::info("Mail to " . implode(', ', $addresses) . " delivered successfully via local mail() fallback after SMTP retry failure.");
                return true;
            }
        } catch (\Throwable $fbErr) {
            Logger::error("Native mail() fallback also failed for " . implode(', ', $addresses) . ": " . $fbErr->getMessage());
        }

        Logger::error("All mail dispatch attempts failed for " . implode(', ', $addresses) . ". Last Error: " . (self::$lastError ?? 'unknown'));
        return false;
    }

    /**
     * Send multiple distinct messages (e.g. ticket confirmation to buyer + individual passes to guests)
     * using a single persistent SMTP connection (Keep-Alive) with automatic fallback.
     *
     * @param array $messages Array of items: ['to' => string, 'subject' => string, 'view' => string, 'params' => array, 'name' => ?string]
     * @return array Summary of dispatch results ['total' => int, 'sent' => int, 'failed' => int, 'details' => array]
     */
    public static function sendMessages(array $messages): array
    {
        $total = count($messages);
        if ($total === 0) {
            return ['total' => 0, 'sent' => 0, 'failed' => 0, 'details' => []];
        }

        // If only 1 message, use standard send
        if ($total === 1) {
            $m = reset($messages);
            $ok = self::send($m['to'], $m['subject'], $m['view'], $m['params'] ?? []);
            return [
                'total' => 1,
                'sent' => $ok ? 1 : 0,
                'failed' => $ok ? 0 : 1,
                'details' => [$m['to'] => $ok ? 'sent' : (self::$lastError ?: 'failed')]
            ];
        }

        $sentCount = 0;
        $failedCount = 0;
        $details = [];

        $mail = new PHPMailer(true);
        $keepAliveActive = false;

        try {
            self::configureMailer($mail);
            $mail->SMTPKeepAlive = true;
            $keepAliveActive = true;
        } catch (\Throwable $e) {
            Logger::warning("Could not initialize persistent SMTP connection for batch: " . $e->getMessage());
        }

        foreach ($messages as $msg) {
            $to = trim($msg['to'] ?? '');
            $name = trim($msg['name'] ?? '');
            $subject = $msg['subject'] ?? 'Notification';
            $view = $msg['view'] ?? '';
            $params = $msg['params'] ?? [];

            if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $failedCount++;
                $details[$to ?: 'empty'] = 'Invalid email address';
                continue;
            }

            $sent = false;

            if ($keepAliveActive) {
                try {
                    $mail->clearAddresses();
                    $mail->clearCustomHeaders();
                    $mail->clearAttachments();

                    $mail->addAddress($to, $name);

                    $body = Application::$app->view->render($view, $params);
                    $finalBody = self::processInlineAttachments($mail, $body);

                    $mail->Subject = $subject;
                    $mail->Body    = $finalBody;
                    $mail->AltBody = strip_tags($body);

                    if ($mail->send()) {
                        $sent = true;
                    }
                } catch (\Throwable $e) {
                    Logger::warning("Persistent SMTP send failed for {$to}: " . $e->getMessage() . ". Retrying via fallback...");
                    // Try to reconnect SMTP if disconnected
                    if ($mail->getSMTPInstance() && !$mail->getSMTPInstance()->connected()) {
                        try {
                            $mail->smtpConnect();
                        } catch (\Throwable $reErr) {
                            $keepAliveActive = false;
                        }
                    }
                }
            }

            // If persistent attempt failed or was not available, use individual self::send (which includes retries & mail() fallback)
            if (!$sent) {
                $sent = self::send($to, $subject, $view, $params);
            }

            if ($sent) {
                $sentCount++;
                $details[$to] = 'sent';
            } else {
                $failedCount++;
                $details[$to] = self::$lastError ?: 'failed';
            }
        }

        if ($keepAliveActive) {
            try {
                $mail->smtpClose();
            } catch (\Throwable $e) {
                // Ignore close error
            }
        }

        return [
            'total' => $total,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'details' => $details
        ];
    }

    /**
     * Send batch emails using a persistent SMTP connection (Keep-Alive).
     * Greatly improves throughput and reliability in production for broadcasts.
     *
     * @param array $recipients Array of ['email' => string, 'name' => string]
     * @param string $subject Email subject
     * @param string $view View template path
     * @param callable $paramsCallback function(array $recipient): array
     * @return array ['total' => int, 'sent' => int, 'failed' => int, 'errors' => array]
     */
    public static function sendBatch(array $recipients, string $subject, string $view, callable $paramsCallback): array
    {
        self::$lastError = null;
        $total = count($recipients);
        $sentCount = 0;
        $failedCount = 0;
        $errors = [];

        if ($total === 0) {
            return ['total' => 0, 'sent' => 0, 'failed' => 0, 'errors' => []];
        }

        $mail = new PHPMailer(true);

        try {
            self::configureMailer($mail);
            $mail->SMTPKeepAlive = true; // Keep connection alive for batch
        } catch (\Throwable $e) {
            self::$lastError = $e->getMessage();
            Logger::error("Failed to initialize SMTP batch session: {$e->getMessage()}");
            return [
                'total' => $total,
                'sent' => 0,
                'failed' => $total,
                'errors' => [$e->getMessage()],
            ];
        }

        foreach ($recipients as $recipient) {
            $email = trim($recipient['email'] ?? '');
            $name  = trim($recipient['name'] ?? '');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $failedCount++;
                $errors[$email ?: 'unknown'] = 'Invalid email address';
                continue;
            }

            try {
                $mail->clearAddresses();
                $mail->clearCustomHeaders();
                $mail->clearAttachments();

                $mail->addAddress($email, $name);

                $params = $paramsCallback($recipient);
                $body = Application::$app->view->render($view, $params);
                $finalBody = self::processInlineAttachments($mail, $body);

                $mail->Subject = $subject;
                $mail->Body    = $finalBody;
                $mail->AltBody = strip_tags($body);

                if ($mail->send()) {
                    $sentCount++;
                } else {
                    $failedCount++;
                    $errMsg = $mail->ErrorInfo ?: 'Unknown send failure';
                    $errors[$email] = $errMsg;
                    self::$lastError = $errMsg;
                }
            } catch (\Throwable $e) {
                $failedCount++;
                $errors[$email] = $e->getMessage();
                self::$lastError = $e->getMessage();
                Logger::error("Batch send failure for {$email}: {$e->getMessage()}");

                // If connection was lost, attempt to reconnect for remaining recipients
                if (!$mail->getSMTPInstance()->connected()) {
                    try {
                        $mail->smtpConnect();
                    } catch (\Throwable $reconnectErr) {
                        Logger::error("SMTP reconnection failed: {$reconnectErr->getMessage()}");
                    }
                }
            }
        }

        try {
            $mail->smtpClose();
        } catch (\Throwable $e) {
            // Ignore close exceptions
        }

        return [
            'total' => $total,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'errors' => $errors,
        ];
    }
}
