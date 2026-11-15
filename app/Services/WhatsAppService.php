<?php

namespace App\Services;

use Ace\Application;
use Ace\Logger;

class WhatsAppService
{
    /**
     * Format and normalize international or domestic phone numbers into E.164 without plus.
     * e.g., 0801 234 5678 -> 2348012345678, +1 (555) 123-4567 -> 15551234567
     *
     * @param string $phone
     * @param string $defaultCountryCode e.g., '234' for Nigeria
     * @return string
     */
    public static function formatPhoneNumber(string $phone, string $defaultCountryCode = '234'): string
    {
        // Strip everything except digits
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (empty($digits)) {
            return '';
        }

        // If starts with 0 (domestic format e.g. 080... in Nigeria or UK), replace leading 0 with country code
        if (str_starts_with($digits, '0')) {
            $digits = $defaultCountryCode . substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Normalize array, ViewData, Model or stdClass into a plain associative array.
     */
    public static function normalizeData(mixed $data): array
    {
        if ($data === null) {
            return [];
        }
        if (is_array($data)) {
            return $data;
        }
        if ($data instanceof \Ace\ViewData) {
            return $data->toArray();
        }
        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                return $data->toArray();
            }
            if ($data instanceof \ArrayAccess || $data instanceof \Traversable) {
                $arr = [];
                foreach ($data as $k => $v) {
                    $arr[$k] = $v;
                }
                return $arr;
            }
            return get_object_vars($data);
        }
        return (array) $data;
    }

    /**
     * Build rich-formatted WhatsApp message for an issued ticket admission pass.
     *
     * @param mixed $event
     * @param mixed $ticket
     * @param string $siteTitle
     * @return string
     */
    public static function buildTicketPassMessage(mixed $event, mixed $ticket, string $siteTitle = 'Eventlyy'): string
    {
        $siteTitle = $siteTitle ?: site_setting('site_title', 'Eventlyy');
        $event = self::normalizeData($event);
        $ticket = self::normalizeData($ticket);

        $eventName = $event['name'] ?? 'Your Event';
        $attendeeName = !empty($ticket['attendee_name']) && $ticket['attendee_name'] !== 'Attendee' 
            ? $ticket['attendee_name'] 
            : 'Valued Attendee';
        $ticketCode = $ticket['ticket_code'] ?? ($ticket['id'] ?? '');
        $tierName = $ticket['ticket_type'] ?? ($ticket['ticket_tier'] ?? 'Standard Admission');

        $eventDate = !empty($event['date']) ? strtotime($event['date']) : null;
        $dateStr = $eventDate ? date('D, M d, Y • g:i A', $eventDate) : 'Date TBA';
        $location = $event['location'] ?? 'Venue TBA';

        $passUrl = self::getPassUrl($ticketCode);

        $msg = "🎟️ *Your Admission Pass is Ready!* \n\n";
        $msg .= "Hey *{$attendeeName}*, your ticket for *{$eventName}* is confirmed on {$siteTitle}! 🎉\n\n";
        $msg .= "🗓️ *Date:* {$dateStr}\n";
        $msg .= "📍 *Venue:* {$location}\n";
        $msg .= "🎫 *Tier:* {$tierName}\n";
        $msg .= "🔑 *Ticket Code:* `{$ticketCode}`\n\n";
        $msg .= "📲 *Your Scannable QR Pass & Gate Access:*\n";
        $msg .= "👉 {$passUrl}\n\n";
        $msg .= "💡 *Gate Instructions:* Keep this message handy or save your QR code. At the venue gate, display your QR pass with full screen brightness for instant scanning.\n\n";
        $msg .= "See you there! ✨\n— *{$siteTitle} Team*";

        return $msg;
    }

