/*
========================================================
 Project: Evently
 File: admin-payouts.js
 Author: Celio Natti
 Version: 1.0.0
 Created: 2026-07-02
 Description: Client-side JavaScript for admin payouts management.
========================================================
*/

/**
 * Admin Payouts — UI interaction script.
 * 
 * In your PHP template, host bank accounts, pending balances, metrics, 
 * and the payouts queue table are all rendered server-side.
 * 
 * This script handles:
 * 1. Process Payout confirmation modal overlay.
 * 2. Flag Account confirmation modal overlay.
 * 3. Resolve Hold (Unflag) confirmation modal overlay.
 * 4. Client-side instant search filtering of table rows & mobile cards.
 */
(function() {
  'use strict';

  // ─── Modal Elements ──────────────────────────────────────
  var processModal = document.getElementById('process-modal');
  var processPayoutAmount = document.getElementById('process-payout-amount');
  var processHostName = document.getElementById('process-host-name');
  var processPayoutId = document.getElementById('process-payout-id');
  var processCancelBtn = document.getElementById('process-cancel-btn');
  var processSubmitBtn = document.getElementById('process-submit-btn');

  // Escrow warning elements
  var processEscrowWarning = document.getElementById('process-escrow-warning');
  var processEventTitle = document.getElementById('process-event-title');

  // Fee policy element
  var processFeeDesc = document.getElementById('process-fee-desc');

  // Manual bank details panel elements
  var manualBankDetails = document.getElementById('manual-bank-details');
  var manualBankName = document.getElementById('manual-bank-name');
  var manualAccountNumber = document.getElementById('manual-account-number');
  var manualRoutingNumber = document.getElementById('manual-routing-number');
  var manualPayoutAmount = document.getElementById('manual-payout-amount');

  var flagModal = document.getElementById('flag-modal');
  var flagHostName = document.getElementById('flag-host-name');
  var flagPayoutId = document.getElementById('flag-payout-id');
  var flagCancelBtn = document.getElementById('flag-cancel-btn');

  var unflagModal = document.getElementById('unflag-modal');
  var unflagHostName = document.getElementById('unflag-host-name');
  var unflagPayoutId = document.getElementById('unflag-payout-id');
  var unflagCancelBtn = document.getElementById('unflag-cancel-btn');

  // Store current payout bank details for manual panel
  var currentPayoutBank = '';
  var currentPayoutAccount = '';
  var currentPayoutRouting = '';
  var currentPayoutAmountVal = '';

  // Toggle manual bank details panel based on radio selection
  function updateManualPanel() {
    if (!processModal) return;
    var selectedMode = processModal.querySelector('input[name="mode"]:checked');
    var isManual = selectedMode && selectedMode.value === 'manual';

    if (manualBankDetails) {
      manualBankDetails.style.display = isManual ? 'block' : 'none';
    }
    if (processSubmitBtn) {
      processSubmitBtn.textContent = isManual ? 'Confirm Transfer Complete' : 'Confirm & Disburse';
    }

    if (isManual) {
      if (manualBankName) manualBankName.textContent = currentPayoutBank || '—';
      if (manualAccountNumber) manualAccountNumber.textContent = currentPayoutAccount || '—';
      if (manualRoutingNumber) manualRoutingNumber.textContent = currentPayoutRouting || '—';
      if (manualPayoutAmount) manualPayoutAmount.textContent = currentPayoutAmountVal || '—';
    }
  }

  // Listen for radio changes inside process modal
  if (processModal) {
    processModal.addEventListener('change', function(e) {
      if (e.target.name === 'mode') {
        updateManualPanel();
      }
    });
  }

  // Revenue breakdown elements
  var processRevenueBreakdown = document.getElementById('process-revenue-breakdown');
  var processTicketsSold = document.getElementById('process-tickets-sold');
  var processGrossSales = document.getElementById('process-gross-sales');
  var processNetSales = document.getElementById('process-net-sales');
  var processPriorDisbursed = document.getElementById('process-prior-disbursed');
  var processThisAmount = document.getElementById('process-this-amount');
  var processRemaining = document.getElementById('process-remaining');
  var processPartialBadge = document.getElementById('process-partial-badge');

  function formatMoney(amount) {
    var num = parseFloat(amount) || 0;
    return '₦' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Delegate action button clicks
  document.addEventListener('click', function(e) {
    var processBtn = e.target.closest('.au-process-btn');
    if (processBtn) {
      e.preventDefault();
      var payoutId = processBtn.getAttribute('data-payout-id') || '';
      var hostName = processBtn.getAttribute('data-host-name') || 'this host';
      var amountVal = processBtn.getAttribute('data-amount') || '₦0.00';
      var payoutType = processBtn.getAttribute('data-payout-type') || 'completed';
      var passFee = processBtn.getAttribute('data-pass-fee') || '1';
      var eventName = processBtn.getAttribute('data-event-name') || 'Upcoming Event';
      
      var ticketsSold = processBtn.getAttribute('data-event-tickets') || '0';
      var grossSales = parseFloat(processBtn.getAttribute('data-event-gross')) || 0;
      var netSales = parseFloat(processBtn.getAttribute('data-event-net')) || 0;
      var priorDisbursed = parseFloat(processBtn.getAttribute('data-event-prior')) || 0;
      var remainingBal = parseFloat(processBtn.getAttribute('data-event-remaining')) || 0;
      var isPartial = processBtn.getAttribute('data-is-partial') === '1';

      // Store bank details for manual panel
      currentPayoutBank = processBtn.getAttribute('data-bank') || 'Not Configured';
      currentPayoutAccount = processBtn.getAttribute('data-account') || 'Not Configured';
      currentPayoutRouting = processBtn.getAttribute('data-routing') || 'N/A';
      currentPayoutAmountVal = amountVal;

      if (processPayoutAmount) processPayoutAmount.textContent = amountVal;
      if (processHostName) processHostName.textContent = hostName;
      if (processPayoutId) processPayoutId.value = payoutId;

      // Populate Revenue Breakdown
      if (processRevenueBreakdown) {
        processRevenueBreakdown.style.display = 'block';
        if (processTicketsSold) processTicketsSold.textContent = ticketsSold + ' tickets';
        if (processGrossSales) processGrossSales.textContent = formatMoney(grossSales);
        if (processNetSales) processNetSales.textContent = formatMoney(netSales);
        if (processPriorDisbursed) processPriorDisbursed.textContent = formatMoney(priorDisbursed);
        if (processThisAmount) processThisAmount.textContent = amountVal;
        if (processRemaining) processRemaining.textContent = formatMoney(remainingBal);
        if (processPartialBadge) processPartialBadge.style.display = isPartial ? 'block' : 'none';
      }

      // Fee description display
      if (processFeeDesc) {
        if (passFee === '0') {
          processFeeDesc.innerHTML = '🏷️ <strong>Host Absorbed Fee:</strong> The <strong>5% platform fee was already deducted</strong> on ticket sales. This amount (' + amountVal + ') is the <strong>exact 95% net share</strong> to disburse.';
        } else {
          processFeeDesc.innerHTML = '🎟️ <strong>Buyer Paid Fee:</strong> Attendee paid ticketing fees at checkout. This amount (' + amountVal + ') is the <strong>100% full ticket face-value earnings</strong> to disburse.';
        }
      }

      // Handle escrow warning display
      if (processEscrowWarning) {
        if (payoutType === 'escrow') {
          processEscrowWarning.style.display = 'block';
          if (processEventTitle) processEventTitle.textContent = eventName;
        } else {
          processEscrowWarning.style.display = 'none';
        }
      }

      // Reset to default radio (paystack) and hide manual panel
      var defaultRadio = processModal ? processModal.querySelector('input[name="mode"][value="paystack"]') : null;
      if (defaultRadio) defaultRadio.checked = true;
      if (manualBankDetails) manualBankDetails.style.display = 'none';
      if (processSubmitBtn) processSubmitBtn.textContent = 'Confirm & Disburse';

      if (processModal) processModal.classList.remove('hidden');
      return;
    }

    var flagBtn = e.target.closest('.au-flag-btn');
    if (flagBtn) {
      e.preventDefault();
      var fPayoutId = flagBtn.getAttribute('data-payout-id') || '';
      var fHostName = flagBtn.getAttribute('data-host-name') || 'this host';
      
      if (flagHostName) flagHostName.textContent = fHostName;
      if (flagPayoutId) flagPayoutId.value = fPayoutId;
      if (flagModal) flagModal.classList.remove('hidden');
      return;
    }

    var unflagBtn = e.target.closest('.au-unflag-btn');
    if (unflagBtn) {
      e.preventDefault();
      var uPayoutId = unflagBtn.getAttribute('data-payout-id') || '';
      var uHostName = unflagBtn.getAttribute('data-host-name') || 'this host';

      if (unflagHostName) unflagHostName.textContent = uHostName;
      if (unflagPayoutId) unflagPayoutId.value = uPayoutId;
      if (unflagModal) unflagModal.classList.remove('hidden');
      return;
    }
  });

  // Cancel buttons
  if (processCancelBtn) {
    processCancelBtn.addEventListener('click', function() {
      if (processModal) processModal.classList.add('hidden');
    });
  }
  if (flagCancelBtn) {
    flagCancelBtn.addEventListener('click', function() {
      if (flagModal) flagModal.classList.add('hidden');
    });
  }
  if (unflagCancelBtn) {
    unflagCancelBtn.addEventListener('click', function() {
      if (unflagModal) unflagModal.classList.add('hidden');
    });
  }

  // Close modals on overlay clicks
  [processModal, flagModal, unflagModal].forEach(function(modal) {
    if (!modal) return;
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        modal.classList.add('hidden');
      }
    });
  });

  // Close modals on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      [processModal, flagModal, unflagModal].forEach(function(modal) {
        if (modal && !modal.classList.contains('hidden')) {
          modal.classList.add('hidden');
        }
      });
    }
  });

  // ─── Client-Side Instant Search ─────────────────────────
  var searchInput = document.getElementById('payouts-search');
  var tableBody = document.getElementById('payouts-tbody');
  var cardsList = document.getElementById('payouts-cards');
  var emptyState = document.getElementById('payouts-empty');

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      var query = (searchInput.value || '').toLowerCase().trim();

      // Filter table rows
      if (tableBody) {
        var rows = tableBody.querySelectorAll('tr');
        var visibleRows = 0;

        rows.forEach(function(row) {
          // If empty state row, skip
          if (row.querySelector('td[colspan]')) return;
          var rowText = (row.textContent || '').toLowerCase();
          var match = rowText.indexOf(query) !== -1;

          row.style.display = match ? '' : 'none';
          if (match) visibleRows++;
        });

        if (emptyState) {
          emptyState.classList.toggle('hidden', visibleRows > 0 || query === '');
        }
      }

      // Filter mobile cards
      if (cardsList) {
        var cards = cardsList.querySelectorAll('.au-user-card');
        cards.forEach(function(card) {
          var cardText = (card.textContent || '').toLowerCase();
          var match = cardText.indexOf(query) !== -1;
          card.style.display = match ? '' : 'none';
        });
      }
    });
  }

})();