<?php
$primaryColor = function_exists('theme_primary_color') ? theme_primary_color() : '#4f46e5';
$secondaryColor = function_exists('theme_secondary_color') ? theme_secondary_color() : '#6366f1';
$siteTitle = site_setting('site_title', 'Eventlyy');
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
$layawayUrl = $appUrl . '/layaway';
$percentPaid = $total_price > 0 ? round(($deposit_paid / $total_price) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layaway Reservation Confirmed - <?= e($siteTitle) ?></title>
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
        .notice { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 6px; font-size: 13px; color: #92400e; margin: 20px 0; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #64748b; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1 style="margin: 0; font-size: 24px; font-weight: 800;">🎟️ Ticket Reserved on Layaway!</h1>
                <p style="margin: 8px 0 0; opacity: 0.9; font-size: 15px;">Your spot is reserved. Pay bit-by-bit before the event.</p>
            </div>
            <div class="content">
                <p>Hello <strong><?= e($attendee_name) ?></strong>,</p>
                <p>Good news! Your layaway reservation for <strong><?= e($event_name) ?></strong> has been successfully registered with your initial deposit.</p>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin: 16px 0;">
                    <p style="margin: 0 0 6px;">📅 <strong>Date:</strong> <?= e($event_date) ?></p>
                    <p style="margin: 0 0 6px;">📍 <strong>Venue:</strong> <?= e($event_location) ?></p>
                    <p style="margin: 0;">🎫 <strong>Reserved:</strong> <?= e($ticket_type) ?></p>
                </div>

                <div class="progress-box">
                    <div style="font-size: 14px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Payment Progress (<?= $percentPaid ?>%)</div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?= $percentPaid ?>%;"></div>
                    </div>
                    <table width="100%" style="font-size: 14px; margin-top: 8px;">
                        <tr>
                            <td align="left">Deposit Paid: <strong style="color: <?= $primaryColor ?>;"><?= format_price($deposit_paid) ?></strong></td>
                            <td align="right">Remaining: <strong><?= format_price($remaining_balance) ?></strong></td>
                        </tr>
                    </table>
                </div>

                <div class="notice">
                    <strong>Important Policy:</strong> Complete your balance anytime before the event date to unlock your official scannable entry QR pass. If your plan is not completed before the event starts, payments are <strong>non-refundable</strong>, but your money is automatically preserved as an <strong>Event Credit</strong> to use on future events!
                </div>

                <div style="text-align: center; margin: 24px 0;">
                    <a href="<?= e($layawayUrl) ?>" class="btn">View Plan &amp; Make Payment →</a>
                </div>
            </div>
            <div class="footer">
                <p style="margin: 0;">Sent by <?= e($siteTitle) ?> • Questions? Contact event support anytime.</p>
            </div>
        </div>
    </div>
</body>
</html>
