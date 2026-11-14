<?php
$primaryColor = function_exists('theme_primary_color') ? theme_primary_color() : '#f59e0b';
$siteTitle = site_setting('site_title', 'Eventlyy');
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
$layawayUrl = $appUrl . '/layaway';
$exploreUrl = $appUrl . '/#explore';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Pass Expired - Credit Available - <?= e($siteTitle) ?></title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; }
        .wrapper { width: 100%; padding: 32px 12px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
        .header { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); padding: 32px 24px; text-align: center; color: #ffffff; }
        .content { padding: 28px 24px; }
        .credit-box { background: #fffbeb; border: 2px solid #fde68a; border-radius: 12px; padding: 20px; margin: 20px 0; text-align: center; }
        .btn { display: inline-block; background: #d97706; color: #ffffff !important; padding: 14px 28px; font-weight: 700; border-radius: 10px; text-decoration: none; margin-top: 16px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #64748b; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1 style="margin: 0; font-size: 24px; font-weight: 800;">🎫 Event Concluded — Credit Available</h1>
                <p style="margin: 8px 0 0; opacity: 0.9; font-size: 15px;">Your paid layaway funds have been converted to Event Credit.</p>
            </div>
            <div class="content">
                <p>Hello <strong><?= e($attendee_name) ?></strong>,</p>
                <p>The event <strong><?= e($event_name) ?></strong> (held on <?= e($original_event_date) ?>) has commenced. Since your layaway plan was not completed before the event start time, your reservation has concluded per our ticketing terms.</p>

                <p><strong>Your funds are protected!</strong> While tickets are non-refundable, 100% of the money you paid has been preserved and converted into an <strong>Event Credit</strong>:</p>

                <div class="credit-box">
                    <div style="font-size: 12px; font-weight: 800; color: #92400e; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">YOUR EVENT CREDIT BALANCE</div>
                    <div style="font-size: 32px; font-weight: 900; color: #78350f;"><?= format_price($credit_amount) ?></div>
                    <div style="margin-top: 8px; font-size: 13px; color: #92400e;">
                        Valid until: <strong><?= e($credit_expires_at) ?></strong>
                    </div>
                </div>

                <p style="font-size: 14px; color: #64748b;">
                    You can use this credit toward tickets or layaway reservations for any other event on <?= e($siteTitle) ?>.
                </p>

                <div style="text-align: center; margin: 24px 0;">
                    <a href="<?= e($layawayUrl) ?>" class="btn">View Credits &amp; Browse Events →</a>
                </div>
            </div>
            <div class="footer">
                <p style="margin: 0;">Sent by <?= e($siteTitle) ?> • Need help? Contact our support desk anytime.</p>
            </div>
        </div>
    </div>
</body>
</html>
