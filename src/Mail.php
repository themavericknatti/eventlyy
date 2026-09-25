<?php

namespace Ace;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{
    /**
     * Send an email using a view template for the body.
     *
     * @param string|array $to Single email address or array of addresses
     * @param string $subject The email subject
     * @param string $view The view template path for the HTML body
     * @param array $params Variables to pass to the view
     * @return bool True if sent, false otherwise
     */
    public static function send(string|array $to, string $subject, string $view, array $params = []): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST', 'mail.eventlyy.com');
            $mail->SMTPAuth   = !empty(env('MAIL_USERNAME'));
            if ($mail->SMTPAuth) {
                $mail->Username = env('MAIL_USERNAME');
                $mail->Password = env('MAIL_PASSWORD');
            }
            
            $encryption = strtolower(trim((string) env('MAIL_ENCRYPTION', 'tls')));
            if ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }

            $mail->Port = (int) env('MAIL_PORT', 587);

            // SSL options for cPanel / shared hosting compatibility
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];

            // Recipients
            $fromAddress = env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME', 'support@eventlyy.com'));
            $fromName    = env('MAIL_FROM_NAME', site_setting('site_title', 'Eventlyy'));
            $mail->setFrom($fromAddress, $fromName);

            if (is_array($to)) {
                foreach ($to as $address) {
                    $mail->addAddress($address);
                }
            } else {
                $mail->addAddress($to);
            }

            // Render view content for the body
            $body = Application::$app->view->render($view, $params);

            // Content
            $mail->CharSet  = PHPMailer::CHARSET_UTF8;
            $mail->Encoding = PHPMailer::ENCODING_BASE64;
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            return $mail->send();
        } catch (\Throwable $e) {
            Logger::error("Message could not be sent. Error: {$e->getMessage()}");
            return false;
        }
    }
}

