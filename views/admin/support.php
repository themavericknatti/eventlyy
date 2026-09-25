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
 * @version 1.0.0
 */

?>

@extends('layouts/admin')

@section('title')
Support Inquiries - Admin Panel
@endsection

@section('content')
<section class="page-hero">
    <div>
        <span class="panel-badge">Admin — Support</span>
        <h1>Support Inquiries</h1>
        <p class="lede">Manage attendee &amp; host support messages, bug reports, and inquiry resolutions.</p>
    </div>
</section>

<!-- Stats & Status Filter Tabs -->
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
        <a href="?status=all<?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" 
           class="admin-action-btn <?= $currentStatus === 'all' ? 'primary' : '' ?>" style="padding:0.5rem 1rem; border-radius:10px; font-size:0.88rem; text-decoration:none;">
            All (<?= (int)($counts['total'] ?? 0) ?>)
        </a>
        <a href="?status=new<?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" 
           class="admin-action-btn <?= $currentStatus === 'new' ? 'primary' : '' ?>" style="padding:0.5rem 1rem; border-radius:10px; font-size:0.88rem; text-decoration:none;">
            🟡 New (<?= (int)($counts['new_count'] ?? 0) ?>)
        </a>
        <a href="?status=read<?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" 
           class="admin-action-btn <?= $currentStatus === 'read' ? 'primary' : '' ?>" style="padding:0.5rem 1rem; border-radius:10px; font-size:0.88rem; text-decoration:none;">
            🔵 Read (<?= (int)($counts['read_count'] ?? 0) ?>)
        </a>
        <a href="?status=resolved<?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" 
           class="admin-action-btn <?= $currentStatus === 'resolved' ? 'primary' : '' ?>" style="padding:0.5rem 1rem; border-radius:10px; font-size:0.88rem; text-decoration:none;">
            🟢 Resolved (<?= (int)($counts['resolved_count'] ?? 0) ?>)
        </a>
    </div>

    <!-- Search Box -->
    <form method="GET" action="" style="display:flex; gap:0.5rem;">
        <input type="hidden" name="status" value="<?= e($currentStatus) ?>" />
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search name, email, topic..." 
               style="padding:0.5rem 0.85rem; border-radius:10px; border:1px solid var(--border-color, rgba(255,255,255,0.15)); background:var(--card-bg, rgba(255,255,255,0.03)); color:var(--text); font-size:0.85rem;" />
        <button type="submit" class="admin-action-btn" style="padding:0.5rem 0.85rem; border-radius:10px; font-size:0.85rem; cursor:pointer;">Search</button>
    </form>
</div>

<!-- Inquiries List -->
<div class="form-card" style="border-radius:22px; padding:1.5rem;">
    <?php if (empty($inquiries)): ?>
        <div style="text-align:center; padding:3rem 1rem; color:var(--text-muted);">
            <div style="font-size:2.5rem; margin-bottom:0.5rem;">📬</div>
            <h3 style="margin:0 0 0.25rem;">No support inquiries found</h3>
            <p style="margin:0; font-size:0.9rem;">There are no messages matching your criteria at this time.</p>
        </div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:1.25rem;">
            <?php foreach ($inquiries as $inquiry):
                $badgeStyle = 'background:rgba(234, 179, 8, 0.12); color:#eab308;';
                if ($inquiry['status'] === 'read') {
                    $badgeStyle = 'background:rgba(59, 130, 246, 0.12); color:#3b82f6;';
                } elseif ($inquiry['status'] === 'resolved') {
                    $badgeStyle = 'background:rgba(16, 185, 129, 0.12); color:#10b981;';
                }
            ?>
                <div style="background:var(--card-bg, rgba(255,255,255,0.02)); border:1px solid var(--border-color, rgba(255,255,255,0.08)); border-radius:16px; padding:1.25rem;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.75rem; margin-bottom:0.75rem;">
                        <div>
                            <div style="display:flex; align-items:center; gap:0.6rem; flex-wrap:wrap;">
                                <span style="font-size:0.72rem; padding:0.2rem 0.6rem; border-radius:20px; font-weight:700; text-transform:uppercase; <?= $badgeStyle ?>">
                                    <?= ucfirst(e($inquiry['status'])) ?>
                                </span>
                                <span style="font-size:0.75rem; background:rgba(var(--brand),0.1); color:rgb(var(--brand)); padding:0.2rem 0.6rem; border-radius:20px; font-weight:600;">
                                    Topic: <?= ucfirst(e($inquiry['subject'])) ?>
                                </span>
                            </div>
                            <h3 style="margin:0.5rem 0 0.15rem; font-size:1.1rem; color:var(--text);"><?= e($inquiry['name']) ?></h3>
                            <div style="font-size:0.82rem; color:var(--text-muted);">
                                ✉️ <a href="mailto:<?= e($inquiry['email']) ?>" style="color:var(--brand); text-decoration:none;"><?= e($inquiry['email']) ?></a>
                                • 📅 <?= date('M d, Y • g:i A', strtotime($inquiry['created_at'])) ?>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                            <?php if ($inquiry['status'] === 'new'): ?>
                                <form method="POST" action="{{ route('admin/support') }}" style="margin:0;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="mark_read" />
                                    <input type="hidden" name="id" value="<?= $inquiry['id'] ?>" />
                                    <button type="submit" class="admin-action-btn" style="padding:0.4rem 0.75rem; font-size:0.8rem; border-radius:8px; cursor:pointer;">
                                        Mark as Read
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($inquiry['status'] !== 'resolved'): ?>
                                <form method="POST" action="{{ route('admin/support') }}" style="margin:0;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="mark_resolved" />
                                    <input type="hidden" name="id" value="<?= $inquiry['id'] ?>" />
                                    <button type="submit" class="admin-action-btn primary" style="padding:0.4rem 0.75rem; font-size:0.8rem; border-radius:8px; cursor:pointer;">
                                        ✓ Resolve
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" action="{{ route('admin/support') }}" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this inquiry?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="id" value="<?= $inquiry['id'] ?>" />
                                <button type="submit" class="admin-action-btn danger" style="padding:0.4rem 0.75rem; font-size:0.8rem; border-radius:8px; cursor:pointer; color:#ef4444;">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Message Body -->
                    <div style="background:rgba(0,0,0,0.15); border-left:3px solid var(--brand); padding:0.85rem 1rem; border-radius:0 10px 10px 0; font-size:0.92rem; line-height:1.6; color:var(--text); white-space:pre-wrap;">
                        <?= e($inquiry['message']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
@endsection
