<?php
$isIssuedOnly = !empty($is_issued) || (!empty($payment_method) && (
    str_contains(strtolower($payment_method), 'gate') || 
    str_contains(strtolower($payment_method), 'host') || 
    str_contains(strtolower($payment_method), 'comp') ||
    str_contains(strtolower($payment_method), 'cash') ||
    str_contains(strtolower($payment_method), 'pos') ||
    str_contains(strtolower($payment_method), 'transfer')
));

$headline = !empty($header_title) ? $header_title : ($isIssuedOnly ? 'Official Admission Pass Issued!' : 'Order Confirmed & Passes Issued!');
$subHeadline = !empty($header_subtitle) ? $header_subtitle : ($isIssuedOnly ? 'Your ticket passes have been issued and are ready for entry' : 'Official admission ticket details for your upcoming event');
$introText = !empty($greeting_text) ? $greeting_text : ($isIssuedOnly 
    ? 'Your official admission pass for <strong>' . e($event_name ?? 'your event') . '</strong> has been issued. Your scannable pass details are below.' 
    : 'Thank you for booking with <strong>' . e(site_setting('site_title', 'Evently')) . '</strong>. Your admission passes are confirmed and ready below.'
);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="x-apple-disable-message-reformatting" />
    <title><?= e($headline) ?> - <?= e(site_setting('site_title', 'Evently')) ?></title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td, h1, h2, h3, p, a, span { font-family: Arial, sans-serif !important; }
    </style>
    <![endif]-->
    <style type="text/css">
        /* Client-specific resets */
        body { 
            margin: 0 !important; 
            padding: 0 !important; 
            width: 100% !important; 
            height: 100% !important; 
            -webkit-text-size-adjust: 100%; 
            -ms-text-size-adjust: 100%; 
            background-color: #f1f5f9; 
            color: #334155; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; 
        }
        table { border-collapse: collapse !important; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        td, th { border-collapse: collapse !important; }
        img { border: 0 !important; outline: none !important; text-decoration: none !important; -ms-interpolation-mode: bicubic; }
        a { text-decoration: none; }

        /* Responsive Breakpoints (Tablet & Mobile) */
        @media only screen and (max-width: 600px) {
            .wrapper-table { width: 100% !important; padding: 12px 6px !important; }
            .container-table { width: 100% !important; max-width: 100% !important; border-radius: 12px !important; }
            .header-padding { padding: 24px 16px !important; }
            .header-brand-title { font-size: 18px !important; }
            .header-title { font-size: 20px !important; }
            .header-sub { font-size: 13px !important; }
            .content-padding { padding: 20px 16px !important; }
            .event-card { padding: 14px 12px !important; }
            .event-title { font-size: 16px !important; }
            .ticket-card { margin-bottom: 16px !important; }
            .qr-code-img { width: 140px !important; height: 140px !important; }
            .ticket-code-badge { font-size: 15px !important; padding: 6px 12px !important; }
            .btn-primary { display: block !important; width: 100% !important; padding: 14px 10px !important; box-sizing: border-box !important; text-align: center !important; font-size: 15px !important; }
            .meta-text { font-size: 13px !important; }
            .footer-padding { padding: 20px 16px !important; }
            .footer-links { font-size: 12px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; -webkit-font-smoothing: antialiased;">
    <table class="wrapper-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; padding: 32px 12px;">
        <tr>
            <td align="center" valign="top">
                <!-- Main Container -->
                <table class="container-table" role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.05);">
                    
                    <!-- Organization Header -->
                    <tr>
                        <td class="header-padding" align="center" style="background-color: #0B7366; background: linear-gradient(135deg, #0B7366 0%, #064e45 100%); padding: 32px 24px; text-align: center;">
                            <!-- Brand & Logo -->
                            <div style="margin-bottom: 14px; display: inline-block;">
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
                                    <tr>
                                        <td align="center" valign="middle">
                                            <?php if ($logoSrc = email_logo_src()): ?>
                                                <img src="<?= $logoSrc ?>" alt="<?= e(site_setting('site_title', 'Evently')) ?>" height="38" style="display:inline-block; max-height:38px; height:38px; width:auto; vertical-align:middle; border:0; margin-right:8px;" />
                                                <span class="header-brand-title" style="display: inline-block; color: #ffffff; font-size: 20px; font-weight: 700; vertical-align: middle;">
                                                    <?= e(site_setting('site_title', 'Evently')) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="brand-letter" style="display: inline-block; width: 36px; height: 36px; line-height: 36px; background-color: #ffffff; color: #0B7366; font-weight: 900; font-size: 22px; border-radius: 8px; text-align: center; vertical-align: middle;">
                                                    <?= strtoupper(substr(site_setting('site_title', 'Evently'), 0, 1)) ?>
                                                </span>
                                                <span class="header-brand-title" style="display: inline-block; color: #ffffff; font-size: 20px; font-weight: 700; margin-left: 10px; vertical-align: middle;">
                                                    <?= e(site_setting('site_title', 'Evently')) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <h1 class="header-title" style="margin: 0; font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.01em;"><?= e($headline) ?></h1>
                            <p class="header-sub" style="margin: 6px 0 0; font-size: 14px; color: #ccfbf1; font-weight: 500;"><?= e($subHeadline) ?></p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="content-padding" style="padding: 30px 28px;">
                            <p style="margin: 0 0 14px; font-size: 15px; color: #1e293b; line-height: 1.5;">
                                Hi <strong><?= e($attendee_name ?? 'Valued Guest') ?></strong>,
                            </p>
                            <p style="margin: 0 0 20px; font-size: 14px; color: #475569; line-height: 1.5;">
                                <?= $introText ?>
                            </p>

                            <!-- Event Details Summary Card -->
                            <table class="event-card" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 16px 18px;">
                                        <div class="event-title" style="font-size: 17px; font-weight: 700; color: #0f172a; margin-bottom: 10px; line-height: 1.3;">
                                            <?= e($event_name ?? 'Event') ?>
                                        </div>
                                        <div class="meta-text" style="font-size: 13.5px; color: #475569; margin-bottom: 6px; line-height: 1.4;">
                                            📅 <strong>Date &amp; Time:</strong> <?= e($event_date ?? 'TBA') ?>
                                        </div>
                                        <div class="meta-text" style="font-size: 13.5px; color: #475569; margin-bottom: 6px; line-height: 1.4;">
                                            📍 <strong>Venue Location:</strong> <?= e($event_location ?? 'TBA') ?>
                                        </div>
                                        <div class="meta-text" style="font-size: 13.5px; color: #475569; line-height: 1.4;">
                                            💳 <strong>Payment Method:</strong> <?= e($payment_method ?? 'Card') ?> &bull; <strong>Status:</strong> <span style="color:#059669; font-weight:700;">✓ Confirmed</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Section Title -->
                            <div style="font-size: 14px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 14px;">
                                Official Admission Passes (<?= count($tickets ?? []) ?>)
                            </div>

                            <!-- Individual Ticket Cards Loop -->
                            <?php foreach (($tickets ?? []) as $index => $t): 
                                $tCode = $t['ticket_code'] ?? ($t['id'] ?? 'EV-' . ($index + 1));
                                $qrSrc = ticket_qr_url($tCode, 260);
                                $tPrice = (float)($t['price'] ?? 0);
                            ?>
                            <table class="ticket-card" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border: 2px solid #0B7366; border-radius: 14px; overflow: hidden; margin-bottom: 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                                <!-- Card Header Strip -->
                                <tr>
                                    <td style="background-color: #0B7366; padding: 10px 16px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="left" style="font-size: 13px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 0.04em;">
                                                    🎟️ <?= e($t['ticket_type'] ?? 'General Admission') ?>
                                                </td>
                                                <td align="right" style="font-size: 13px; font-weight: 800; color: #ffffff;">
                                                    <?= $tPrice > 0 ? (get_currency_symbol() . number_format($tPrice, 2)) : 'FREE / COMP' ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <!-- Card Body: Scannable QR & Admission Code -->
                                <tr>
                                    <td align="center" style="padding: 18px 16px; text-align: center;">
                                        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px; display: inline-block; box-shadow: 0 2px 6px rgba(0,0,0,0.04); margin-bottom: 10px;">
                                            <img class="qr-code-img" src="<?= $qrSrc ?>" alt="QR Code for <?= e($tCode) ?>" width="135" height="135" style="display: block; margin: 0 auto; width: 135px; height: 135px; border-radius: 4px;" />
                                        </div>
                                        <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.06em; margin-bottom: 4px;">
                                            Unique Admission Code
                                        </div>
                                        <div>
                                            <span class="ticket-code-badge" style="display: inline-block; font-family: Consolas, Monaco, 'Courier New', monospace; background-color: #f1f5f9; color: #0B7366; border: 1px dashed #0B7366; font-size: 16px; font-weight: 800; padding: 5px 14px; border-radius: 8px; letter-spacing: 0.08em; margin-bottom: 6px;">
                                                <?= e($tCode) ?>
                                            </span>
                                        </div>
                                        <p style="margin: 4px 0 0; font-size: 11.5px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">
                                            ⚡ Present this QR code or code at gate entry
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            <?php endforeach; ?>

                            <!-- Call-to-Action Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 24px; margin-bottom: 12px;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= \url('/profile') ?>" class="btn-primary" style="display: inline-block; background-color: #0B7366; color: #ffffff !important; text-decoration: none; padding: 14px 32px; font-weight: 700; font-size: 15px; border-radius: 10px; box-shadow: 0 4px 12px rgba(11, 115, 102, 0.25);">
                                            View &amp; Manage Passes &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 12.5px; color: #64748b; text-align: center; margin: 12px 0 0; line-height: 1.4;">
                                Tip: Take a screenshot of this email or save the QR code to your photos for quick access at the venue entrance.
                            </p>
                        </td>
                    </tr>

                    <!-- Organization Footer -->
                    <tr>
                        <td class="footer-padding" style="padding: 24px 30px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #64748b; line-height: 1.6;">
                            <div style="margin-bottom: 10px;">
                                <strong style="color: #334155; font-size: 13px;"><?= e(site_setting('site_title', 'Evently')) ?></strong>
                                <span style="color: #94a3b8;"> &bull; Ticketing &amp; Event Experiences</span>
                            </div>
                            <p style="margin: 0 0 8px;">&copy; <?= date('Y') ?> <?= e(site_setting('site_title', 'Evently')) ?>. All rights reserved.</p>
                            <p class="footer-links" style="margin: 0 0 10px;">
                                <a href="<?= \url('/privacy') ?>" style="color: #0B7366; text-decoration: none; font-weight: 600;">Privacy Policy</a> &bull;
                                <a href="<?= \url('/terms') ?>" style="color: #0B7366; text-decoration: none; font-weight: 600;">Terms of Service</a> &bull;
                                <a href="<?= \url('/support') ?>" style="color: #0B7366; text-decoration: none; font-weight: 600;">Support Desk</a> &bull;
                                <a href="<?= \url('/about') ?>" style="color: #0B7366; text-decoration: none; font-weight: 600;">About Us</a>
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                                You are receiving this email because an admission pass or ticket was issued on your behalf via <?= e(site_setting('site_title', 'Evently')) ?>.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
