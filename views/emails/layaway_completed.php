<?php
$primaryColor = function_exists('theme_primary_color') ? theme_primary_color() : '#10b981';
$secondaryColor = function_exists('theme_secondary_color') ? theme_secondary_color() : '#059669';
$siteTitle = site_setting('site_title', 'Eventlyy');
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
$ticketUrl = $appUrl . '/profile?tab=tickets';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pass Fully Paid &amp; Active! - <?= e($siteTitle) ?></title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; }
        .wrapper { width: 100%; padding: 32px 12px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
        .header { background: linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $secondaryColor ?> 100%); padding: 32px 24px; text-align: center; color: #ffffff; }
        .content { padding: 28px 24px; }
        .ticket-box { background: #f0fdf4; border: 2px dashed #86efac; border-radius: 12px; padding: 20px; margin: 20px 0; text-align: center; }
        .btn { display: inline-block; background: <?= $primaryColor ?>; color: #ffffff !important; padding: 14px 28px; font-weight: 700; border-radius: 10px; text-decoration: none; margin-top: 16px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #64748b; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1 style="margin: 0; font-size: 26px; font-weight: 800;">🎉 100% Paid — Pass Activated!</h1>
                <p style="margin: 8px 0 0; opacity: 0.9; font-size: 15px;">Your layaway plan is fully completed.</p>
            </div>
            <div class="content">
                <p>Hello <strong><?= e($attendee_name) ?></strong>,</p>
                <p>Congratulations! Your final payment has been verified. Your official admission pass for <strong><?= e($event_name) ?></strong> is now <strong>fully active</strong> and ready for entry at the gate.</p>

                <div class="ticket-box">
                    <div style="font-size: 12px; font-weight: 800; color: #15803d; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px;">OFFICIAL ADMISSION PASS</div>
                    <div style="font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: 0.05em; font-family: monospace;"><?= e($ticket_code) ?></div>
                    <div style="margin-top: 8px; font-size: 14px; color: #334155;">
                        <strong><?= e($ticket_type) ?></strong> • <?= format_price($total_amount) ?>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin: 16px 0;">
                    <p style="margin: 0 0 6px;">📅 <strong>Date:</strong> <?= e($event_date) ?></p>
                    <p style="margin: 0;">📍 <strong>Venue:</strong> <?= e($event_location) ?></p>
                </div>

                <p style="font-size: 14px; color: #64748b;">
                    Show your digital ticket pass on your mobile phone or print it out upon arrival at the venue for instant gate scanning.
                </p>

                <div style="text-align: center; margin: 24px 0;">
                    <a href="<?= e($ticketUrl) ?>" class="btn">View &amp; Download My Pass →</a>
                </div>
            </div>
            <div class="footer">
                <p style="margin: 0;">Sent by <?= e($siteTitle) ?> • See you at the event!</p>
            </div>
        </div>
    </div>
</body>
</html>
