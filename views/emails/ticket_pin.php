<?php
$primaryColor = function_exists('theme_primary_color') ? theme_primary_color() : '#108a55';
$secondaryColor = function_exists('theme_secondary_color') ? theme_secondary_color() : '#1aae6c';
$siteName = site_setting('site_title', 'Eventlyy');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Your Pass Security PIN - <?= e($siteName) ?></title>
    <style type="text/css">
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f1f5f9; color: #334155; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        table { border-collapse: collapse !important; }
        @media only screen and (max-width: 600px) {
            .container-table { width: 100% !important; border-radius: 12px !important; }
            .content-padding { padding: 20px 16px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; padding: 32px 12px;">
        <tr>
            <td align="center" valign="top">
                <table class="container-table" role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color: <?= $primaryColor ?>; background: linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $secondaryColor ?> 100%); padding: 32px 24px; text-align: center;">
                            <div style="margin-bottom: 12px;">
                                <?php if ($logoSrc = email_logo_src()): ?>
                                    <img src="<?= $logoSrc ?>" alt="<?= e($siteName) ?>" height="38" style="display:inline-block; max-height:38px; height:38px; width:auto; vertical-align:middle; border:0; margin-right:8px;" />
                                    <span style="display: inline-block; color: #ffffff; font-size: 20px; font-weight: 700; vertical-align: middle;">
                                        <?= e($siteName) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="display: inline-block; width: 36px; height: 36px; line-height: 36px; background-color: #ffffff; color: <?= $primaryColor ?>; font-weight: 900; font-size: 22px; border-radius: 8px; text-align: center; vertical-align: middle;">
                                        <?= strtoupper(substr($siteName, 0, 1)) ?>
                                    </span>
                                    <span style="display: inline-block; color: #ffffff; font-size: 20px; font-weight: 700; margin-left: 10px; vertical-align: middle;">
                                        <?= e($siteName) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #ffffff;">🔒 Admission Pass Security PIN</h1>
                            <p style="margin: 6px 0 0; font-size: 14px; color: #ccfbf1; font-weight: 500;">Pass #<?= e($ticketCode) ?></p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="content-padding" style="padding: 32px 28px;">
                            <p style="margin: 0 0 16px; font-size: 15px; color: #1e293b; line-height: 1.5;">
                                Hello <strong><?= e($attendeeName ?? 'Attendee') ?></strong>,
                            </p>
                            <p style="margin: 0 0 20px; font-size: 14px; color: #475569; line-height: 1.6;">
                                Here is the security PIN to unlock your scannable gate QR code for <strong><?= e($eventName) ?></strong>:
                            </p>

                            <!-- PIN Box -->
                            <div style="background-color: #f8fafc; border: 2px dashed <?= $primaryColor ?>; border-radius: 12px; padding: 22px 16px; text-align: center; margin: 24px 0;">
                                <span style="display: block; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Your 4-Digit Pass PIN</span>
                                <span style="display: inline-block; font-size: 40px; font-weight: 900; letter-spacing: 0.3em; color: <?= $primaryColor ?>; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;"><?= e($securityPin) ?></span>
                                <span style="display: block; font-size: 12px; color: #64748b; margin-top: 8px;">Enter this PIN on your digital pass to reveal your gate QR code.</span>
                            </div>

                            <!-- Direct Unlock Button -->
                            <div style="text-align: center; margin: 28px 0;">
                                <a href="<?= e($passUrl) ?>" style="display: inline-block; background-color: <?= $primaryColor ?>; color: #ffffff; padding: 14px 32px; font-size: 14px; font-weight: 700; text-decoration: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(16, 138, 85, 0.25);">
                                    🎟️ Open &amp; Unlock Admission Pass →
                                </a>
                            </div>

                            <p style="margin: 24px 0 0; font-size: 13px; color: #94a3b8; line-height: 1.5;">
                                Best regards,<br />
                                <strong>The <?= e($siteName) ?> Team</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px 24px; text-align: center; border-top: 1px solid #f1f5f9; font-size: 12px; color: #94a3b8;">
                            &copy; <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
