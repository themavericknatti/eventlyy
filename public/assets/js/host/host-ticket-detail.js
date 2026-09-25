/*
========================================================
 Project: Evently
 File: host-ticket-detail.js
 Author: Celio Natti
 Version: 1.0.0
 Created: 2026-06-19
 Description: Handles the ticket detail page for hosts, including UI interactions for ticket actions (resend, refund, flag).
========================================================
*/

/**
 * Host Ticket Detail — UI interactions only.
 * All ticket data is rendered server-side by PHP.
 * This script handles: toast notifications, confirmation modals,
 * and wiring the action buttons (resend / refund / flag).
 *
 * In your PHP integration, update the action URLs in the
 * data-action-url attributes or the fetch endpoints below.
 */
(function () {
    'use strict';

    // ── Toast notifications ──────────────────────────────
    function showToast(message, type, timeout) {
        type = type || 'info';
        timeout = timeout || 3000;
        var tc = document.getElementById('toast-container');
        if (!tc) return;
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.textContent = message;
        tc.appendChild(t);
        setTimeout(function () { t.classList.add('visible'); }, 10);
        setTimeout(function () {
            t.classList.remove('visible');
            setTimeout(function () { t.remove(); }, 300);
        }, timeout);
    }

    // ── Confirmation modal ───────────────────────────────
    var backdrop = document.getElementById('modal-backdrop');
    var confirmMsg = document.getElementById('confirm-message');
    var confirmYes = document.getElementById('confirm-yes');
    var confirmNo = document.getElementById('confirm-no');
    var pendingAction = null;

    function openConfirm(message, onConfirm) {
        if (!backdrop) return onConfirm();
        confirmMsg.textContent = message;
        pendingAction = onConfirm;
        backdrop.classList.remove('hidden');
    }

    function closeModal() {
        if (backdrop) backdrop.classList.add('hidden');
        pendingAction = null;
    }

    if (confirmYes) {
        confirmYes.addEventListener('click', function () {
            var actionToRun = pendingAction;
            closeModal();
            if (typeof actionToRun === 'function') {
                actionToRun();
            }
        });
    }
    if (confirmNo) {
        confirmNo.addEventListener('click', closeModal);
    }
    // Close modal on backdrop click
    if (backdrop) {
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) closeModal();
        });
    }

    // ── Action buttons ───────────────────────────────────
    var resendBtn = document.getElementById('action-resend');
    var refundBtn = document.getElementById('action-refund');
    var flagBtn = document.getElementById('action-flag');

    function performAction(btn, actionName, successCallback) {
        var actionUrl = btn.getAttribute('data-action-url');
        if (!actionUrl) return;

        var csrfToken = btn.getAttribute('data-csrf-token') || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');

        btn.disabled = true;
        fetch(actionUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ action: actionName, csrf_token: csrfToken })
        })
        .then(function (res) {
            return res.json().then(function (data) {
                if (!res.ok) {
                    throw new Error(data.message || 'Action failed.');
                }
                return data;
            });
        })
        .then(function (data) {
            btn.disabled = false;
            if (data.success) {
                successCallback(data);
            } else {
                showToast(data.message || 'Action failed.', 'error');
            }
        })
        .catch(function (err) {
            btn.disabled = false;
            console.error(err);
            showToast(err.message || 'An error occurred.', 'error');
        });
    }

    var checkinBtn = document.getElementById('action-checkin');
    var uncheckBtn = document.getElementById('action-uncheck');

    function wireCheckinUncheck() {
        var currentCheckinBtn = document.getElementById('action-checkin');
        var currentUncheckBtn = document.getElementById('action-uncheck');

        if (currentCheckinBtn) {
            currentCheckinBtn.onclick = function (e) {
                e.preventDefault();
                performAction(currentCheckinBtn, 'check_in', function (data) {
                    var checkinStatusEl = document.getElementById('checkin-status');
                    if (checkinStatusEl) {
                        checkinStatusEl.className = 'status-badge confirmed';
                        checkinStatusEl.style.background = 'rgba(16, 185, 129, 0.12)';
                        checkinStatusEl.style.color = '#10b981';
                        checkinStatusEl.textContent = '✓ Checked In (' + (data.formatted_time || 'Just now') + ')';
                    }
                    // Update refund button state
                    var currentRefundBtn = document.getElementById('action-refund');
                    if (currentRefundBtn) {
                        currentRefundBtn.disabled = true;
                        currentRefundBtn.style.opacity = '0.5';
                        currentRefundBtn.style.cursor = 'not-allowed';
                        currentRefundBtn.title = 'Cannot refund a ticket that has already been checked in (uncheck first if needed)';
                    }

                    // Transform button to uncheck
                    currentCheckinBtn.id = 'action-uncheck';
                    currentCheckinBtn.setAttribute('data-action', 'uncheck');
                    currentCheckinBtn.style.background = 'rgba(245, 158, 11, 0.12)';
                    currentCheckinBtn.style.color = '#d97706';
                    currentCheckinBtn.style.borderColor = 'rgba(245, 158, 11, 0.3)';
                    currentCheckinBtn.innerHTML = '<span>↩️</span> Uncheck Ticket';
                    wireCheckinUncheck();
                    showToast(data.message || 'Ticket checked in successfully.', 'success');
                });
            };
        }

        if (currentUncheckBtn) {
            currentUncheckBtn.onclick = function (e) {
                e.preventDefault();
                openConfirm('Undo check-in for this ticket? Attendee will be marked as awaiting entry.', function () {
                    performAction(currentUncheckBtn, 'uncheck', function (data) {
                        var checkinStatusEl = document.getElementById('checkin-status');
                        if (checkinStatusEl) {
                            checkinStatusEl.className = 'status-badge pending';
                            checkinStatusEl.style.background = '';
                            checkinStatusEl.style.color = '';
                            checkinStatusEl.textContent = '⏳ Awaiting Entry';
                        }

                        // Restore refund button state
                        var currentRefundBtn = document.getElementById('action-refund');
                        if (currentRefundBtn) {
                            currentRefundBtn.disabled = false;
                            currentRefundBtn.style.opacity = '1';
                            currentRefundBtn.style.cursor = 'pointer';
                            currentRefundBtn.title = '';
                        }

                        // Transform button to checkin
                        currentUncheckBtn.id = 'action-checkin';
                        currentUncheckBtn.setAttribute('data-action', 'check_in');
                        currentUncheckBtn.style.background = 'rgba(16, 185, 129, 0.12)';
                        currentUncheckBtn.style.color = '#10b981';
                        currentUncheckBtn.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                        currentUncheckBtn.innerHTML = '<span>🎟️</span> Check In Ticket';
                        wireCheckinUncheck();
                        showToast(data.message || 'Check-in undone successfully.', 'info');
                    });
                });
            };
        }
    }

    wireCheckinUncheck();

    if (resendBtn) {
        resendBtn.addEventListener('click', function (e) {
            e.preventDefault();
            performAction(resendBtn, 'resend', function (data) {
                showToast(data.message || 'Ticket resent successfully.', 'success');
            });
        });
    }

    if (flagBtn) {
        flagBtn.addEventListener('click', function (e) {
            e.preventDefault();
            performAction(flagBtn, 'flag', function (data) {
                var detailEl = document.getElementById('status-detail');
                if (detailEl && data.status_detail) {
                    detailEl.textContent = data.status_detail;
                }
                showToast(data.message || 'Ticket flagged for review.', 'info');
            });
        });
    }

    if (refundBtn) {
        refundBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openConfirm('Refund this ticket? This action cannot be undone.', function () {
                performAction(refundBtn, 'refund', function (data) {
                    // Update status badge visually after refund
                    var statusEl = document.getElementById('ticket-status');
                    if (statusEl) {
                        statusEl.className = 'status-badge cancelled';
                        statusEl.textContent = 'CANCELLED';
                    }
                    var detailEl = document.getElementById('status-detail');
                    if (detailEl && data.status_detail) {
                        detailEl.textContent = data.status_detail;
                    }
                    showToast(data.message || 'Ticket refunded successfully.', 'success');
                });
            });
        });
    }
})();