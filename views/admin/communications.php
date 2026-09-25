<?php

/**
 * Admin Communications Center View
 *
 * This file contains the HTML markup for the admin communications/broadcast page.
 *
 * @package Evently
 * @subpackage Views/Admin
 * @version 1.0.0
 */

?>

@extends('layouts/admin')

@section('title')
Communications Center - Admin
@endsection

@section('content')
<?php
    $historyItems = $history['items'] ?? [];
    $totalPages = $history['totalPages'] ?? 1;
    $currentPage = $history['page'] ?? 1;
?>

<!-- Page Header -->
<div style="margin-bottom: 2rem;">
    <h1 style="margin: 0; font-size: 1.65rem;">📨 Communications Center</h1>
    <p style="margin: 0.3rem 0 0; color: var(--text-muted, #64748b); font-size: 0.92rem;">Send announcements, updates, and messages to hosts and users via email</p>
</div>

<!-- Compose & Preview Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; align-items: start;">

    <!-- Compose Form -->
    <div style="background: var(--bg-card, #fff); border: 1px solid rgba(var(--muted), 0.1); border-radius: 16px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <h2 style="margin: 0 0 1.25rem; font-size: 1.15rem; display: flex; align-items: center; gap: 0.5rem;">
            ✍️ Compose Message
        </h2>
        <form method="POST" action="/admin/communications" id="broadcastForm">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <!-- Audience Type -->
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted, #64748b); margin-bottom: 0.35rem;">Audience <span style="color: #ef4444;">*</span></label>
                <select name="audience_type" id="audienceType" required style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid rgba(var(--muted), 0.15); border-radius: 10px; font-size: 0.9rem; background: var(--bg-card, #fff); color: var(--text-primary, #1e293b); appearance: auto;">
                    <option value="">— Select Audience —</option>
                    <option value="all_users">📢 All Users</option>
                    <option value="all_hosts">🎪 All Hosts / Organizers</option>
                    <option value="single_host">👤 Individual Host</option>
                    <option value="single_user">👤 Individual User</option>
                </select>
            </div>

            <!-- Recipient Email (shown for individual) -->
            <div id="recipientField" style="margin-bottom: 1rem; display: none;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted, #64748b); margin-bottom: 0.35rem;">Recipient Email <span style="color: #ef4444;">*</span></label>
                <div style="position: relative;">
                    <input type="email" name="recipient_email" id="recipientEmail" placeholder="Search by name or email..." autocomplete="off" style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid rgba(var(--muted), 0.15); border-radius: 10px; font-size: 0.9rem; background: var(--bg-card, #fff); color: var(--text-primary, #1e293b); box-sizing: border-box;">
                    <div id="recipientDropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: var(--bg-card, #fff); border: 1px solid rgba(var(--muted), 0.15); border-radius: 10px; margin-top: 4px; max-height: 200px; overflow-y: auto; z-index: 100; box-shadow: 0 8px 24px rgba(0,0,0,0.12);"></div>
                </div>
            </div>

            <!-- Priority -->
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted, #64748b); margin-bottom: 0.35rem;">Priority</label>
                <select name="priority" id="prioritySelect" style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid rgba(var(--muted), 0.15); border-radius: 10px; font-size: 0.9rem; background: var(--bg-card, #fff); color: var(--text-primary, #1e293b); appearance: auto;">
                    <option value="normal">🟢 Normal</option>
                    <option value="urgent">🔴 Urgent</option>
                </select>
            </div>

            <!-- Subject -->
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted, #64748b); margin-bottom: 0.35rem;">Subject <span style="color: #ef4444;">*</span></label>
                <input type="text" name="subject" id="subjectInput" required placeholder="Enter email subject..." style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid rgba(var(--muted), 0.15); border-radius: 10px; font-size: 0.9rem; background: var(--bg-card, #fff); color: var(--text-primary, #1e293b); box-sizing: border-box;">
            </div>

            <!-- Message Body -->
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted, #64748b); margin-bottom: 0.35rem;">Message <span style="color: #ef4444;">*</span></label>
                <textarea name="message" id="messageInput" rows="6" required placeholder="Write your message here..." style="width: 100%; padding: 0.65rem 0.85rem; border: 1.5px solid rgba(var(--muted), 0.15); border-radius: 10px; font-size: 0.9rem; resize: vertical; font-family: inherit; background: var(--bg-card, #fff); color: var(--text-primary, #1e293b); box-sizing: border-box;"></textarea>
            </div>

            <!-- CTA (Optional) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-muted, #64748b); margin-bottom: 0.3rem;">CTA Button Text <span style="font-weight: 400; opacity: 0.6;">(optional)</span></label>
                    <input type="text" name="cta_text" id="ctaTextInput" placeholder="e.g. Visit Dashboard" style="width: 100%; padding: 0.55rem 0.75rem; border: 1.5px solid rgba(var(--muted), 0.15); border-radius: 10px; font-size: 0.88rem; background: var(--bg-card, #fff); color: var(--text-primary, #1e293b); box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-muted, #64748b); margin-bottom: 0.3rem;">CTA Button URL <span style="font-weight: 400; opacity: 0.6;">(optional)</span></label>
                    <input type="url" name="cta_url" id="ctaUrlInput" placeholder="https://..." style="width: 100%; padding: 0.55rem 0.75rem; border: 1.5px solid rgba(var(--muted), 0.15); border-radius: 10px; font-size: 0.88rem; background: var(--bg-card, #fff); color: var(--text-primary, #1e293b); box-sizing: border-box;">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.7rem 1.5rem; border-radius: 10px; font-weight: 700; font-size: 0.95rem; cursor: pointer;">
                📨 Send Broadcast
            </button>
        </form>
    </div>

    <!-- Live Email Preview -->
    <div style="background: var(--bg-card, #fff); border: 1px solid rgba(var(--muted), 0.1); border-radius: 16px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04); position: sticky; top: 1rem;">
        <h2 style="margin: 0 0 1rem; font-size: 1.15rem; display: flex; align-items: center; gap: 0.5rem;">
            👁️ Email Preview
        </h2>
        <div id="emailPreview" style="border: 1px solid rgba(var(--muted), 0.12); border-radius: 12px; overflow: hidden; font-size: 0.85rem;">
            <!-- Preview Header -->
            <div style="background: #0B7366; padding: 16px; text-align: center;">
                <span style="display: inline-block; width: 28px; height: 28px; line-height: 28px; background: #fff; color: #0B7366; font-weight: 900; font-size: 16px; border-radius: 6px;">E</span>
                <span style="color: #fff; font-weight: 700; font-size: 14px; margin-left: 6px; vertical-align: middle;" id="previewBrand"><?= e(site_setting('site_title', 'Eventlyy')) ?></span>
            </div>
            <!-- Preview Body -->
            <div style="padding: 20px; background: #fff;" id="previewBody">
                <div id="previewUrgent" style="display: none; background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; padding: 8px 12px; font-weight: 700; font-size: 12px; text-align: center; border-radius: 6px; margin-bottom: 12px;">
                    ⚠️ Urgent Communication
                </div>
                <p style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 8px;" id="previewSubject">Email Subject</p>
                <p style="font-size: 13px; color: #475569; margin: 0 0 12px;">Hi <span id="previewRecipient">Recipient</span>,</p>
                <div style="font-size: 13px; line-height: 1.6; color: #475569; margin-bottom: 16px; min-height: 60px; white-space: pre-wrap;" id="previewMessage">Your message will appear here...</div>
                <div id="previewCta" style="display: none; text-align: center; margin: 16px 0;">
                    <span style="display: inline-block; background: #0B7366; color: #fff; padding: 8px 20px; font-weight: 700; font-size: 12px; border-radius: 8px;" id="previewCtaBtn">Button Text</span>
                </div>
                <p style="font-size: 11px; color: #94a3b8; margin: 16px 0 0; border-top: 1px solid #f1f5f9; padding-top: 12px;">
                    This message was sent by the <?= e(site_setting('site_title', 'Eventlyy')) ?> team.
                </p>
            </div>
            <!-- Preview Footer -->
            <div style="background: #f8fafc; padding: 12px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #f1f5f9;">
                &copy; <?= date('Y') ?> <?= e(site_setting('site_title', 'Eventlyy')) ?>. All rights reserved.
            </div>
        </div>
    </div>
</div>

<!-- Broadcast History -->
<div style="background: var(--bg-card, #fff); border: 1px solid rgba(var(--muted), 0.1); border-radius: 16px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <h2 style="margin: 0; font-size: 1.15rem;">📋 Broadcast History</h2>
        <?php if (!empty($historyItems)): ?>
            <span style="font-size: 0.82rem; color: var(--text-muted, #64748b);"><?= $history['total'] ?? 0 ?> total log entries</span>
        <?php endif; ?>
    </div>

    <?php if (empty($historyItems)): ?>
        <div style="text-align: center; padding: 3rem 1rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">📭</div>
            <p style="font-size: 1rem; font-weight: 600; color: var(--text-primary, #1e293b); margin: 0 0 0.3rem;">No broadcasts sent yet</p>
            <p style="font-size: 0.88rem; color: var(--text-muted, #64748b); margin: 0;">Compose your first message above to get started.</p>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(var(--muted), 0.1); text-align: left;">
                        <th style="padding: 0.65rem 0.5rem; color: var(--text-muted, #64748b); font-weight: 600; white-space: nowrap;">Date</th>
                        <th style="padding: 0.65rem 0.5rem; color: var(--text-muted, #64748b); font-weight: 600;">Subject</th>
                        <th style="padding: 0.65rem 0.5rem; color: var(--text-muted, #64748b); font-weight: 600;">Audience</th>
                        <th style="padding: 0.65rem 0.5rem; color: var(--text-muted, #64748b); font-weight: 600;">Recipient</th>
                        <th style="padding: 0.65rem 0.5rem; color: var(--text-muted, #64748b); font-weight: 600;">Priority</th>
                        <th style="padding: 0.65rem 0.5rem; color: var(--text-muted, #64748b); font-weight: 600;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historyItems as $log): ?>
                        <?php
                            $audienceLabels = [
                                'all_users' => ['📢', 'All Users'],
                                'all_hosts' => ['🎪', 'All Hosts'],
                                'single_host' => ['👤', 'Host'],
                                'single_user' => ['👤', 'User'],
                            ];
                            $aud = $audienceLabels[$log['audience_type']] ?? ['📧', $log['audience_type']];
                        ?>
                        <tr style="border-bottom: 1px solid rgba(var(--muted), 0.06);">
                            <td style="padding: 0.65rem 0.5rem; white-space: nowrap; color: var(--text-muted, #64748b); font-size: 0.82rem;">
                                <?= !empty($log['sent_at']) ? date('M d, Y H:i', strtotime($log['sent_at'])) : '—' ?>
                            </td>
                            <td style="padding: 0.65rem 0.5rem; font-weight: 600; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= e($log['subject'] ?? '') ?>
                            </td>
                            <td style="padding: 0.65rem 0.5rem;">
                                <span style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.82rem;">
                                    <?= $aud[0] ?> <?= e($aud[1]) ?>
                                </span>
                            </td>
                            <td style="padding: 0.65rem 0.5rem; font-size: 0.82rem; color: var(--text-muted, #64748b);">
                                <?php if (in_array($log['audience_type'], ['single_host', 'single_user'])): ?>
                                    <?= e($log['recipient_name'] ?? '') ?> <span style="opacity:0.5;">(<?= e($log['recipient_email'] ?? '') ?>)</span>
                                <?php else: ?>
                                    <?= (int)($log['recipient_count'] ?? 0) ?> recipients
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.65rem 0.5rem;">
                                <?php if ($log['priority'] === 'urgent'): ?>
                                    <span style="background: rgba(239, 68, 68, 0.12); color: #ef4444; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 6px;">🔴 Urgent</span>
                                <?php else: ?>
                                    <span style="background: rgba(16, 185, 129, 0.12); color: #10b981; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 6px;">🟢 Normal</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.65rem 0.5rem;">
                                <?php if ($log['status'] === 'sent'): ?>
                                    <span style="background: rgba(16, 185, 129, 0.12); color: #10b981; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 6px;">✅ Sent</span>
                                <?php else: ?>
                                    <span style="background: rgba(239, 68, 68, 0.12); color: #ef4444; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 6px;">❌ Failed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 1.25rem; flex-wrap: wrap;">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="/admin/communications?page=<?= $p ?>" style="padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; <?= $p === $currentPage ? 'background: rgb(var(--brand)); color: #fff;' : 'background: rgba(var(--muted), 0.08); color: var(--text-primary, #1e293b);' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    /* Responsive grid for compose + preview */
    @media (max-width: 900px) {
        div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
    @media (max-width: 600px) {
        div[style*="grid-template-columns: 1fr 1fr"][style*="gap: 0.75rem"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const audienceSelect = document.getElementById('audienceType');
    const recipientField = document.getElementById('recipientField');
    const recipientInput = document.getElementById('recipientEmail');
    const recipientDropdown = document.getElementById('recipientDropdown');
    const subjectInput = document.getElementById('subjectInput');
    const messageInput = document.getElementById('messageInput');
    const prioritySelect = document.getElementById('prioritySelect');
    const ctaTextInput = document.getElementById('ctaTextInput');
    const ctaUrlInput = document.getElementById('ctaUrlInput');

    // Preview elements
    const previewSubject = document.getElementById('previewSubject');
    const previewMessage = document.getElementById('previewMessage');
    const previewUrgent = document.getElementById('previewUrgent');
    const previewCta = document.getElementById('previewCta');
    const previewCtaBtn = document.getElementById('previewCtaBtn');
    const previewRecipient = document.getElementById('previewRecipient');

    // Audience toggle
    audienceSelect.addEventListener('change', function() {
        const val = this.value;
        const showIndividual = (val === 'single_host' || val === 'single_user');
        recipientField.style.display = showIndividual ? 'block' : 'none';
        if (!showIndividual) recipientInput.value = '';

        // Update preview recipient
        const labels = { all_users: 'User', all_hosts: 'Host', single_host: 'Host', single_user: 'User' };
        previewRecipient.textContent = labels[val] || 'Recipient';
    });

    // Live preview updates
    subjectInput.addEventListener('input', function() {
        previewSubject.textContent = this.value || 'Email Subject';
    });

    messageInput.addEventListener('input', function() {
        previewMessage.textContent = this.value || 'Your message will appear here...';
    });

    prioritySelect.addEventListener('change', function() {
        previewUrgent.style.display = this.value === 'urgent' ? 'block' : 'none';
    });

    ctaTextInput.addEventListener('input', updateCtaPreview);
    ctaUrlInput.addEventListener('input', updateCtaPreview);

    function updateCtaPreview() {
        const text = ctaTextInput.value.trim();
        const url = ctaUrlInput.value.trim();
        if (text && url) {
            previewCta.style.display = 'block';
            previewCtaBtn.textContent = text;
        } else {
            previewCta.style.display = 'none';
        }
    }

    // Recipient autocomplete
    let searchTimeout = null;
    recipientInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(searchTimeout);
        if (query.length < 2) {
            recipientDropdown.style.display = 'none';
            return;
        }

        const type = audienceSelect.value === 'single_host' ? 'host' : 'all';
        searchTimeout = setTimeout(() => {
            fetch('/admin/communications/search?q=' + encodeURIComponent(query) + '&type=' + type)
                .then(r => r.json())
                .then(results => {
                    if (!results.length) {
                        recipientDropdown.style.display = 'none';
                        return;
                    }
                    recipientDropdown.innerHTML = results.map(r => 
                        '<div style="padding: 0.55rem 0.85rem; cursor: pointer; font-size: 0.88rem; border-bottom: 1px solid rgba(0,0,0,0.04); transition: background 0.15s;" ' +
                        'onmouseover="this.style.background=\'rgba(99,102,241,0.06)\'" ' +
                        'onmouseout="this.style.background=\'transparent\'" ' +
                        'data-email="' + (r.email || '') + '">' +
                        '<strong>' + (r.name || '') + '</strong>' +
                        '<span style="color: #94a3b8; margin-left: 0.5rem; font-size: 0.82rem;">' + (r.email || '') + '</span>' +
                        '</div>'
                    ).join('');
                    recipientDropdown.style.display = 'block';

                    recipientDropdown.querySelectorAll('[data-email]').forEach(el => {
                        el.addEventListener('click', () => {
                            recipientInput.value = el.getAttribute('data-email');
                            recipientDropdown.style.display = 'none';
                            previewRecipient.textContent = el.querySelector('strong').textContent || 'Recipient';
                        });
                    });
                })
                .catch(() => { recipientDropdown.style.display = 'none'; });
        }, 300);
    });

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (!recipientInput.contains(e.target) && !recipientDropdown.contains(e.target)) {
            recipientDropdown.style.display = 'none';
        }
    });
});
</script>

@endsection