    /**
     * Build rich-formatted WhatsApp Event Day Alert message.
     *
     * @param mixed $event
     * @param string $alertType 'gates_open'|'reminder_2h'|'parking'|'schedule'|'custom'
     * @param string $customMessage
     * @param mixed $ticket
     * @param string $siteTitle
     * @return string
     */
    public static function buildEventDayAlertMessage(
        mixed $event,
        string $alertType = 'gates_open',
        string $customMessage = '',
        mixed $ticket = null,
        string $siteTitle = 'Eventlyy'
    ): string {
        $siteTitle = $siteTitle ?: site_setting('site_title', 'Eventlyy');
        $event = self::normalizeData($event);
        $ticket = !empty($ticket) ? self::normalizeData($ticket) : null;

        $eventName = $event['name'] ?? 'Your Event';
        $attendeeName = !empty($ticket['attendee_name']) && $ticket['attendee_name'] !== 'Attendee' 
            ? $ticket['attendee_name'] 
            : 'Attendee';
        $location = $event['location'] ?? 'Venue TBA';
        $ticketCode = $ticket ? ($ticket['ticket_code'] ?? ($ticket['id'] ?? '')) : '';
        $passUrl = !empty($ticketCode) ? self::getPassUrl($ticketCode) : '';

        $headline = match ($alertType) {
            'gates_open'  => "🚪 *GATES ARE NOW OPEN!*",
            'reminder_2h' => "⏳ *STARTING IN 2 HOURS!*",
            'parking'     => "🚗 *PARKING & DIRECTIONS NOTICE*",
            'schedule'    => "📍 *IMPORTANT EVENT SCHEDULE UPDATE*",
            default       => "📢 *EVENT DAY NOTICE*"
        };

        $msg = "{$headline}\n\n";
        $msg .= "Hey *{$attendeeName}*, today is the day for *{$eventName}*! 🎉\n\n";

        if (!empty($customMessage)) {
            $msg .= "📝 *Message from Organizer:*\n{$customMessage}\n\n";
        } elseif ($alertType === 'gates_open') {
            $msg .= "Doors and accreditation checkpoints are now active. Head to the main entrance for instant QR scanning and pass verification.\n\n";
        } elseif ($alertType === 'reminder_2h') {
            $msg .= "The event commences in approximately 2 hours. Please arrive early to beat the entry queues and secure optimal seating.\n\n";
        } elseif ($alertType === 'parking') {
            $msg .= "Designated parking is available at the venue grounds. Please have your digital ticket ready for parking marshals.\n\n";
        }

        $msg .= "📍 *Location:* {$location}\n";

        if (!empty($passUrl)) {
            $msg .= "🔑 *Your Ticket Code:* `{$ticketCode}`\n";
            $msg .= "📲 *Quick Gate QR Pass:* {$passUrl}\n\n";
        }

        $msg .= "✨ Enjoy the experience!\n— *{$siteTitle}*";

        return $msg;
    }

    /**
     * Generate direct one-click WhatsApp send link (wa.me / api.whatsapp.com).
     * Works seamlessly on mobile WhatsApp app, WhatsApp Web, and Desktop.
     *
     * @param string $phone Optional recipient phone number
     * @param string $message Text message payload
     * @return string
     */
    public static function generateDirectWhatsAppUrl(string $phone, string $message): string
    {
        $cleanPhone = self::formatPhoneNumber($phone);
        $encodedText = rawurlencode($message);

        if (!empty($cleanPhone)) {
            return "https://api.whatsapp.com/send?phone={$cleanPhone}&text={$encodedText}";
        }

        return "https://api.whatsapp.com/send?text={$encodedText}";
    }

