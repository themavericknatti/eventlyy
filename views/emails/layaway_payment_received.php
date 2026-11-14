<?php
$primaryColor = function_exists('theme_primary_color') ? theme_primary_color() : '#108a55';
$secondaryColor = function_exists('theme_secondary_color') ? theme_secondary_color() : '#1aae6c';
$primaryRgb = function_exists('hex_to_rgb_triple') ? str_replace(' ', ', ', hex_to_rgb_triple($primaryColor)) : '16, 138, 85';
$siteTitle = site_setting('site_title', 'Eventlyy');
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
$layawayUrl = $appUrl . '/layaway';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="format-detection" content="telephone=no" />
    <title>Installment Payment Received - <?= e($siteTitle) ?></title>
    <style type="text/css">
        body { 
            margin: 0 !important; 
            padding: 0 !important; 
            width: 100% !important; 
            background-color: #f1f5f9; 
            color: #334155; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
        }
        table { border-collapse: collapse !important; }
        img { border: 0 !important; outline: none !important; }
        a { text-decoration: none; }
        @media only screen and (max-width: 600px) {
            .wrapper-table { width: 100% !important; padding: 12px 6px !important; }
            .container-table { width: 100% !important; border-radius: 12px !important; }
            .content-padding { padding: 20px 16px !important; }
            .btn-primary { display: block !important; width: 100% !important; padding: 14px 10px !important; box-sizing: border-box !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; -webkit-font-smoothing: antialiased;">
    <table class="wrapper-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; padding: 32px 12px;">
        <tr>
            <td align="center" valign="top">
                <table class="container-table" role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.05);">
                    
                    <!-- Organization Header with Brand Logo -->
                    <tr>
                        <td align="center" style="background-color: <?= $primaryColor ?>; background: linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $secondaryColor ?> 100%); padding: 32px 24px; text-align: center;">
                            <div style="margin-bottom: 14px; display: inline-block;">
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
                                    <tr>
                                        <td align="center" valign="middle">
                                            <?php if ($logoSrc = email_logo_src()): ?>
                                                <img src="<?= $logoSrc ?>" alt="<?= e($siteTitle) ?>" height="38" style="display:inline-block; max-height:38px; height:38px; width:auto; vertical-align:middle; border:0; margin-right:8px;" />
                                                <span style="display: inline-block; color: #ffffff; font-size: 20px; font-weight: 700; vertical-align: middle;">
                                                    <?= e($siteTitle) ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="display: inline-block; width: 36px; height: 36px; line-height: 36px; background-color: #ffffff; color: <?= $primaryColor ?>; font-weight: 900; font-size: 22px; border-radius: 8px; text-align: center; vertical-align: middle;">
                                                    <?= strtoupper(substr($siteTitle, 0, 1)) ?>
                                                </span>
                                                <span style="display: inline-block; color: #ffffff; font-size: 20px; font-weight: 700; margin-left: 10px; vertical-align: middle;">
                                                    <?= e($siteTitle) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.01em;">💰 Installment Payment Received!</h1>
                            <p style="margin: 6px 0 0; font-size: 14px; color: #e2e8f0; font-weight: 500;">Your layaway plan has been updated.</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="content-padding" style="padding: 30px 28px;">
                            <p style="margin: 0 0 14px; font-size: 15px; color: #1e293b; line-height: 1.5;">
                                Hi <strong><?= e($attendee_name) ?></strong>,
                            </p>
                            <p style="margin: 0 0 20px; font-size: 14px; color: #475569; line-height: 1.5;">
                                We've successfully received and recorded your payment of <strong style="color: <?= $primaryColor ?>; font-size: 16px;"><?= format_price($amount_paid) ?></strong> toward your pass for <strong><?= e($event_name) ?></strong>.
                            </p>

                            <!-- Progress Box -->
                            <div style="background-color: #f1f5f9; border-radius: 12px; padding: 20px; margin-bottom: 24px; text-align: center;">
                                <div style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; margin-bottom: 8px;">
                                    Plan Progress: <?= (int)$percentage ?>% Completed
                                </div>
                                <div style="background-color: #e2e8f0; border-radius: 999px; height: 12px; overflow: hidden; margin-bottom: 12px;">
                                    <div style="background-color: <?= $primaryColor ?>; height: 100%; width: <?= (int)$percentage ?>%; border-radius: 999px;"></div>
                                </div>
                                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 14px;">
                                    <tr>
                                        <td align="left" style="color: #475569;">Total Paid: <strong style="color: <?= $primaryColor ?>;"><?= format_price($total_paid) ?></strong></td>
                                        <td align="right" style="color: #475569;">Remaining: <strong style="color: #ef4444;"><?= format_price($remaining_balance) ?></strong></td>
                                    </tr>
                                </table>
                            </div>

                            <p style="font-size: 14px; color: #64748b; line-height: 1.5; margin-bottom: 24px;">
                                Keep going! Once you complete the remaining <strong><?= format_price($remaining_balance) ?></strong> before the event starts, your official admission pass with scannable entry QR code will be activated instantly.
                            </p>

                            <!-- Action Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= e($layawayUrl) ?>" class="btn-primary" style="display: inline-block; background-color: <?= $primaryColor ?>; color: #ffffff !important; text-decoration: none; padding: 14px 32px; font-weight: 700; font-size: 15px; border-radius: 10px; box-shadow: 0 4px 12px rgba(<?= $primaryRgb ?>, 0.25);">
                                            View Plan / Pay Next Bit &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 30px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #64748b; line-height: 1.6;">
                            <div style="margin-bottom: 10px;">
                                <strong style="color: #334155; font-size: 13px;"><?= e($siteTitle) ?></strong>
                                <span style="color: #94a3b8;"> &bull; Ticket Reference: <?= e($ticket_code) ?></span>
                            </div>
                            <p style="margin: 0 0 8px;">&copy; <?= date('Y') ?> <?= e($siteTitle) ?>. All rights reserved.</p>
                            <p style="margin: 0 0 10px;">
                                <a href="<?= \url('/privacy') ?>" style="color: <?= $primaryColor ?>; text-decoration: none; font-weight: 600;">Privacy Policy</a> &bull;
                                <a href="<?= \url('/terms') ?>" style="color: <?= $primaryColor ?>; text-decoration: none; font-weight: 600;">Terms of Service</a> &bull;
                                <a href="<?= \url('/support') ?>" style="color: <?= $primaryColor ?>; text-decoration: none; font-weight: 600;">Support Desk</a>
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                                You are receiving this email because a payment was registered for your layaway reservation on <?= e($siteTitle) ?>.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
