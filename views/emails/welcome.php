<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Eventlyy</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        table {
            border-spacing: 0;
            width: 100%;
        }

        td {
            padding: 0;
        }

        img {
            border: 0;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding-bottom: 40px;
        }

        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            font-family: sans-serif;
            color: #334155;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .header {
            background-color: #0B7366;
            padding: 32px 24px;
            text-align: center;
        }

        .brand-letter {
            display: inline-block;
            width: 36px;
            height: 36px;
            line-height: 36px;
            background-color: #ffffff;
            color: #0B7366;
            font-weight: 900;
            font-size: 22px;
            border-radius: 8px;
            text-align: center;
        }

        .brand-name {
            display: inline-block;
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            margin-left: 10px;
            vertical-align: middle;
        }

        .content {
            padding: 36px 32px;
            background-color: #ffffff;
        }

        h1 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 16px;
        }

        p {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-top: 0;
            margin-bottom: 24px;
        }

        .btn-wrap {
            text-align: center;
            margin: 32px 0;
        }

        .btn {
            background-color: #0B7366;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            font-weight: 700;
            font-size: 14px;
            border-radius: 12px;
            display: inline-block;
            box-shadow: 0 8px 16px rgba(11, 115, 102, 0.15);
        }

        .features {
            border-top: 1px solid #f1f5f9;
            padding-top: 24px;
            margin-top: 24px;
        }

        .feature-item {
            margin-bottom: 16px;
        }

        .feature-item strong {
            color: #0f172a;
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .feature-item span {
            color: #64748b;
            font-size: 13px;
        }

        .footer {
            padding: 24px 32px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }

        .footer a {
            color: #0B7366;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <center class="wrapper">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td align="center" style="padding: 40px 10px 20px 10px;">
                    <table class="main" cellpadding="0" cellspacing="0" border="0">
                        <!-- Header -->
                        <tr>
                            <td class="header">
                                <?php if ($logoSrc = email_logo_src()): ?>
                                    <img src="<?= $logoSrc ?>" alt="<?= e(site_setting('site_title', 'Evently')) ?>" height="38" style="display:inline-block; max-height:38px; height:38px; width:auto; vertical-align:middle; border:0; margin-right:8px;" />
                                    <span class="brand-name" style="display:inline-block; color:#ffffff; font-size:20px; font-weight:700; vertical-align:middle;"><?= e(site_setting('site_title', 'Evently')) ?></span>
                                <?php else: ?>
                                    <span class="brand-letter"><?= strtoupper(substr(site_setting('site_title', 'Evently'), 0, 1)) ?></span>
                                    <span class="brand-name"><?= e(site_setting('site_title', 'Evently')) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <!-- Content -->
                        <tr>
                            <td class="content">
                                <h1>Confirm your registration</h1>
                                <p><span style="text-transform: capitalize;"><?= e($name ?? 'User') ?></span>, welcome to <?= e(site_setting('site_title', 'Evently')) ?>! We are excited to have you join our platform. Set up your profile, browse upcoming experiences, or configure your host settings and start listing events in minutes.</p>

                                <p>Please click the button below to verify your email address and activate your account:</p>

                                <?php $vUrl = $verification_url ?? '#'; ?>
                                <div class="btn-wrap">
                                    <a href="<?= e($vUrl) ?>" class="btn" style="background-color:#0B7366; color:#ffffff !important; padding:14px 32px; border-radius:12px; font-weight:700; text-decoration:none;">Verify Account &rarr;</a>
                                </div>

                                <p>If you did not sign up for an <?= e(site_setting('site_title', 'Evently')) ?> account, you can safely ignore this email.</p>

                                <div class="features">
                                    <div class="feature-item">
                                        <strong>Explore tickets</strong>
                                        <span>Find live concerts, sporting games, and comedy festivals near you.</span>
                                    </div>
                                    <div class="feature-item">
                                        <strong>Host events</strong>
                                        <span>Configure banking details, scan attendee gate passes, and check
                                            statistics.</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <!-- Footer -->
                        <tr>
                            <td class="footer">
                                <p style="margin-bottom: 8px;">&copy; <?= date('Y') ?> <?= e(site_setting('site_title', 'Evently')) ?>. All rights reserved.</p>
                                <p style="margin-top: 12px;">
                                    <a href="<?= \url('/privacy') ?>" style="color:#0B7366; text-decoration:none; font-weight:600;">Privacy Policy</a> &bull;
                                    <a href="<?= \url('/terms') ?>" style="color:#0B7366; text-decoration:none; font-weight:600;">Terms of Service</a> &bull;
                                    <a href="<?= \url('/support') ?>" style="color:#0B7366; text-decoration:none; font-weight:600;">Support</a> &bull;
                                    <a href="<?= \url('/about') ?>" style="color:#0B7366; text-decoration:none; font-weight:600;">About</a>
                                </p>
                                <p style="margin-top: 10px; font-size: 11px; color: #94a3b8;">
                                    You're receiving this email because you created an account on <?= e(site_setting('site_title', 'Evently')) ?>.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>

</html>