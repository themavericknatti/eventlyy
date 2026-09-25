<?php

/**
 * Team Event Organiser (Box Office/Finance) Dashboard Page
 *
 * Workstation task view for Event Organisers to manage:
 * - Door/Gate ticket purchases and complimentary ticket issuance
 * - Staff check-in and deployment status
 * - Live check-in rates and gate capacity
 *
 * @package Evently
 * @subpackage Views/Teams
 * @author Celio Natti
 * @version 2.3.0
 */

$eventTime = !empty($event['date']) ? strtotime($event['date']) : null;
$staffRoster = $staff_roster ?? [];
$allTicketTypes = $all_ticket_types ?? [];
$currencySymbol = get_currency_symbol();
?>

@extends('layouts/team')

@section('title')
Event Organiser (Box Office) - <?= e($event['name'] ?? 'Evently') ?>
@endsection

@section('style')
<style>
.coord-hero {
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}
.coord-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.coord-content-grid {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 1.5rem;
    margin-top: 1.5rem;
}
.staff-roster-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgb(var(--bg));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 14px;
    padding: 0.9rem 1.15rem;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.gate-sale-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

@media (max-width: 1024px) {
    .coord-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .coord-content-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 768px) {
    .coord-hero {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }
    .coord-hero button {
        width: 100%;
        justify-content: center;
    }
    .coord-stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.65rem;
    }
    .staff-roster-item {
        flex-direction: column;
        align-items: stretch;
        padding: 0.85rem;
        gap: 0.65rem;
    }
    .staff-roster-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        flex-wrap: wrap;
        gap: 0.4rem;
        padding-top: 0.4rem;
        border-top: 1px dashed rgba(var(--muted), 0.15);
    }
    .gate-sale-grid-2 {
        grid-template-columns: 1fr;
        gap: 0.65rem;
    }
}
@media (max-width: 480px) {
    .coord-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')
<section class="coord-hero">
    <div>
        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem; flex-wrap:wrap;">
            <span class="panel-badge" style="background:rgba(99,102,241,0.15); color:#6366f1; font-weight:800;">
                🎧 Workstation — Event Organiser (Box Office/Finance)
            </span>
            <span style="font-size:0.75rem; font-weight:800; background:rgba(16,185,129,0.15); color:#059669; padding:0.25rem 0.65rem; border-radius:10px;">
                ● Box Office Active
            </span>
        </div>
        <h1 style="font-size:clamp(1.35rem, 3vw, 1.85rem); margin:0.25rem 0; color:rgb(var(--text));"><?= e($event['name'] ?? 'Event Organizer Desk') ?></h1>
        <p class="lede" style="margin:0; font-size:0.92rem; color:rgb(var(--muted));">
            <?php if ($eventTime): ?>
                <span>📅 <?= date('D, M d, Y • g:i A', $eventTime) ?></span> •
            <?php endif; ?>
            <span>📍 <?= e($event['location'] ?? 'Venue') ?></span>
        </p>
    </div>

    <!-- Quick Gate Action -->
    <div>
        <button type="button" id="btn-open-box-office" class="checkout-btn" style="background:linear-gradient(135deg, #6366f1, #4f46e5); color:#fff; font-weight:800; padding:0.7rem 1.35rem; border-radius:14px; display:inline-flex; align-items:center; gap:0.45rem; border:none; cursor:pointer; box-shadow:0 8px 20px rgba(99,102,241,0.35);">
            <span>🎟️</span> Sell / Issue Gate Ticket
        </button>
    </div>
</section>

<!-- Overall Check-in & Gate Stats -->
<section class="coord-stats-grid">
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--brand">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num" style="color:rgb(var(--brand));"><?= number_format($stats['checked_in'] ?? 0) ?></span>
            <span class="au-stat-lbl">Checked-In Attendees</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($stats['total_tickets'] ?? 0) ?></span>
            <span class="au-stat-lbl">Total Issued Passes</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--warning">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num" style="color:#d97706;"><?= number_format($stats['remaining'] ?? 0) ?></span>
            <span class="au-stat-lbl">Awaiting Arrival</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--success">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10" />
                <line x1="12" y1="20" x2="12" y2="4" />
                <line x1="6" y1="20" x2="6" y2="14" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num" style="color:#10b981;"><?= $stats['percentage'] ?? 0 ?>%</span>
            <span class="au-stat-lbl">Check-In Rate</span>
        </div>
    </div>
</section>

<!-- SECTION: Staff Check-In & Deployment Hub -->
<section class="form-card" style="margin: 1.5rem 0;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
            <h2 style="margin:0; font-size:1.15rem; font-weight:800; display:flex; align-items:center; gap:0.4rem; color:rgb(var(--text));">
                <span>👥</span> Staff Check-In &amp; Deployment Roster
            </h2>
            <p style="margin:0.2rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">
                Maintain and verify which team members are currently on duty and checked-in at the event
            </p>
        </div>
        <span style="font-size:0.8rem; font-weight:800; background:rgba(var(--brand),0.1); color:rgb(var(--brand)); padding:0.35rem 0.85rem; border-radius:12px;">
            <?= count($staffRoster) ?> Assigned Personnel
        </span>
    </div>

    <?php if (empty($staffRoster)): ?>
        <div style="text-align:center; padding:2rem 1rem; color:rgb(var(--muted)); background:rgba(var(--muted),0.04); border-radius:14px; border:1px dashed rgba(var(--muted),0.2);">
            <span style="font-size:2rem; display:block; margin-bottom:0.25rem;">👥</span>
            <p style="margin:0; font-weight:700;">No team members assigned to this event yet.</p>
        </div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:0.75rem;">
            <?php foreach ($staffRoster as $st): 
                $isChecked = !empty($st['checked_in']);
            ?>
                <div class="staff-roster-item" id="staff-row-<?= (int)$st['assignment_id'] ?>">
                    <div style="display:flex; align-items:center; gap:0.75rem; flex:1; min-width:0;">
                        <div style="width:38px; height:38px; border-radius:10px; background:linear-gradient(135deg, #6366f1, #a855f7); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1rem; flex-shrink:0;">
                            <?= strtoupper(substr($st['staff_name'], 0, 1)) ?>
                        </div>
                        <div style="min-width:0; flex:1;">
                            <strong style="font-size:0.92rem; color:rgb(var(--text)); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= e($st['staff_name']) ?></strong>
                            <span style="font-size:0.78rem; color:rgb(var(--muted)); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= e($st['staff_email']) ?></span>
                        </div>
                    </div>

                    <div class="staff-roster-actions" style="display:flex; align-items:center; gap:0.6rem; flex-wrap:wrap;">
                        <span style="font-size:0.75rem; font-weight:800; background:rgba(99,102,241,0.12); color:#6366f1; border:1px solid rgba(99,102,241,0.25); padding:0.25rem 0.65rem; border-radius:8px; white-space:nowrap;">
                            <?= e($st['role_name']) ?>
                        </span>

                        <span class="staff-checkin-badge" id="badge-checkin-<?= (int)$st['assignment_id'] ?>" style="font-size:0.75rem; font-weight:800; padding:0.25rem 0.75rem; border-radius:8px; white-space:nowrap; <?= $isChecked ? 'background:rgba(16,185,129,0.14); color:#059669; border:1px solid rgba(16,185,129,0.3);' : 'background:rgba(100,116,139,0.12); color:rgb(100,116,139);' ?>">
                            <?= $isChecked ? '● Checked In' . (!empty($st['checked_in_at']) ? ' (' . date('H:i', strtotime($st['checked_in_at'])) . ')' : '') : '○ Not Checked In' ?>
                        </span>

                        <button type="button" class="btn-toggle-checkin" data-assignment-id="<?= (int)$st['assignment_id'] ?>" data-current="<?= $isChecked ? '1' : '0' ?>" style="padding:0.4rem 0.9rem; border-radius:10px; font-size:0.8rem; font-weight:800; cursor:pointer; border:none; transition:all 0.15s ease; white-space:nowrap; <?= $isChecked ? 'background:rgba(239,68,68,0.12); color:#ef4444; border:1px solid rgba(239,68,68,0.25);' : 'background:#10b981; color:#fff;' ?>">
                            <?= $isChecked ? 'Check Out' : '✓ Check In Staff' ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Content Grid: Tier Attendance & Gate Activity -->
<div class="coord-content-grid">
    
    <!-- Ticket Type Capacity Breakdown -->
    <div class="form-card" style="margin-bottom:0;">
        <h2 style="margin:0 0 0.25rem; font-size:1.15rem; font-weight:700; color:rgb(var(--text));">Ticket Tier Attendance</h2>
        <p class="section-note" style="margin:0 0 1.25rem; font-size:0.82rem; color:rgb(var(--muted));">Live entry rates per ticket tier.</p>

        <div style="display:flex; flex-direction:column; gap:1.25rem;">
            <?php if (empty($ticket_types)): ?>
                <p style="color:rgb(var(--muted)); text-align:center; padding:1.5rem 0;">No ticket type data available.</p>
            <?php else: ?>
                <?php foreach ($ticket_types as $type):
                    $sold = (int)($type['tickets_sold'] ?? 0);
                    $checked = (int)($type['checked_in_count'] ?? 0);
                    $pct = $sold > 0 ? round(($checked / $sold) * 100, 1) : 0;
                ?>
                <div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.4rem; font-size:0.88rem; flex-wrap:wrap; gap:0.25rem;">
                        <strong style="color:rgb(var(--text));"><?= e($type['type_name']) ?></strong>
                        <span style="font-size:0.82rem; color:rgb(var(--muted));"><strong style="color:rgb(var(--text));"><?= $checked ?></strong> / <?= $sold ?> checked in (<?= $pct ?>%)</span>
                    </div>
                    <div style="width:100%; height:10px; background:rgba(var(--muted),0.15); border-radius:10px; overflow:hidden;">
                        <div style="width:<?= $pct ?>%; height:100%; background:linear-gradient(90deg, rgb(var(--brand)), #10b981); border-radius:10px; transition:width 0.5s ease;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Live Scan Stream / Recent Activity -->
    <div class="form-card" style="margin-bottom:0;">
        <h2 style="margin:0 0 0.25rem; font-size:1.15rem; font-weight:700; color:rgb(var(--text));">Recent Gate Activity Log</h2>
        <p class="section-note" style="margin:0 0 1.25rem; font-size:0.82rem; color:rgb(var(--muted));">Real-time stream of verified admission passes.</p>

        <div style="display:flex; flex-direction:column; gap:0.6rem; max-height:360px; overflow-y:auto;">
            <?php if (empty($recent_activity)): ?>
                <div style="text-align:center; padding:2rem 1rem; color:rgb(var(--muted)); background:rgba(var(--muted),0.04); border-radius:12px; border:1px dashed rgba(var(--muted),0.18);">
                    No check-in activity logged yet.
                </div>
            <?php else: ?>
                <?php foreach ($recent_activity as $act): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:0.65rem 0.85rem; border-radius:12px; background:rgb(var(--bg)); border:1px solid rgba(var(--muted),0.12); font-size:0.85rem; gap:0.5rem; flex-wrap:wrap;">
                    <div style="min-width:0; flex:1;">
                        <strong style="display:block; word-break:break-word; color:rgb(var(--text));"><?= e($act['attendee_name']) ?></strong>
                        <div style="font-size:0.75rem; color:rgb(var(--muted)); margin-top:0.15rem;">
                            <code><?= e($act['ticket_code']) ?></code> • <?= e($act['ticket_type']) ?>
                        </div>
                    </div>
                    <span style="font-size:0.75rem; color:#10b981; font-weight:700; background:rgba(16,185,129,0.12); padding:0.2rem 0.5rem; border-radius:6px; flex-shrink:0;">
                        <?= date('H:i:s', strtotime($act['checked_in_at'])) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════
     GATE BOX OFFICE & TICKET SALES MODAL (CRYSTAL CLEAR)
     ════════════════════════════════════════════════════════════════ -->
<div id="modal-box-office" class="team-modal-backdrop">
    <div class="team-modal-dialog">
        <div class="team-modal-header">
            <div>
                <h3 class="team-modal-title">
                    <span>🎟️</span> Door / Gate Box Office
                </h3>
                <p class="team-modal-subtitle">Issue tickets on the spot for walk-in attendees</p>
            </div>
            <button type="button" id="btn-close-box-office" class="team-modal-close-btn" aria-label="Close modal">✕</button>
        </div>

        <form id="form-gate-sale">
            <?= csrf_field() ?>
            
            <div style="margin-bottom:1rem;">
                <label class="team-modal-label">Ticket Tier *</label>
                <select name="ticket_type_id" id="gate-ticket-tier" required class="team-modal-select">
                    <?php foreach ($allTicketTypes as $tt): 
                        $cap = (int)($tt['capacity'] ?? 0);
                        $sold = (int)($tt['sold_count'] ?? 0);
                        $avail = $cap > 0 ? max(0, $cap - $sold) : 'Unlimited';
                    ?>
                        <option value="<?= $tt['id'] ?>" data-price="<?= (float)$tt['price'] ?>">
                            <?= e($tt['name']) ?> — <?= (float)$tt['price'] > 0 ? format_price($tt['price']) : 'Free' ?> (Avail: <?= $avail ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="gate-sale-grid-2">
                <div>
                    <label class="team-modal-label">Attendee Full Name *</label>
                    <input type="text" name="attendee_name" required placeholder="John Doe" class="team-modal-input" />
                </div>
                <div>
                    <label class="team-modal-label">Attendee Email</label>
                    <input type="email" name="attendee_email" placeholder="john@example.com" class="team-modal-input" />
                </div>
            </div>

            <div class="gate-sale-grid-2">
                <div>
                    <label class="team-modal-label">Quantity</label>
                    <input type="number" name="quantity" value="1" min="1" max="10" required class="team-modal-input" />
                </div>
                <div>
                    <label class="team-modal-label">Payment Method *</label>
                    <select name="payment_method" class="team-modal-select">
                        <option value="cash">💵 Cash at Gate</option>
                        <option value="pos">💳 POS Card Terminal</option>
                        <option value="transfer">📱 Bank Transfer / USSD</option>
                        <option value="comp">🎁 Complimentary / VIP Comp</option>
                    </select>
                </div>
            </div>

            <div class="team-modal-notice">
                <input type="checkbox" name="mark_checked_in" id="chk-gate-in" value="1" checked style="width:18px; height:18px; accent-color:rgb(var(--brand)); cursor:pointer;" />
                <label for="chk-gate-in" style="font-size:0.85rem; font-weight:700; color:rgb(var(--text)); cursor:pointer; margin:0;">
                    Check attendee into event immediately upon issue
                </label>
            </div>

            <div id="gate-sale-status" style="font-size:0.85rem; margin-bottom:0.75rem; display:none;"></div>

            <div style="display:flex; justify-content:flex-end; gap:0.6rem; margin-top:1.25rem;">
                <button type="button" id="btn-cancel-box-office" class="checkout-btn secondary" style="padding:0.75rem 1.25rem; border-radius:12px;">Cancel</button>
                <button type="submit" id="btn-submit-gate-sale" class="checkout-btn" style="padding:0.75rem 1.5rem; border-radius:12px; background:#10b981; color:#fff; font-weight:800; border:none; cursor:pointer;">
                    ✓ Issue Gate Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = "<?= csrf_token() ?>";

    // 1. Staff Check-In Toggle Logic
    const checkinBtns = document.querySelectorAll('.btn-toggle-checkin');
    checkinBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const asgId = this.getAttribute('data-assignment-id');
            const curState = this.getAttribute('data-current');
            this.disabled = true;
            this.textContent = 'Updating...';

            fetch('<?= route('/teams/toggle-staff-checkin') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ 
                    assignment_id: asgId,
                    csrf_token: csrfToken 
                })
            })
            .then(r => r.json())
            .then(data => {
                this.disabled = false;
                if (data.success) {
                    const isChecked = data.checked_in == 1;
                    this.setAttribute('data-current', isChecked ? '1' : '0');
                    this.textContent = isChecked ? 'Check Out' : '✓ Check In Staff';
                    this.style.background = isChecked ? 'rgba(239,68,68,0.12)' : '#10b981';
                    this.style.color = isChecked ? '#ef4444' : '#fff';
                    this.style.border = isChecked ? '1px solid rgba(239,68,68,0.25)' : 'none';

                    const badge = document.getElementById('badge-checkin-' + asgId);
                    if (badge) {
                        badge.textContent = isChecked ? ('● Checked In' + (data.checked_in_at ? ' (' + data.checked_in_at + ')' : '')) : '○ Not Checked In';
                        badge.style.background = isChecked ? 'rgba(16,185,129,0.14)' : 'rgba(100,116,139,0.12)';
                        badge.style.color = isChecked ? '#059669' : 'rgb(100,116,139)';
                        badge.style.border = isChecked ? '1px solid rgba(16,185,129,0.3)' : 'none';
                    }
                } else {
                    alert(data.message || 'Error updating staff check-in.');
                    this.textContent = curState == '1' ? 'Check Out' : '✓ Check In Staff';
                }
            })
            .catch(() => {
                this.disabled = false;
                this.textContent = curState == '1' ? 'Check Out' : '✓ Check In Staff';
                alert('Could not update staff check-in.');
            });
        });
    });

    // 2. Gate Box Office Modal Actions
    const modalBoxOffice = document.getElementById('modal-box-office');
    const btnOpen = document.getElementById('btn-open-box-office');
    const btnClose = document.getElementById('btn-close-box-office');
    const btnCancel = document.getElementById('btn-cancel-box-office');
    const formSale = document.getElementById('form-gate-sale');
    const saleStatus = document.getElementById('gate-sale-status');
    const btnSubmit = document.getElementById('btn-submit-gate-sale');

    if (btnOpen && modalBoxOffice) {
        btnOpen.addEventListener('click', () => { modalBoxOffice.style.display = 'flex'; });
        btnClose.addEventListener('click', () => { modalBoxOffice.style.display = 'none'; });
        btnCancel.addEventListener('click', () => { modalBoxOffice.style.display = 'none'; });
        modalBoxOffice.addEventListener('click', (e) => {
            if (e.target === modalBoxOffice) modalBoxOffice.style.display = 'none';
        });
    }

    if (formSale) {
        formSale.addEventListener('submit', function(e) {
            e.preventDefault();
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Issuing...';
            saleStatus.style.display = 'none';

            const formData = new FormData(formSale);
            const payload = Object.fromEntries(formData.entries());

            fetch('<?= route('/teams/issue-gate-ticket') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                btnSubmit.disabled = false;
                btnSubmit.textContent = '✓ Issue Gate Ticket';
                saleStatus.style.display = 'block';

                if (data.success) {
                    saleStatus.innerHTML = `<span style="color:#10b981; font-weight:800;">✓ ${data.message}</span>`;
                    setTimeout(() => {
                        modalBoxOffice.style.display = 'none';
                        window.location.reload();
                    }, 1500);
                } else {
                    saleStatus.innerHTML = `<span style="color:#ef4444; font-weight:800;">✕ ${data.message}</span>`;
                }
            })
            .catch(() => {
                btnSubmit.disabled = false;
                btnSubmit.textContent = '✓ Issue Gate Ticket';
                saleStatus.style.display = 'block';
                saleStatus.innerHTML = `<span style="color:#ef4444; font-weight:800;">✕ Connection error. Please try again.</span>`;
            });
        });
    }
});
</script>
@endsection