/*
========================================================
 Project: Evently
 File: host-ticket-verification.js
 Author: Celio Natti
 Version: 2.1.0
 Description: Handles host ticket verification with real-time camera scanning,
              Web Audio API chimes/buzzers, haptic feedback, group pass isolation,
              and seamless attendance logging.
========================================================
*/

(function () {
    'use strict';

    // DOM Elements
    var toggleScannerBtn = document.getElementById('toggle-scanner-btn');
    var toggleManualInputBtn = document.getElementById('toggle-manual-input-btn');
    var scannerView = document.getElementById('scanner-view');
    var manualInputView = document.getElementById('manual-input-view');
    var ticketCodeInput = document.getElementById('ticket-code-input');
    var verifyForm = document.getElementById('manual-verify-form');
    var scannerStatus = document.getElementById('scanner-status');
    var searchInput = document.getElementById('search-verified-tickets');
    var logList = document.getElementById('verified-tickets-list');
    var emptyLogMsg = document.getElementById('empty-log');
    var clearResultBtn = document.getElementById('clear-result-btn');
    var verificationResult = document.getElementById('verification-result');
    var undoCheckinBtn = document.getElementById('undo-checkin-btn');

    // Scanner state variables
    var html5QrcodeScanner = null;
    var isScannerActive = false;
    var isProcessing = false;
    var isScanLocked = false;
    var lastScannedCode = '';
    var lastScanTime = 0;
    var resumeTimer = null;
    var audioCtx = null;

    // ─── Web Audio API Synthesizer & Mobile Unlock ─────────

    function getAudioContext() {
        if (!audioCtx) {
            var AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) {
                audioCtx = new AudioContextClass();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    function unlockAudio() {
        var ctx = getAudioContext();
        if (ctx && ctx.state === 'suspended') {
            ctx.resume();
        }
    }
    document.addEventListener('touchstart', unlockAudio, { once: true, passive: true });
    document.addEventListener('click', unlockAudio, { once: true });

    function playScanSound(type) {
        try {
            var ctx = getAudioContext();
            if (!ctx) return;

            var now = ctx.currentTime;

            if (type === 'success') {
                // Melodic two-tone success chime (587.33 Hz [D5] -> 880 Hz [A5])
                var osc1 = ctx.createOscillator();
                var osc2 = ctx.createOscillator();
                var gain = ctx.createGain();

                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now);
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, now + 0.09);

                gain.gain.setValueAtTime(0.2, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.32);

                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(ctx.destination);

                osc1.start(now);
                osc1.stop(now + 0.09);
                osc2.start(now + 0.09);
                osc2.stop(now + 0.32);
            } else if (type === 'duplicate') {
                // Distinct 2-pulse warning buzzer (220 Hz -> 180 Hz)
                var osc = ctx.createOscillator();
                var gain = ctx.createGain();

                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(220, now);
                osc.frequency.setValueAtTime(180, now + 0.12);

                gain.gain.setValueAtTime(0.25, now);
                gain.gain.setValueAtTime(0.01, now + 0.09);
                gain.gain.setValueAtTime(0.25, now + 0.12);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.32);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + 0.32);
            } else {
                // Harsh rejection buzzer (140 Hz downward chirp)
                var osc = ctx.createOscillator();
                var gain = ctx.createGain();

                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(140, now);
                osc.frequency.exponentialRampToValueAtTime(85, now + 0.28);

                gain.gain.setValueAtTime(0.28, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.28);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + 0.28);
            }
        } catch (e) {
            console.warn('Audio feedback error:', e);
        }
    }

    // Trigger audio & haptic if page rendered with server-side result
    document.addEventListener('DOMContentLoaded', function () {
        if (verificationResult && !verificationResult.classList.contains('hidden')) {
            var status = verificationResult.getAttribute('data-status') || '';
            var type = verificationResult.getAttribute('data-type') || '';
            if (status === 'success') {
                playScanSound('success');
                if (navigator.vibrate) navigator.vibrate([120, 60, 120]);
            } else if (type === 'duplicate' || status === 'warning') {
                playScanSound('duplicate');
                if (navigator.vibrate) navigator.vibrate([250, 100, 250]);
            } else if (status === 'error' || type === 'invalid' || type === 'cancelled') {
                playScanSound('error');
                if (navigator.vibrate) navigator.vibrate([350]);
            }
        }
    });

    // ─── Camera Scanner Logic ───────────────────────────────

    function updateScannerStatus(message, type) {
        type = type || 'info';
        if (!scannerStatus) return;
        scannerStatus.textContent = message;
        scannerStatus.className = 'scanner-status scanner-' + type;
        scannerStatus.style.display = 'block';
    }

    function stopScanner() {
        if (resumeTimer) {
            clearTimeout(resumeTimer);
            resumeTimer = null;
        }
        if (!html5QrcodeScanner) return;
        var instance = html5QrcodeScanner;
        html5QrcodeScanner = null;

        try {
            if (instance.isScanning) {
                instance.stop().then(function () {
                    try { instance.clear(); } catch (e) { }
                }).catch(function (err) {
                    console.warn('Error stopping scanner:', err);
                    try { instance.clear(); } catch (e) { }
                });
            } else {
                try { instance.clear(); } catch (e) { }
            }
        } catch (e) {
            console.warn('Scanner stop exception:', e);
        }
    }

    function resumeScanner() {
        if (resumeTimer) {
            clearTimeout(resumeTimer);
            resumeTimer = null;
        }
        isScanLocked = false;
        isProcessing = false;

        if (html5QrcodeScanner && typeof html5QrcodeScanner.resume === 'function') {
            try {
                html5QrcodeScanner.resume();
            } catch (e) {}
        }
        updateScannerStatus('Point camera at attendee QR code', 'info');
    }

    function initializeScanner() {
        stopScanner();

        if (typeof Html5Qrcode === 'undefined') {
            updateScannerStatus('❌ QR Code scanner library not loaded. Please refresh the page.', 'error');
            return;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            updateScannerStatus('❌ Camera access requires HTTPS or localhost.', 'error');
            return;
        }

        try {
            html5QrcodeScanner = new Html5Qrcode('qr-scanner');
        } catch (e) {
            updateScannerStatus('❌ Failed to initialize scanner: ' + e.message, 'error');
            html5QrcodeScanner = null;
            return;
        }

        var onScanSuccess = function (decodedText) {
            var now = Date.now();
            var code = (decodedText || '').trim();
            if (!code) return;

            // Isolate group passes: prevent scanning next pass until previous scan is concluded
            if (isScanLocked || isProcessing) return;
            if (code === lastScannedCode && (now - lastScanTime) < 4000) return;

            isScanLocked = true;
            lastScannedCode = code;
            lastScanTime = now;
            unlockAudio();

            // Pause hardware stream during check-in to avoid accidental multi-scans
            if (html5QrcodeScanner && typeof html5QrcodeScanner.pause === 'function') {
                try {
                    html5QrcodeScanner.pause(true);
                } catch (e) {}
            }

            if (ticketCodeInput) {
                ticketCodeInput.value = code;
            }
            updateScannerStatus('Verifying pass ' + code + '...', 'info');

            // Perform seamless AJAX verification without full page refresh
            verifyTicketAjax(code);
        };

        var onScanError = function () {
            // Suppress frame scan failures
        };

        var config = {
            fps: 10,
            qrbox: function (viewfinderWidth, viewfinderHeight) {
                var minDim = Math.min(viewfinderWidth, viewfinderHeight);
                return {
                    width: Math.floor(minDim * 0.72),
                    height: Math.floor(minDim * 0.72)
                };
            },
            aspectRatio: 1.0
        };

        html5QrcodeScanner.start(
            { facingMode: 'environment' },
            config,
            onScanSuccess,
            onScanError
        ).catch(function (err) {
            var errMsg = (err && err.message) ? err.message : String(err);
            updateScannerStatus('❌ Camera access error: ' + errMsg, 'error');
            stopScanner();
        });
    }

    // ─── Seamless AJAX Check-In Handler ─────────────────────

    function verifyTicketAjax(code) {
        if (isProcessing) return;
        isProcessing = true;

        var eventIdInput = verifyForm ? verifyForm.querySelector('input[name="event_id"]') : null;
        var eventId = eventIdInput ? eventIdInput.value : '';
        var csrfToken = document.querySelector('meta[name="csrf-token"]') ?
            document.querySelector('meta[name="csrf-token"]').getAttribute('content') :
            (verifyForm && verifyForm.querySelector('input[name="csrf_token"]') ? verifyForm.querySelector('input[name="csrf_token"]').value : '');

        var postUrl = (verifyForm && verifyForm.getAttribute('action')) ? verifyForm.getAttribute('action') : '/host/event/ticket-verification';

        fetch(postUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: 'ticket_code=' + encodeURIComponent(code) +
                '&event_id=' + encodeURIComponent(eventId) +
                '&ajax=1' +
                '&csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            var isSuccess = !!data.success;
            var statusType = data.status || (isSuccess ? 'success' : 'error');
            var ticket = data.ticket || null;
            var attendeeName = ticket ? (ticket.attendee_name || 'Attendee') : 'Guest';
            var typeName = ticket ? (ticket.ticket_type || ticket.ticket_type_name || 'General Admission') : 'Pass';
            var displayCode = ticket ? (ticket.id || code) : code;
            var checkedTime = (ticket && ticket.checked_in_at) ?
                new Date(ticket.checked_in_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) :
                new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            // 1. Audio & Haptic Feedback
            if (isSuccess) {
                playScanSound('success');
                if (navigator.vibrate) navigator.vibrate([120, 60, 120]);
                updateScannerStatus('🟢 Admitted: ' + attendeeName + ' (' + displayCode + ')', 'success');
            } else if (statusType === 'duplicate') {
                playScanSound('duplicate');
                if (navigator.vibrate) navigator.vibrate([250, 100, 250]);
                updateScannerStatus('⚠️ Already Checked In: ' + attendeeName + ' (' + displayCode + ')', 'error');
            } else {
                playScanSound('error');
                if (navigator.vibrate) navigator.vibrate([350]);
                updateScannerStatus('❌ ' + (data.message || 'Check-in failed'), 'error');
            }

            // 2. Update Result Card DOM
            var successCard = document.getElementById('ticket-success-card');
            var errorCard = document.getElementById('ticket-error-card');

            if (verificationResult) {
                verificationResult.classList.remove('hidden');

                if (isSuccess) {
                    if (successCard) successCard.classList.remove('hidden');
                    if (errorCard) errorCard.classList.add('hidden');
                    if (undoCheckinBtn) undoCheckinBtn.classList.remove('hidden');

                    var nameEl = document.getElementById('ticket-success-attendee-name');
                    var typeEl = document.getElementById('ticket-success-ticket-type');
                    var codeEl = document.getElementById('ticket-success-code');
                    var barcodeEl = document.getElementById('ticket-success-barcode-code');
                    var timeEl = document.getElementById('ticket-success-time');
                    var avatarEl = document.getElementById('ticket-success-avatar');

                    if (nameEl) nameEl.textContent = attendeeName;
                    if (typeEl) typeEl.textContent = typeName + ' Ticket';
                    if (codeEl) codeEl.textContent = displayCode;
                    if (barcodeEl) barcodeEl.textContent = displayCode;
                    if (timeEl) timeEl.textContent = checkedTime;
                    if (avatarEl) avatarEl.textContent = attendeeName.substring(0, 2).toUpperCase();

                    // Prepend to Checked-In Attendee Log
                    if (logList) {
                        var existingItem = logList.querySelector('[data-code="' + displayCode + '"]');
                        if (!existingItem) {
                            var newRow = '<article class="verified-ticket-item" data-code="' + escapeHtml(displayCode) + '" id="verified-item-' + escapeHtml(displayCode) + '">' +
                                '<div class="ticket-info" style="min-width: 0; flex: 1;">' +
                                '<h4 style="margin: 0 0 2px; font-size: 0.92rem; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: rgb(var(--text));">' + escapeHtml(attendeeName) + '</h4>' +
                                '<p class="ticket-meta" style="margin: 0; font-size: 0.8rem; color: rgb(var(--muted));">' +
                                'Code: <strong style="font-family: monospace; color: rgb(var(--text));">' + escapeHtml(displayCode) + '</strong> &bull; ' + escapeHtml(typeName) +
                                '</p>' +
                                '<p class="ticket-time" style="margin: 2px 0 0; font-size: 0.76rem; color: #10b981; font-weight: 700;">' +
                                '✓ Checked in at ' + checkedTime +
                                '</p></div>' +
                                '<button class="btn-icon remove-checkin-btn" aria-label="Undo check-in status" title="Undo check-in" onclick="removeVerifiedTicket(\'' + escapeHtml(displayCode) + '\')">✕</button>' +
                                '</article>';
                            logList.insertAdjacentHTML('afterbegin', newRow);
                        }
                    }
                    if (emptyLogMsg) emptyLogMsg.classList.add('hidden');

                    // Update Counters
                    var checkinCountEl = document.getElementById('verified-tickets-count');
                    var remainingCountEl = document.getElementById('remaining-tickets-count');
                    var tabBadge = document.getElementById('tv-tab-log-badge');
                    var logCountDisplay = document.getElementById('log-count-display');

                    if (checkinCountEl) {
                        var curr = parseInt(checkinCountEl.textContent) || 0;
                        checkinCountEl.textContent = curr + 1;
                        if (tabBadge) tabBadge.textContent = curr + 1;
                        if (logCountDisplay) logCountDisplay.textContent = curr + 1;
                    }
                    if (remainingCountEl) {
                        var rem = parseInt(remainingCountEl.textContent) || 0;
                        remainingCountEl.textContent = Math.max(0, rem - 1);
                    }
                } else {
                    if (successCard) successCard.classList.add('hidden');
                    if (errorCard) errorCard.classList.remove('hidden');
                    if (undoCheckinBtn) undoCheckinBtn.classList.add('hidden');

                    var titleEl = document.getElementById('ticket-error-title');
                    var badgeEl = document.getElementById('ticket-error-badge');
                    var codeValEl = document.getElementById('ticket-error-code-val');

                    if (titleEl) titleEl.textContent = data.message || 'Check-in failed';
                    if (badgeEl) badgeEl.textContent = statusType.toUpperCase();
                    if (codeValEl) codeValEl.textContent = displayCode;
                }
            }

            // Automatically resume scanner after 2.8 seconds
            resumeTimer = setTimeout(function () {
                resumeScanner();
            }, 2800);
        })
        .catch(function (err) {
            console.error('Check-in error:', err);
            updateScannerStatus('❌ Network error. Retrying...', 'error');
            resumeTimer = setTimeout(function () {
                resumeScanner();
            }, 2000);
        });
    }

    // Bind scanner toggles
    if (toggleScannerBtn) {
        toggleScannerBtn.addEventListener('click', function () {
            isScannerActive = true;
            unlockAudio();
            if (scannerView) scannerView.classList.remove('hidden');
            if (manualInputView) manualInputView.classList.add('hidden');
            toggleScannerBtn.classList.add('hidden');
            if (toggleManualInputBtn) toggleManualInputBtn.classList.remove('hidden');

            updateScannerStatus('📷 Starting camera...', 'info');
            setTimeout(initializeScanner, 100);
        });
    }

    if (toggleManualInputBtn) {
        toggleManualInputBtn.addEventListener('click', function () {
            isScannerActive = false;
            stopScanner();
            if (scannerView) scannerView.classList.add('hidden');
            if (manualInputView) manualInputView.classList.remove('hidden');
            if (toggleScannerBtn) toggleScannerBtn.classList.remove('hidden');
            toggleManualInputBtn.classList.add('hidden');
            if (ticketCodeInput) {
                setTimeout(function () {
                    ticketCodeInput.focus();
                }, 50);
            }
        });
    }

    // Clear Result display block
    if (clearResultBtn) {
        clearResultBtn.addEventListener('click', function () {
            if (verificationResult) {
                verificationResult.classList.add('hidden');
            }
            if (ticketCodeInput) {
                ticketCodeInput.focus();
            }
            if (isScannerActive) {
                resumeScanner();
            }
        });
    }

    // ─── Checked In Log Filter ─────────────────────────────

    if (searchInput && logList) {
        searchInput.addEventListener('input', function (e) {
            var query = (e.target.value || '').toLowerCase().trim();
            var items = logList.querySelectorAll('.verified-ticket-item');
            var visibleCount = 0;

            items.forEach(function (item) {
                var attendeeName = (item.querySelector('h4') ? item.querySelector('h4').textContent : '').toLowerCase();
                var ticketCode = (item.querySelector('strong') ? item.querySelector('strong').textContent : '').toLowerCase();

                if (attendeeName.indexOf(query) !== -1 || ticketCode.indexOf(query) !== -1) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (emptyLogMsg) {
                if (items.length > 0 && visibleCount === 0) {
                    emptyLogMsg.classList.remove('hidden');
                } else {
                    emptyLogMsg.classList.add('hidden');
                }
            }
        });
    }

    // ─── Checked In Ticket Removal (Undo Check-In) ─────────
    window.removeVerifiedTicket = function (code) {
        if (!code) return;

        if (!confirm('Are you sure you want to undo check-in for ticket ' + code + '? The attendee will be marked as awaiting entry.')) {
            return;
        }

        var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

        // Find button to show loading state
        var itemToRemove = logList ? logList.querySelector('[data-code="' + code + '"]') : null;
        var btn = itemToRemove ? itemToRemove.querySelector('.remove-checkin-btn') : null;
        if (btn) {
            btn.disabled = true;
            btn.textContent = '...';
        }

        fetch('/host/event/ticket/' + encodeURIComponent(code) + '/action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ action: 'uncheck', csrf_token: csrfToken })
        })
        .then(function (res) {
            return res.json().then(function (data) {
                if (!res.ok) {
                    throw new Error(data.message || 'Failed to undo check-in.');
                }
                return data;
            });
        })
        .then(function (data) {
            if (itemToRemove) {
                itemToRemove.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                itemToRemove.style.opacity = '0';
                itemToRemove.style.transform = 'translateX(20px)';
                setTimeout(function () {
                    itemToRemove.remove();

                    // If list is now empty, toggle empty log label
                    var remainingItems = logList ? logList.querySelectorAll('.verified-ticket-item') : [];
                    if (remainingItems.length === 0 && emptyLogMsg) {
                        emptyLogMsg.classList.remove('hidden');
                    }
                }, 250);
            }

            // Update check-in counts in DOM header metrics
            var checkinCountEl = document.getElementById('verified-tickets-count');
            var remainingCountEl = document.getElementById('remaining-tickets-count');
            if (checkinCountEl && remainingCountEl) {
                var currentChecked = parseInt(checkinCountEl.textContent) || 0;
                var currentRemaining = parseInt(remainingCountEl.textContent) || 0;

                if (currentChecked > 0) {
                    checkinCountEl.textContent = currentChecked - 1;
                    remainingCountEl.textContent = currentRemaining + 1;
                    var tabBadge = document.getElementById('tv-tab-log-badge');
                    var logCountDisplay = document.getElementById('log-count-display');
                    if (tabBadge) tabBadge.textContent = Math.max(0, currentChecked - 1);
                    if (logCountDisplay) logCountDisplay.textContent = Math.max(0, currentChecked - 1);
                }
            }

            // If success card matches this code, update it
            var successCodeEl = document.getElementById('ticket-success-code');
            if (successCodeEl && successCodeEl.textContent.trim() === code) {
                var badge = document.querySelector('.ticket-status-badge.badge-verified');
                if (badge) {
                    badge.textContent = '↩️ Check-in Undone';
                    badge.style.background = 'rgba(245, 158, 11, 0.15)';
                    badge.style.color = '#d97706';
                }
            }

            alert(data.message || 'Check-in undone successfully.');
        })
        .catch(function (err) {
            alert('Error: ' + err.message);
            if (btn) {
                btn.disabled = false;
                btn.textContent = '✕';
            }
        });
    };

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

})();
