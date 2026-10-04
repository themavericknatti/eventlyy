<?php

/**
 * Admin Support Inquiries Page
 *
 * Displays submitted support messages, inquiry filters, and status management.
 *
 * @package Evently
 * @subpackage Views/Admin
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 2.0.0
 */

?>

@extends('layouts/admin')

@section('title')
Support Inquiries - Admin Panel
@endsection

@section('content')
<style>
/* ═══════════════════════════════════════════════════════════
   Admin Support Inquiries — Responsive Layout
   ═══════════════════════════════════════════════════════════ */

.support-filters {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.support-filter-pills {
    display: flex;
    gap: 0.45rem;
    flex-wrap: wrap;
}

.support-filter-pill {
    padding: 0.5rem 1rem;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: rgba(var(--muted), 0.08);
    color: rgb(var(--text));
    border: 1px solid rgba(var(--muted), 0.15);
    transition: all 0.2s ease;
    white-space: nowrap;
}

.support-filter-pill:hover {
    background: rgba(var(--brand), 0.1);
    border-color: rgba(var(--brand), 0.25);
    color: rgb(var(--brand));
}

.support-filter-pill.active {
    background: rgb(var(--brand));
    color: #fff;
    border-color: rgb(var(--brand));
}

.support-search {
    display: flex;
    gap: 0.5rem;
}

.support-search input[type="text"] {
    padding: 0.5rem 0.85rem;
    border-radius: 10px;
    border: 1px solid rgba(var(--muted), 0.18);
    background: rgba(var(--card), 0.6);
    color: rgb(var(--text));
    font-size: 0.85rem;
    min-width: 180px;
    font-family: inherit;
}

.support-search button {
    padding: 0.5rem 0.85rem;
    border-radius: 10px;
    font-size: 0.85rem;
    cursor: pointer;
    background: rgba(var(--muted), 0.1);
    color: rgb(var(--text));
    border: 1px solid rgba(var(--muted), 0.18);
    font-weight: 700;
    font-family: inherit;
    transition: all 0.2s ease;
}

.support-search button:hover {
    background: rgba(var(--brand), 0.12);
    border-color: rgba(var(--brand), 0.3);
    color: rgb(var(--brand));
}

/* ── Inquiry Card ── */
.support-inquiry-card {
    background: rgba(var(--card), 0.5);
    border: 1px solid rgba(var(--muted), 0.12);
    border-radius: 18px;
    padding: 1.35rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.support-inquiry-card:hover {
    border-color: rgba(var(--brand), 0.2);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.support-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 0.85rem;
}

.support-card-meta {
    flex: 1;
    min-width: 0;
}

.support-badges {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.45rem;
}

.support-status-badge {
    font-size: 0.72rem;
    padding: 0.22rem 0.65rem;
    border-radius: 20px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    white-space: nowrap;
}

.support-status-badge--new {
    background: rgba(234, 179, 8, 0.14);
    color: #eab308;
}

.support-status-badge--read {
    background: rgba(59, 130, 246, 0.14);
    color: #3b82f6;
}

.support-status-badge--resolved {
    background: rgba(16, 185, 129, 0.14);
    color: #10b981;
}

.support-topic-badge {
    font-size: 0.73rem;
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    padding: 0.22rem 0.65rem;
    border-radius: 20px;
    font-weight: 600;
    white-space: nowrap;
}

.support-card-name {
    margin: 0.4rem 0 0.15rem;
    font-size: 1.08rem;
    font-weight: 800;
    color: rgb(var(--text));
}

.support-card-contact {
    font-size: 0.82rem;
    color: rgb(var(--muted));
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.support-card-contact a {
    color: rgb(var(--brand));
    text-decoration: none;
    font-weight: 600;
}

.support-card-contact a:hover {
    text-decoration: underline;
}

/* ── Action Buttons ── */
.support-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    flex-shrink: 0;
    align-items: flex-start;
}

.support-action-btn {
    padding: 0.45rem 0.85rem;
    font-size: 0.82rem;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    border: 1px solid transparent;
    transition: all 0.2s ease;
    white-space: nowrap;
    -webkit-tap-highlight-color: transparent;
}

.support-action-btn--read {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
    border-color: rgba(59, 130, 246, 0.25);
}

.support-action-btn--read:hover {
    background: rgba(59, 130, 246, 0.2);
    border-color: rgba(59, 130, 246, 0.4);
}

.support-action-btn--resolve {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border-color: rgba(16, 185, 129, 0.25);
}

.support-action-btn--resolve:hover {
    background: rgba(16, 185, 129, 0.2);
    border-color: rgba(16, 185, 129, 0.4);
}

.support-action-btn--delete {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.2);
}

.support-action-btn--delete:hover {
    background: rgba(239, 68, 68, 0.18);
    border-color: rgba(239, 68, 68, 0.4);
}

/* ── Message Body ── */
.support-message-body {
    background: rgba(var(--muted), 0.06);
    border-left: 3px solid rgb(var(--brand));
    padding: 0.85rem 1rem;
    border-radius: 0 12px 12px 0;
    font-size: 0.92rem;
    line-height: 1.65;
    color: rgb(var(--text));
    white-space: pre-wrap;
    word-break: break-word;
}

/* ── Empty State ── */
.support-empty-state {
    text-align: center;
    padding: 3.5rem 1.5rem;
    color: rgb(var(--muted));
}

.support-empty-state .support-empty-icon {
    font-size: 3rem;
    margin-bottom: 0.65rem;
    display: block;
}

.support-empty-state h3 {
    margin: 0 0 0.3rem;
    color: rgb(var(--text));
    font-size: 1.1rem;
}

.support-empty-state p {
    margin: 0;
    font-size: 0.9rem;
}

/* ══ Tablet (<= 768px) ══ */
@media (max-width: 768px) {
    .support-filters {
        flex-direction: column;
        align-items: stretch;
    }

    .support-search {
        width: 100%;
    }

    .support-search input[type="text"] {
        flex: 1;
        min-width: 0;
    }

    .support-card-header {
        flex-direction: column;
        gap: 0.75rem;
    }

    .support-actions {
        width: 100%;
    }

    .support-action-btn {
        flex: 1;
        justify-content: center;
        min-width: 0;
    }
}

/* ══ Mobile (<= 480px) ══ */
@media (max-width: 480px) {
    .support-filter-pills {
        width: 100%;
        overflow-x: auto;
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 0.25rem;
    }

    .support-filter-pills::-webkit-scrollbar {
        display: none;
    }

    .support-filter-pill {
        font-size: 0.82rem;
        padding: 0.45rem 0.8rem;
        flex-shrink: 0;
    }

    .support-inquiry-card {
        padding: 1rem;
        border-radius: 14px;
    }

    .support-card-name {
        font-size: 1rem;
    }

    .support-card-contact {
        font-size: 0.78rem;
    }

    .support-message-body {
        font-size: 0.88rem;
        padding: 0.75rem 0.85rem;
    }

    .support-action-btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
    }
}
</style>

