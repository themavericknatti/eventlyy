/*
========================================================
 Project: Evently
 File: host-ticket-verification.js
 Author: Celio Natti
 Version: 1.0.0
 Created: 2026-06-19
 Description: Handles the ticket verification page for hosts, including UI interactions for verification actions.
========================================================
*/

/**
 * Host Ticket Verification — UI & Scanner interaction script.
 * 
 * In your PHP template, the verification logic, checked-in log list, 
 * and metrics stats are rendered server-side.
 * 
 * This script handles:
 * 1. Initializing and managing the device camera scanner (via html5-qrcode library).
 * 2. Autocomplete scan input values and submitting the verification form.
 * 3. Client-side search filtering of the checked-in log list.
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

    // Scanner state variables
    var html5QrcodeScanner = null;
    var isScannerActive = false;

    // ─── Camera Scanner Logic ───────────────────────────────

    function updateScannerStatus(message, type) {
        type = type || 'info';
        if (!scannerStatus) return;
        scannerStatus.textContent = message;
        scannerStatus.className = 'scanner-status scanner-' + type;
        scannerStatus.style.display = 'block';
    }

    function stopScanner() {
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
            var code = (decodedText || '').trim();
            if (code) {
                if (ticketCodeInput) {
                    ticketCodeInput.value = code;
                }
                updateScannerStatus('🟢 Code Scanned: ' + code + '. Verifying...', 'success');

                if (verifyForm) {
                    setTimeout(function () {
                        verifyForm.submit();
                    }, 400);
                }
            }
        };

        var onScanError = function () {
            // Suppress console spam during real-time scan frames
        };

        var config = {
            fps: 10,
            qrbox: function (viewfinderWidth, viewfinderHeight) {
                var minDim = Math.min(viewfinderWidth, viewfinderHeight);
                return {
                    width: Math.floor(minDim * 0.7),
                    height: Math.floor(minDim * 0.7)
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

    // Bind scanner toggles
    if (toggleScannerBtn) {
        toggleScannerBtn.addEventListener('click', function () {
            isScannerActive = true;
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

})();
