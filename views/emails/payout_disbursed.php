<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="format-detection" content="telephone=no" />
    <title>Payout Disbursed - <?= e(site_setting('site_title', 'Evently')) ?></title>
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
            .payout-card { padding: 14px 12px !important; }
            .footer-padding { padding: 20px 16px !important; }
            .btn-action { display: block !important; width: 100% !important; text-align: center !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; -webkit-font-smoothing: antialiased;">
    <table class="wrapper-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; padding: 32px 12px;">
        <tr>
            <td align="center" valign="top">
                <!-- Main Container -->
                <table class="container-table" role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td class="header-padding" align="center" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 32px 24px; text-align: center;">
                            <div style="margin-bottom: 14px;">
                                <?php if ($logoSrc = email_logo_src()): ?>
                                    <img src="<?= $logoSrc ?>" alt="<?= e(site_setting('site_title', 'Evently')) ?>" height="38" style="display:inline-block; max-height:38px; height:38px; width:auto; vertical-align:middle; border:0; margin-right:8px;" />
                                    <span style="display: inline-block; color: #ffffff; font-size: 20px; font-weight: 700; vertical-align: middle;">
                                        <?= e(site_setting('site_title', 'Evently')) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="display: inline-block; width: 36px; height: 36px; line-height: 36px; background-color: #ffffff; color: #059669; font-weight: 900; font-size: 22px; border-radius: 8px; text-align: center; vertical-align: middle;">
                                        <?= strtoupper(substr(site_setting('site_title', 'Evently'), 0, 1)) ?>
                                    </span>
                                    <span style="display: inline-block; color: #ffffff; font-size: 20px; font-weight: 700; margin-left: 10px; vertical-align: middle;">
                                        <?= e(site_setting('site_title', 'Evently')) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h1 class="header-title" style="margin: 0; font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.01em;">Payout Disbursed Successfully</h1>
                            <p style="margin: 6px 0 0; font-size: 14px; color: #d1fae5; font-weight: 500;">Your organizer earnings have been processed and sent</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content-padding" style="padding: 30px 28px;">
                            <p style="margin: 0 0 14px; font-size: 15px; color: #1e293b; line-height: 1.5;">
                                Hi <strong><?= e($host_name ?? 'Organizer') ?></strong>,
                            </p>
                            <p style="margin: 0 0 20px; font-size: 14px; color: #475569; line-height: 1.5;">
                                Great news! A payout disbursement of <strong><?= format_price($amount ?? 0) ?></strong> for <strong><?= e($event_name ?? 'your event earnings') ?></strong> has been processed and sent to your designated bank account.
                            </p>

                            <!-- Payout Breakdown Card -->
                            <table class="payout-card" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 18px 20px;">
                                        <div style="font-size: 15px; font-weight: 700; color: #065f46; margin-bottom: 12px; border-bottom: 1px dashed #6ee7b7; padding-bottom: 8px;">
                                            Disbursement Summary
                                        </div>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13.5px; color: #047857; width: 40%;"><strong>Amount Disbursed:</strong></td>
                                                <td style="padding: 4px 0; font-size: 16px; font-weight: 800; color: #065f46; text-align: right;"><?= format_price($amount ?? 0) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13.5px; color: #047857;"><strong>Payout Type:</strong></td>
                                                <td style="padding: 4px 0; font-size: 13.5px; color: #065f46; text-align: right;"><?= e($payout_type_label ?? 'Event Revenue Payout') ?></td>
                                            </tr>
                                            <?php if (!empty($event_name)): ?>
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13.5px; color: #047857;"><strong>Associated Event:</strong></td>
                                                <td style="padding: 4px 0; font-size: 13.5px; color: #065f46; text-align: right;"><?= e($event_name) ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13.5px; color: #047857;"><strong>Payment Method:</strong></td>
                                                <td style="padding: 4px 0; font-size: 13.5px; color: #065f46; text-align: right;"><?= e($disbursement_method ?? 'Direct Bank Transfer') ?></td>
                                            </tr>
                                            <?php if (!empty($bank_name)): ?>
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13.5px; color: #047857;"><strong>Bank:</strong></td>
                                                <td style="padding: 4px 0; font-size: 13.5px; color: #065f46; text-align: right;"><?= e($bank_name) ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($masked_account)): ?>
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13.5px; color: #047857;"><strong>Account Number:</strong></td>
                                                <td style="padding: 4px 0; font-size: 13.5px; color: #065f46; text-align: right; font-family: monospace; font-size: 14px;"><?= e($masked_account) ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13.5px; color: #047857;"><strong>Transfer Ref:</strong></td>
                                                <td style="padding: 4px 0; font-size: 13px; color: #065f46; text-align: right; font-family: monospace;"><?= e($reference ?? ('TRF_' . uniqid())) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13.5px; color: #047857;"><strong>Date Processed:</strong></td>
                                                <td style="padding: 4px 0; font-size: 13px; color: #065f46; text-align: right;"><?= date('M d, Y • h:i A', strtotime($date_disbursed ?? 'now')) ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 13.5px; color: #475569; line-height: 1.5; margin-bottom: 22px;">
                                Direct bank transfers are usually credited within a few minutes to hours depending on interbank settlement speeds. If your funds do not reflect within 24 business hours, please check with your financial institution quoting the transfer reference above.
                            </p>

                            <!-- Call to Action -->
                            <div style="text-align: center; margin-bottom: 10px;">
                                <a href="<?= \url('/host/financials') ?>" class="btn-action" style="display: inline-block; background-color: #059669; color: #ffffff; padding: 12px 28px; font-size: 14px; font-weight: 700; border-radius: 8px; text-decoration: none; box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);">
                                    View Host Financials &amp; History &rarr;
                                </a>
                            </div>
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
                                <a href="<?= \url('/privacy') ?>" style="color: #059669; text-decoration: none; font-weight: 600;">Privacy Policy</a> &bull;
                                <a href="<?= \url('/terms') ?>" style="color: #059669; text-decoration: none; font-weight: 600;">Terms of Service</a> &bull;
                                <a href="<?= \url('/support') ?>" style="color: #059669; text-decoration: none; font-weight: 600;">Support Desk</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
