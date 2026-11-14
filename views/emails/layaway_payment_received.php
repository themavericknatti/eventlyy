<?php
$primaryColor = function_exists('theme_primary_color') ? theme_primary_color() : '#4f46e5';
$secondaryColor = function_exists('theme_secondary_color') ? theme_secondary_color() : '#6366f1';
$siteTitle = site_setting('site_title', 'Eventlyy');
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
$layawayUrl = $appUrl . '/layaway';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installment Payment Received - <?= e($siteTitle) ?></title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; }
        .wrapper { width: 100%; padding: 32px 12px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
        .header { background: linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $secondaryColor ?> 100%); padding: 32px 24px; text-align: center; color: #ffffff; }
        .content { padding: 28px 24px; }
        .progress-box { background: #f1f5f9; border-radius: 12px; padding: 20px; margin: 20px 0; text-align: center; }
        .progress-bar-bg { background: #e2e8f0; border-radius: 999px; height: 12px; overflow: hidden; margin: 12px 0; }
        .progress-bar-fill { background: <?= $primaryColor ?>; height: 100%; border-radius: 999px; }
        .btn { display: inline-block; background: <?= $primaryColor ?>; color: #ffffff !important; padding: 14px 28px; font-weight: 700; border-radius: 10px; text-decoration: none; margin-top: 16px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #64748b; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1 style="margin: 0; font-size: 24px; font-weight: 800;">💰 Installment Received!</h1>
                <p style="margin: 8px 0 0; opacity: 0.9; font-size: 15px;">Your layaway plan has been updated.</p>
            </div>
            <div class="content">
                <p>Hello <strong><?= e($attendee_name) ?></strong>,</p>
                <p>We received your payment of <strong style="color: <?= $primaryColor ?>; font-size: 18px;"><?= format_price($amount_paid) ?></strong> toward your pass for <strong><?= e($event_name) ?></strong>.</p>

                <div class="progress-box">
                    <div style="font-size: 14px; color: #64748b; text-transform: uppercase; font-weight: 700;">Progress: <?= (int)$percentage ?>% Completed</div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?= (int)$percentage ?>%;"></div>
                    </div>
                    <table width="100%" style="font-size: 14px; margin-top: 8px;">
                        <tr>
                            <td align="left">Total Paid: <strong><?= format_price($total_paid) ?></strong></td>
                            <td align="right">Remaining: <strong style="color: #ef4444;"><?= format_price($remaining_balance) ?></strong></td>
                        </tr>
                    </table>
                </div>

                <p style="font-size: 14px; color: #64748b;">
                    Keep going! Once you complete the remaining <?= format_price($remaining_balance) ?>, your official entry pass with digital QR code will be generated instantly.
                </p>

                <div style="text-align: center; margin: 24px 0;">
                    <a href="<?= e($layawayUrl) ?>" class="btn">View Plan / Pay Next Bit →</a>
                </div>
            </div>
            <div class="footer">
                <p style="margin: 0;">Sent by <?= e($siteTitle) ?> • Ticket Reference: <?= e($ticket_code) ?></p>
            </div>
        </div>
    </div>
</body>
</html>