    /**
     * Dispatch WhatsApp message via automated gateway (if configured)
     * with transparent fallback to direct WhatsApp URL.
     *
     * Supported gateway providers in settings.json or .env:
     * - 'termii': Termii SMS / WhatsApp API
     * - 'whatsapp_cloud': Meta Official WhatsApp Cloud API
     * - 'twilio': Twilio Programmable WhatsApp Messaging
     * - 'wa_direct' / default: Direct WhatsApp API link generator
     *
     * @param string $phone
     * @param string $message
     * @param array $options
     * @return array ['success' => bool, 'whatsapp_url' => string, 'provider' => string, 'message' => string]
     */
    public static function dispatch(string $phone, string $message, array $options = []): array
    {
        $formattedPhone = self::formatPhoneNumber($phone);
        $directUrl = self::generateDirectWhatsAppUrl($formattedPhone, $message);

        if (empty($formattedPhone)) {
            return [
                'success' => false,
                'provider' => 'none',
                'whatsapp_url' => $directUrl,
                'message' => 'No valid phone number provided for WhatsApp dispatch.'
            ];
        }

        // Check for active automated gateway provider
        $provider = strtolower((string)(setting('whatsapp_provider') ?: env('WHATSAPP_PROVIDER', 'wa_direct')));

        // 1. Termii Gateway Dispatch (widely used in Africa / Nigeria for WhatsApp & SMS)
        if ($provider === 'termii') {
            $apiKey = trim((string)(setting('termii_api_key') ?: env('TERMII_API_KEY', '')));
            if (!empty($apiKey)) {
                $termiiRes = self::sendViaTermii($formattedPhone, $message, $apiKey);
                if ($termiiRes['success']) {
                    return [
                        'success' => true,
                        'provider' => 'termii',
                        'whatsapp_url' => $directUrl,
                        'message' => 'WhatsApp / SMS pass dispatched successfully via Termii.'
                    ];
                }
            }
        }

        // 2. WhatsApp Cloud API (Meta Official)
        if ($provider === 'whatsapp_cloud') {
            $token = trim((string)(setting('whatsapp_cloud_token') ?: env('WHATSAPP_CLOUD_TOKEN', '')));
            $phoneId = trim((string)(setting('whatsapp_cloud_phone_id') ?: env('WHATSAPP_CLOUD_PHONE_ID', '')));
            if (!empty($token) && !empty($phoneId)) {
                $cloudRes = self::sendViaWhatsAppCloud($formattedPhone, $message, $phoneId, $token);
                if ($cloudRes['success']) {
                    return [
                        'success' => true,
                        'provider' => 'whatsapp_cloud',
                        'whatsapp_url' => $directUrl,
                        'message' => 'WhatsApp message dispatched successfully via Meta Cloud API.'
                    ];
                }
            }
        }

        // 3. Twilio WhatsApp Dispatch
        if ($provider === 'twilio') {
            $sid = trim((string)(setting('twilio_account_sid') ?: env('TWILIO_ACCOUNT_SID', '')));
            $token = trim((string)(setting('twilio_auth_token') ?: env('TWILIO_AUTH_TOKEN', '')));
            $from = trim((string)(setting('twilio_whatsapp_from') ?: env('TWILIO_WHATSAPP_FROM', '')));
            if (!empty($sid) && !empty($token) && !empty($from)) {
                $twilioRes = self::sendViaTwilio($formattedPhone, $message, $sid, $token, $from);
                if ($twilioRes['success']) {
                    return [
                        'success' => true,
                        'provider' => 'twilio',
                        'whatsapp_url' => $directUrl,
                        'message' => 'WhatsApp pass dispatched successfully via Twilio.'
                    ];
                }
            }
        }

        // Default Direct WhatsApp Link (100% Reliable, 0 API credentials required)
        return [
            'success' => true,
            'provider' => 'wa_direct',
            'whatsapp_url' => $directUrl,
            'message' => 'Direct WhatsApp link generated ready for one-tap delivery.'
        ];
    }

    /**
     * Send message via Termii API (SMS / WhatsApp channel).
     */
    private static function sendViaTermii(string $phone, string $message, string $apiKey): array
    {
        try {
            $ch = curl_init('https://api.ng.termii.com/api/sms/send');
            $payload = json_encode([
                'to' => $phone,
                'from' => setting('termii_sender_id') ?: env('TERMII_SENDER_ID', 'Eventlyy'),
                'sms' => $message,
                'type' => 'plain',
                'channel' => 'generic',
                'api_key' => $apiKey
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code >= 200 && $code < 300) {
                return ['success' => true];
            }
        } catch (\Throwable $e) {
            error_log("Termii dispatch error: " . $e->getMessage());
        }
        return ['success' => false];
    }

    /**
     * Send message via Meta WhatsApp Cloud API.
     */
    private static function sendViaWhatsAppCloud(string $phone, string $message, string $phoneId, string $token): array
    {
        try {
            $url = "https://graph.facebook.com/v19.0/{$phoneId}/messages";
            $ch = curl_init($url);
            $payload = json_encode([
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $phone,
                'type' => 'text',
                'text' => ['preview_url' => true, 'body' => $message]
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                "Authorization: Bearer {$token}"
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code >= 200 && $code < 300) {
                return ['success' => true];
            }
        } catch (\Throwable $e) {
            error_log("WhatsApp Cloud dispatch error: " . $e->getMessage());
        }
        return ['success' => false];
    }

    /**
     * Send message via Twilio Programmable Messaging API.
     */
    private static function sendViaTwilio(string $phone, string $message, string $sid, string $token, string $from): array
    {
        try {
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
            $ch = curl_init($url);
            $fromNumber = str_starts_with($from, 'whatsapp:') ? $from : "whatsapp:{$from}";
            $toNumber = "whatsapp:+{$phone}";
            $postData = http_build_query([
                'From' => $fromNumber,
                'To' => $toNumber,
                'Body' => $message
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_USERPWD, "{$sid}:{$token}");
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code >= 200 && $code < 300) {
                return ['success' => true];
            }
        } catch (\Throwable $e) {
            error_log("Twilio dispatch error: " . $e->getMessage());
        }
        return ['success' => false];
    }

    /**
     * Build the public link to an individual scannable admission pass.
     *
     * @param string $ticketCode
     * @return string
     */
    public static function getPassUrl(string $ticketCode): string
    {
        $appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
        if (function_exists('url')) {
            return url('/pass/' . urlencode($ticketCode));
        }
        return $appUrl . '/pass/' . urlencode($ticketCode);
    }
}
