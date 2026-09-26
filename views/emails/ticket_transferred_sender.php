<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="format-detection" content="telephone=no" />
    <title>Ticket Transfer Confirmed - <?= e(site_setting('site_title', 'Eventlyy')) ?></title>
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
                        <td class="header-padding" align="center" style="background-color: #0369a1; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); padding: 32px 24px; text-align: center;">
                            <div style="display: inline-block; background: rgba(255,255,255,0.18); color: #ffffff; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 20px; margin-bottom: 12px;">
                                ✓ TRANSFER PROCESSED
                            </div>
                            <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.01em;">
                                Ticket Transfer Complete
                            </h1>
                            <p style="margin: 8px 0 0; font-size: 15px; color: #e0f2fe; font-weight: 500;">
                                Your event pass has been re-assigned to <strong><?= e($recipientName) ?></strong>.
                            </p>
                        </td>
                    </tr>

                    <!-- Body / Summary -->
                    <tr>
                        <td class="content-padding" style="padding: 24px;">
                            <p style="margin: 0 0 16px 0; font-size: 15px; color: #334155; line-height: 1.6;">
                                Hi <?= e($senderName) ?>,<br />
                                This email confirms that you have successfully transferred your ticket for <strong><?= e($event['name'] ?? $event['event_name']) ?></strong>.
                            </p>

                            <!-- Transfer Details Card -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; margin-bottom: 24px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 14px; color: #475569;">
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b; width: 140px;">Event:</td>
                                        <td style="padding: 6px 0; font-weight: 700; color: #0f172a;">
                                            <?= e($event['name'] ?? $event['event_name']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Event Date:</td>
                                        <td style="padding: 6px 0; font-weight: 600; color: #1e293b;">
                                            <?= date('l, M d, Y • g:i A', strtotime($event['date'] ?? $event['event_date'])) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Ticket Tier:</td>
                                        <td style="padding: 6px 0; font-weight: 700; color: #0284c7;">
                                            <?= e($ticketType) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Transferred To:</td>
                                        <td style="padding: 6px 0; font-weight: 600; color: #1e293b;">
                                            <?= e($recipientName) ?> (<?= e($recipientEmail) ?>)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Deactivated Code:</td>
                                        <td style="padding: 6px 0; font-family: monospace; color: #ef4444; font-weight: 700;">
                                            <s><?= e($oldTicketCode) ?></s> (Void)
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Security Warning Alert -->
                            <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #ef4444; border-radius: 10px; padding: 14px 16px; margin-bottom: 24px;">
                                <strong style="font-size: 13px; color: #991b1b; display: block; margin-bottom: 4px;">⚠️ Important Notice:</strong>
                                <p style="margin: 0; font-size: 13px; color: #7f1d1d; line-height: 1.5;">
                                    Your previous ticket code (<strong><?= e($oldTicketCode) ?></strong>) and any downloaded/printed QR passes have been permanently deactivated and will be rejected by gate scanners. The recipient has been issued a fresh digital pass with a new QR code.
                                </p>
                            </div>

                            <!-- Back to Profile Link -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <a href="<?= url('/profile') ?>" style="display: inline-block; background-color: #0284c7; color: #ffffff; font-size: 14px; font-weight: 700; padding: 12px 24px; border-radius: 8px; text-decoration: none;">
                                            Return to My Profile
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8;">
                            <p style="margin: 0;">&copy; <?= date('Y') ?> <?= e(site_setting('site_title', 'Eventlyy')) ?>. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
