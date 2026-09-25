<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="format-detection" content="telephone=no" />
    <title>Reset Your Password - <?= e(site_setting('site_title', 'Evently')) ?></title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td, h1, h2, h3, p, a, span { font-family: Arial, sans-serif !important; }
    </style>
    <![endif]-->
    <style type="text/css">
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; height: 100% !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; background-color: #f1f5f9; color: #334155; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        table { border-collapse: collapse !important; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        td, th { border-collapse: collapse !important; }
        img { border: 0 !important; outline: none !important; text-decoration: none !important; }
        a { text-decoration: none; }

        @media only screen and (max-width: 600px) {
            .wrapper-table { width: 100% !important; padding: 12px 6px !important; }
            .container-table { width: 100% !important; max-width: 100% !important; border-radius: 12px !important; }
            .header-padding { padding: 24px 16px !important; }
            .header-title { font-size: 20px !important; }
            .content-padding { padding: 20px 16px !important; }
            .btn-primary { display: block !important; width: 100% !important; padding: 14px 10px !important; box-sizing: border-box !important; text-align: center !important; font-size: 15px !important; }
            .footer-padding { padding: 20px 16px !important; }
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
                            <div style="margin-bottom: 14px;">
                                <?php if ($logoSrc = email_logo_src()): ?>
                                    <img src="<?= $logoSrc ?>" alt="<?= e(site_setting('site_title', 'Evently')) ?>" height="38" style="display:inline-block; max-height:38px; height:38px; width:auto; vertical-align:middle; border:0; margin-right:8px;" />
                                    <span style="display: inline-block; color: #ffffff; font-size: 20px; font-weight: 700; vertical-align: middle;">
                                        <?= e(site_setting('site_title', 'Evently')) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="display: inline-block; width: 36px; height: 36px; line-height: 36px; background-color: #ffffff; color: #0B7366; font-weight: 900; font-size: 22px; border-radius: 8px; text-align: center; vertical-align: middle;">
                                        <?= strtoupper(substr(site_setting('site_title', 'Evently'), 0, 1)) ?>
                                    </span>
                                    <span style="display: inline-block; color: #ffffff; font-size: 20px; font-weight: 700; margin-left: 10px; vertical-align: middle;">
                                        <?= e(site_setting('site_title', 'Evently')) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h1 class="header-title" style="margin: 0; font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.01em;">🔐 Password Reset Request</h1>
                            <p style="margin: 6px 0 0; font-size: 14px; color: #ccfbf1; font-weight: 500;">Secure authentication service</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content-padding" style="padding: 30px 28px;">
                            <p style="margin: 0 0 14px; font-size: 15px; color: #1e293b; line-height: 1.5;">
                                Hello <strong><?= e($name ?? 'User') ?></strong>,
                            </p>
                            <p style="margin: 0 0 20px; font-size: 14px; color: #475569; line-height: 1.5;">
                                We received a request to reset your password. You can set a new password securely by clicking the button below:
                            </p>

                            <?php $rUrl = $reset_url ?? $resetLink ?? '#'; ?>
                            <!-- Call-to-Action Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 28px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= e($rUrl) ?>" class="btn-primary" style="display: inline-block; background-color: #0B7366; color: #ffffff !important; text-decoration: none; padding: 14px 32px; font-weight: 700; font-size: 15px; border-radius: 10px; box-shadow: 0 4px 12px rgba(11, 115, 102, 0.25);">
                                            Reset My Password &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 13.5px; color: #64748b; line-height: 1.5; margin-bottom: 12px;">
                                If you did not request a password reset, no further action is required and your account remains protected.
                            </p>
                            <p style="font-size: 12.5px; color: #94a3b8; line-height: 1.4; margin: 0;">
                                Note: For security reasons, this reset link will expire in 1 hour.
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
                            <p style="margin: 0 0 10px;">
                                <a href="<?= \url('/privacy') ?>" style="color: #0B7366; text-decoration: none; font-weight: 600;">Privacy Policy</a> &bull;
                                <a href="<?= \url('/terms') ?>" style="color: #0B7366; text-decoration: none; font-weight: 600;">Terms of Service</a> &bull;
                                <a href="<?= \url('/support') ?>" style="color: #0B7366; text-decoration: none; font-weight: 600;">Support Desk</a>
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                                This is an automated security communication regarding your <?= e(site_setting('site_title', 'Evently')) ?> account.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