<section class="page-hero">
    <div>
        <span class="panel-badge">Admin — Support</span>
        <h1>Support Inquiries</h1>
        <p class="lede">Manage attendee &amp; host support messages, bug reports, and inquiry resolutions.</p>
    </div>
</section>

<!-- Stats & Status Filter Tabs -->
<div class="support-filters">
    <div class="support-filter-pills">
        <a href="?status=all<?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" 
           class="support-filter-pill <?= $currentStatus === 'all' ? 'active' : '' ?>">
            All (<?= (int)($counts['total'] ?? 0) ?>)
        </a>
        <a href="?status=new<?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" 
           class="support-filter-pill <?= $currentStatus === 'new' ? 'active' : '' ?>">
            🟡 New (<?= (int)($counts['new_count'] ?? 0) ?>)
        </a>
        <a href="?status=read<?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" 
           class="support-filter-pill <?= $currentStatus === 'read' ? 'active' : '' ?>">
            🔵 Read (<?= (int)($counts['read_count'] ?? 0) ?>)
        </a>
        <a href="?status=resolved<?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" 
           class="support-filter-pill <?= $currentStatus === 'resolved' ? 'active' : '' ?>">
            🟢 Resolved (<?= (int)($counts['resolved_count'] ?? 0) ?>)
        </a>
    </div>

    <!-- Search Box -->
    <form method="GET" action="" class="support-search">
        <input type="hidden" name="status" value="<?= e($currentStatus) ?>" />
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search name, email, topic..." />
        <button type="submit">🔍 Search</button>
    </form>
</div>

<!-- Inquiries List -->
<div class="form-card" style="border-radius:22px; padding:1.5rem;">
    <?php if (empty($inquiries)): ?>
        <div class="support-empty-state">
            <span class="support-empty-icon">📬</span>
            <h3>No support inquiries found</h3>
            <p>There are no messages matching your criteria at this time.</p>
        </div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:1.15rem;">
            <?php foreach ($inquiries as $inquiry):
                $statusClass = 'support-status-badge--new';
                if ($inquiry['status'] === 'read') {
                    $statusClass = 'support-status-badge--read';
                } elseif ($inquiry['status'] === 'resolved') {
                    $statusClass = 'support-status-badge--resolved';
                }
            ?>
                <div class="support-inquiry-card">
                    <div class="support-card-header">
                        <div class="support-card-meta">
                            <div class="support-badges">
                                <span class="support-status-badge <?= $statusClass ?>">
                                    <?= ucfirst(e($inquiry['status'])) ?>
                                </span>
                                <span class="support-topic-badge">
                                    Topic: <?= ucfirst(e($inquiry['subject'])) ?>
                                </span>
                            </div>
                            <h3 class="support-card-name"><?= e($inquiry['name']) ?></h3>
                            <div class="support-card-contact">
                                <span>✉️ <a href="mailto:<?= e($inquiry['email']) ?>"><?= e($inquiry['email']) ?></a></span>
                                <span>•</span>
                                <span>📅 <?= date('M d, Y • g:i A', strtotime($inquiry['created_at'])) ?></span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="support-actions">
                            <?php if ($inquiry['status'] === 'new'): ?>
                                <form method="POST" action="{{ route('admin/support') }}" style="margin:0;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="mark_read" />
                                    <input type="hidden" name="id" value="<?= $inquiry['id'] ?>" />
                                    <button type="submit" class="support-action-btn support-action-btn--read">
                                        👁️ Mark Read
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($inquiry['status'] !== 'resolved'): ?>
                                <form method="POST" action="{{ route('admin/support') }}" style="margin:0;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="mark_resolved" />
                                    <input type="hidden" name="id" value="<?= $inquiry['id'] ?>" />
                                    <button type="submit" class="support-action-btn support-action-btn--resolve">
                                        ✅ Resolve
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" action="{{ route('admin/support') }}" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this inquiry?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="id" value="<?= $inquiry['id'] ?>" />
                                <button type="submit" class="support-action-btn support-action-btn--delete">
                                    🗑️ Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Message Body -->
                    <div class="support-message-body"><?= e($inquiry['message']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
@endsection
