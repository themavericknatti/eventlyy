<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($subject ?? 'Message') ?></title>
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
            margin-bottom: 16px;
        }

        .urgent-banner {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 12px 20px;
            font-weight: 700;
            font-size: 14px;
            text-align: center;
            border-radius: 8px;
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

        .footer {
            background-color: #f8fafc;
            padding: 24px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }

        .footer a {
            color: #0B7366;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <center class="wrapper">
        <table class="main" role="presentation">
            <!-- Header -->
            <tr>
                <td class="header">
                    <?php if ($logoSrc = email_logo_src()): ?>
                        <img src="<?= $logoSrc ?>" alt="<?= e($siteName ?? site_setting('site_title', 'Evently')) ?>" height="38" style="display:inline-block; max-height:38px; height:38px; width:auto; vertical-align:middle; border:0; margin-right:8px;" />
                        <span class="brand-name" style="display:inline-block; color:#ffffff; font-size:20px; font-weight:700; vertical-align:middle;"><?= e($siteName ?? site_setting('site_title', 'Evently')) ?></span>
                    <?php else: ?>
                        <span class="brand-letter"><?= strtoupper(mb_substr($siteName ?? 'E', 0, 1)) ?></span>
                        <span class="brand-name"><?= e($siteName ?? site_setting('site_title', 'Evently')) ?></span>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- Content -->
            <tr>
                <td class="content">
                    <?php if (($priority ?? 'normal') === 'urgent'): ?>
                        <div class="urgent-banner">
                            ⚠️ Urgent Communication
                        </div>
                    <?php endif; ?>

                    <h1><?= e($subject ?? 'Message from the Team') ?></h1>

                    <p style="margin-bottom: 8px;">Hi <?= e($recipientName ?? 'there') ?>,</p>

                    <div style="font-size: 15px; line-height: 1.7; color: #475569; margin-bottom: 24px;">
                        <?= nl2br(e($messageBody ?? '')) ?>
                    </div>

                    <?php if (!empty($ctaText) && !empty($ctaUrl)): ?>
                        <div class="btn-wrap">
                            <a href="<?= e($ctaUrl) ?>" class="btn"><?= e($ctaText) ?></a>
                        </div>
                    <?php endif; ?>

                    <p style="font-size: 13px; color: #94a3b8; margin-top: 32px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                        This message was sent by the <?= e($siteName ?? 'Eventlyy') ?> team. If you believe you received this email in error, please disregard it.
                    </p>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="footer">
                    <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                        &copy; <?= date('Y') ?> <?= e($siteName ?? 'Eventlyy') ?>. All rights reserved.
                    </p>
                </td>
            </tr>
        </table>
    </center>
</body>

</html>
