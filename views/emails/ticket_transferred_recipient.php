<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="format-detection" content="telephone=no" />
    <title>Ticket Received - <?= e(site_setting('site_title', 'Eventlyy')) ?></title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td, h1, h2, h3, p, a, span { font-family: Arial, sans-serif !important; }
    </style>
    <![endif]-->
    <style type="text/css">
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
        img { border: 0 !important; outline: none !important; text-decoration: none !important; }
        a { text-decoration: none; }

        @media only screen and (max-width: 600px) {
            .wrapper-table { width: 100% !important; padding: 12px 6px !important; }
            .container-table { width: 100% !important; max-width: 100% !important; border-radius: 12px !important; }
            .header-padding { padding: 24px 16px !important; }
            .content-padding { padding: 20px 16px !important; }
            .qr-code-img { width: 150px !important; height: 150px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; -webkit-font-smoothing: antialiased;">
    <table class="wrapper-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; padding: 32px 12px;">
        <tr>
            <td align="center" valign="top">
                <table class="container-table" role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td class="header-padding" align="center" style="background-color: #0B7366; background: linear-gradient(135deg, #0B7366 0%, #064e45 100%); padding: 32px 24px; text-align: center;">
                            <div style="display: inline-block; background: rgba(255,255,255,0.18); color: #ffffff; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 20px; margin-bottom: 12px;">
                                🎟️ TICKET REASSIGNED TO YOU
                            </div>
                            <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.01em;">
                                You've Got a Ticket!
                            </h1>
                            <p style="margin: 8px 0 0; font-size: 15px; color: #ccfbf1; font-weight: 500;">
                                <strong><?= e($senderName) ?></strong> has transferred an event pass to you.
                            </p>
                        </td>
                    </tr>

                    <!-- Personal Note Block if provided -->
                    <?php if (!empty($transferNote)): ?>
                    <tr>
                        <td style="padding: 20px 24px 0 24px;">
                            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-left: 4px solid #10b981; border-radius: 10px; padding: 14px 16px;">
                                <span style="font-size: 12px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Note from <?= e($senderName) ?>:</span>
                                <p style="margin: 0; font-size: 14px; color: #14532d; font-style: italic; line-height: 1.5;">
                                    "<?= nl2br(e($transferNote)) ?>"
                                </p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <!-- Body / Ticket Card -->
                    <tr>
                        <td class="content-padding" style="padding: 24px;">
                            <!-- Event Details Card -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; margin-bottom: 24px;">
                                <span style="display: inline-block; background-color: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 3px 8px; border-radius: 6px; margin-bottom: 8px;">
                                    Event Details
                                </span>
                                <h2 style="margin: 0 0 10px 0; font-size: 19px; font-weight: 800; color: #0f172a; line-height: 1.3;">
                                    <?= e($event['name'] ?? $event['event_name']) ?>
                                </h2>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 14px; color: #475569;">
                                    <tr>
                                        <td style="padding: 4px 0; width: 24px; vertical-align: top;">📅</td>
                                        <td style="padding: 4px 0; font-weight: 600; color: #1e293b;">
                                            <?= date('l, F j, Y \a\t g:i A', strtotime($event['date'] ?? $event['event_date'])) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; vertical-align: top;">📍</td>
                                        <td style="padding: 4px 0; color: #475569;">
                                            <?= e($event['location'] ?? $event['event_location']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; vertical-align: top;">🎫</td>
                                        <td style="padding: 4px 0; color: #0B7366; font-weight: 700;">
                                            <?= e($ticketType) ?> Pass
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; vertical-align: top;">👤</td>
                                        <td style="padding: 4px 0; color: #1e293b;">
                                            Attendee: <strong><?= e($recipientName) ?></strong>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- QR Code & Digital Admission Stub -->
                            <div style="background-color: #ffffff; border: 2px dashed #cbd5e1; border-radius: 16px; padding: 24px; text-align: center; margin-bottom: 24px;">
                                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 12px;">
                                    Digital Admission Pass
                                </span>
                                <div style="display: inline-block; background-color: #ffffff; padding: 10px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.06); margin-bottom: 12px;">
                                    <img src="<?= $qrUrl ?>" alt="Ticket QR Code" width="180" height="180" class="qr-code-img" style="display: block; width: 180px; height: 180px; border-radius: 6px; image-rendering: pixelated;" />
                                </div>
                                <div style="font-family: monospace; font-size: 18px; font-weight: 800; color: #0B7366; letter-spacing: 1.5px; margin-bottom: 6px;">
                                    <?= e($ticketCode) ?>
                                </div>
                                <p style="margin: 0; font-size: 13px; color: #64748b; font-weight: 500;">
                                    Present this QR code on your mobile device at the entrance gate for instant check-in.
                                </p>
                            </div>

                            <!-- Quick Action Buttons -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 10px;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= url('/profile') ?>" style="display: inline-block; background-color: #0B7366; color: #ffffff; font-size: 15px; font-weight: 700; padding: 14px 28px; border-radius: 10px; text-decoration: none; box-shadow: 0 4px 12px rgba(11, 115, 102, 0.25);">
                                            View in My Tickets Portal
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 20px 0 0; font-size: 13px; color: #94a3b8; text-align: center; line-height: 1.5;">
                                If you don't have an Eventlyy account yet, simply <a href="<?= url('/register') ?>" style="color: #0B7366; font-weight: 600; text-decoration: underline;">create an account</a> with this email address (<strong><?= e($recipientEmail ?? '') ?></strong>) and your ticket will automatically appear in your portal.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px; text-align: center; font-size: 12px; color: #94a3b8;">
                            <p style="margin: 0 0 6px 0;">This email was sent to <?= e($recipientEmail ?? '') ?> because a ticket was transferred to you on <?= e(site_setting('site_title', 'Eventlyy')) ?>.</p>
                            <p style="margin: 0;">&copy; <?= date('Y') ?> <?= e(site_setting('site_title', 'Eventlyy')) ?>. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
