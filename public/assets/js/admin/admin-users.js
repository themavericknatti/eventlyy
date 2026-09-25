/*
========================================================
 Project: Evently
 File: admin-user.js
 Author: Celio Natti
 Version: 1.0.0
 Created: 2026-07-02
 Description: Client-side JavaScript for admin user management.
========================================================
*/

/**
 * Admin Users — UI interaction script.
 *
 * In your PHP template, the user list, stats, pagination, and filtering
 * are all rendered server-side. This script only handles:
 *
 * 1. Suspend / Reactivate confirmation modals.
 * 2. Client-side instant search filtering of table rows & mobile cards.
 */
(function () {
  'use strict';

  // ─── Modal Logic ────────────────────────────────────────

  var suspendModal = document.getElementById('suspend-modal');
  var suspendUserName = document.getElementById('suspend-user-name');
  var suspendUserId = document.getElementById('suspend-user-id');
  var suspendCancelBtn = document.getElementById('suspend-cancel-btn');

  var reactivateModal = document.getElementById('reactivate-modal');
  var reactivateUserName = document.getElementById('reactivate-user-name');
  var reactivateUserId = document.getElementById('reactivate-user-id');
  var reactivateCancelBtn = document.getElementById('reactivate-cancel-btn');

  var verifyModal = document.getElementById('verify-modal');
  var verifyUserName = document.getElementById('verify-user-name');
  var verifyUserId = document.getElementById('verify-user-id');
  var verifyCancelBtn = document.getElementById('verify-cancel-btn');

  var resendModal = document.getElementById('resend-modal');
  var resendUserName = document.getElementById('resend-user-name');
  var resendUserId = document.getElementById('resend-user-id');
  var resendCancelBtn = document.getElementById('resend-cancel-btn');

  var viewModal = document.getElementById('view-modal');
  var viewModalLoading = document.getElementById('view-modal-loading');
  var viewModalContent = document.getElementById('view-modal-content');
  var viewCloseBtn = document.getElementById('view-close-btn');
  var viewCloseXBtn = document.getElementById('view-close-x-btn');

  // Elements inside details modal
  var vdName = document.getElementById('vd-name');
  var vdEmail = document.getElementById('vd-email');
  var vdPhone = document.getElementById('vd-phone');
  var vdJoined = document.getElementById('vd-joined');
  var vdStatus = document.getElementById('vd-status');
  var vdRole = document.getElementById('vd-role');

  var vdVerificationBadge = document.getElementById('vd-verification-badge');
  var vdVerificationDate = document.getElementById('vd-verification-date');
  var vdVerificationWarning = document.getElementById('vd-verification-warning');
  var vdVerificationActions = document.getElementById('vd-verification-actions');
  var vdModalVerifyBtn = document.querySelector('.au-modal-verify-btn');
  var vdModalResendBtn = document.querySelector('.au-modal-resend-btn');

  var vdSectionAttendee = document.getElementById('vd-section-attendee');
  var vdTicketsBought = document.getElementById('vd-tickets-bought');
  var vdTotalSpent = document.getElementById('vd-total-spent');
  var vdLastPurchase = document.getElementById('vd-last-purchase');

  var vdSectionHost = document.getElementById('vd-section-host');
  var vdBio = document.getElementById('vd-bio');
  var vdTotalEvents = document.getElementById('vd-total-events');
  var vdTicketsSold = document.getElementById('vd-tickets-sold');
  var vdTotalRevenue = document.getElementById('vd-total-revenue');
  var vdAvailableBalance = document.getElementById('vd-available-balance');
  var vdBank = document.getElementById('vd-bank');
  var vdAccount = document.getElementById('vd-account');
  var vdRouting = document.getElementById('vd-routing');
  var vdRating = document.getElementById('vd-rating');

  // Helper to format currency
  function formatMoney(amount) {
    var symbol = window.APP_CURRENCY_SYMBOL || '₦';
    var val = Number(amount || 0);
    return symbol + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Delegate suspend, reactivate, verify, resend, and view button clicks
  document.addEventListener('click', function (e) {
    var suspendBtn = e.target.closest('.au-suspend-btn');
    if (suspendBtn) {
      e.preventDefault();
      var userId = suspendBtn.getAttribute('data-user-id') || '';
      var userName = suspendBtn.getAttribute('data-user-name') || 'this user';
      if (suspendUserName) suspendUserName.textContent = userName;
      if (suspendUserId) suspendUserId.value = userId;
      if (suspendModal) suspendModal.classList.remove('hidden');
      return;
    }

    var reactivateBtn = e.target.closest('.au-reactivate-btn');
    if (reactivateBtn) {
      e.preventDefault();
      var rUserId = reactivateBtn.getAttribute('data-user-id') || '';
      var rUserName = reactivateBtn.getAttribute('data-user-name') || 'this user';
      if (reactivateUserName) reactivateUserName.textContent = rUserName;
      if (reactivateUserId) reactivateUserId.value = rUserId;
      if (reactivateModal) reactivateModal.classList.remove('hidden');
      return;
    }

    var verifyBtn = e.target.closest('.au-verify-btn') || e.target.closest('.au-modal-verify-btn');
    if (verifyBtn) {
      e.preventDefault();
      var vUserId = verifyBtn.getAttribute('data-user-id') || '';
      var vUserName = verifyBtn.getAttribute('data-user-name') || (vdName ? vdName.textContent : 'this user');
      if (verifyUserName) verifyUserName.textContent = vUserName;
      if (verifyUserId) verifyUserId.value = vUserId;
      if (verifyModal) verifyModal.classList.remove('hidden');
      return;
    }

    var resendBtn = e.target.closest('.au-modal-resend-btn');
    if (resendBtn) {
      e.preventDefault();
      var rsUserId = resendBtn.getAttribute('data-user-id') || '';
      var rsUserName = resendBtn.getAttribute('data-user-name') || (vdName ? vdName.textContent : 'this user');
      if (resendUserName) resendUserName.textContent = rsUserName;
      if (resendUserId) resendUserId.value = rsUserId;
      if (resendModal) resendModal.classList.remove('hidden');
      return;
    }

    var viewBtn = e.target.closest('.au-view-btn');
    if (viewBtn) {
      e.preventDefault();
      var targetUserId = viewBtn.getAttribute('data-user-id') || '';
      if (!targetUserId) return;

      // Show modal and loading indicator, hide content
      if (viewModal) viewModal.classList.remove('hidden');
      if (viewModalLoading) viewModalLoading.classList.remove('hidden');
      if (viewModalContent) viewModalContent.classList.add('hidden');

      // Fetch user details
      var detailUrl = viewBtn.getAttribute('data-detail-url');
      if (!detailUrl) {
        var basePath = window.location.pathname.replace(/\/admin\/users\/?$/, '');
        detailUrl = basePath + '/admin/users/detail?id=' + encodeURIComponent(targetUserId);
      }

      fetch(detailUrl)
        .then(function (res) {
          if (!res.ok) throw new Error('Failed to load user detail');
          return res.json();
        })
        .then(function (data) {
          var user = data.user;
          var stats = data.stats;

          // Populate basic details
          if (vdName) vdName.textContent = user.name || 'N/A';
          if (vdEmail) vdEmail.textContent = user.email || 'N/A';
          if (vdPhone) vdPhone.textContent = user.phone || 'N/A';
          if (vdJoined) {
            vdJoined.textContent = user.joined_at || 'N/A';
          }

          // Populate status badge
          if (vdStatus) {
            vdStatus.textContent = user.status === 'suspended' ? 'Suspended' : 'Active';
            vdStatus.className = 'au-status-badge ' + (user.status === 'suspended' ? 'au-status--suspended' : 'au-status--active');
          }

          // Populate email verification status
          var isVerified = !!user.is_verified;
          var isHost = (user.role_slug === 'event-organizer' || user.role_slug === 'host');

          if (vdVerificationBadge) {
            if (isVerified) {
              vdVerificationBadge.textContent = '✓ Verified';
              vdVerificationBadge.className = 'au-status-badge au-status--active';
            } else {
              vdVerificationBadge.textContent = '⚠️ Unverified';
              vdVerificationBadge.className = 'au-status-badge';
              vdVerificationBadge.style.cssText = 'background:rgba(245,158,11,0.12); color:#f59e0b;';
            }
          }

          if (vdVerificationDate) {
            if (isVerified && user.email_verified_at_formatted) {
              vdVerificationDate.textContent = 'Verified on ' + user.email_verified_at_formatted;
            } else {
              vdVerificationDate.textContent = '';
            }
          }

          if (vdVerificationWarning) {
            if (isHost && !isVerified) {
              vdVerificationWarning.classList.remove('hidden');
            } else {
              vdVerificationWarning.classList.add('hidden');
            }
          }

          if (vdVerificationActions) {
            if (!isVerified) {
              vdVerificationActions.classList.remove('hidden');
              if (vdModalVerifyBtn) {
                vdModalVerifyBtn.setAttribute('data-user-id', user.id);
                vdModalVerifyBtn.setAttribute('data-user-name', user.name);
              }
              if (vdModalResendBtn) {
                vdModalResendBtn.setAttribute('data-user-id', user.id);
                vdModalResendBtn.setAttribute('data-user-name', user.name);
              }
            } else {
              vdVerificationActions.classList.add('hidden');
            }
          }

          // Populate role badge
          if (vdRole) {
            var roleLabel = 'Attendee';
            var roleClass = 'au-role--attendee';
            if (user.role_slug === 'event-organizer') {
              roleLabel = 'Host';
              roleClass = 'au-role--host';
            } else if (user.role_slug === 'admin' || user.role_slug === 'super-admin') {
              roleLabel = 'Admin';
              roleClass = 'au-role--admin';
            }
            vdRole.textContent = roleLabel;
            vdRole.className = 'au-role-badge ' + roleClass;
          }

          // Toggle sections and populate stats
          if (user.role_slug === 'event-organizer') {
            if (vdSectionAttendee) vdSectionAttendee.classList.add('hidden');
            if (vdSectionHost) vdSectionHost.classList.remove('hidden');

            if (vdBio) vdBio.textContent = stats.bio || 'No bio set.';
            if (vdTotalEvents) vdTotalEvents.textContent = stats.total_events || 0;
            if (vdTicketsSold) vdTicketsSold.textContent = stats.tickets_sold || 0;
            if (vdTotalRevenue) vdTotalRevenue.textContent = stats.total_revenue_formatted || formatMoney(stats.total_revenue || 0);
            if (vdAvailableBalance) vdAvailableBalance.textContent = stats.available_balance_formatted || formatMoney(stats.available_balance || 0);
            if (vdBank) vdBank.textContent = stats.payout_bank || 'Not set';
            if (vdAccount) vdAccount.textContent = stats.payout_account || 'Not set';
            if (vdRouting) vdRouting.textContent = stats.payout_routing || 'Not set';
            if (vdRating) vdRating.textContent = (stats.rating || 5.00).toFixed(2) + ' / 5.00';

            // Render Recent Events
            var eventsList = document.getElementById('vd-host-events-list');
            if (eventsList) {
              eventsList.innerHTML = '';
              var events = stats.events_history || [];
              if (events.length === 0) {
                eventsList.innerHTML = '<p style="margin:0; font-size:0.9rem; color:var(--text-muted);">No events hosted yet.</p>';
              } else {
                events.forEach(function (e) {
                  var eventDate = new Date(e.date);
                  var formattedDate = isNaN(eventDate.getTime()) ? 'N/A' : eventDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                  var badgeClass = e.status === 'published' ? 'au-status--active' : 'au-status--suspended';
                  var statusText = e.status.charAt(0).toUpperCase() + e.status.slice(1);

                  var item = document.createElement('div');
                  item.style.cssText = 'display:flex; justify-content:space-between; align-items:center; padding:0.65rem 0.85rem; background:rgba(255,255,255,0.01); border:1px solid rgba(255,255,255,0.03); border-radius:8px;';
                  item.innerHTML = 
                    '<div>' +
                      '<span style="font-weight:550; font-size:0.92rem; display:block;">' + e.name + '</span>' +
                      '<span style="font-size:0.8rem; color:var(--text-muted);">' + formattedDate + '</span>' +
                    '</div>' +
                    '<div style="display:flex; align-items:center; gap:0.75rem;">' +
                      '<span style="font-size:0.85rem; color:var(--text-muted);">' + e.tickets_sold + ' tickets sold</span>' +
                      '<span class="au-status-badge ' + badgeClass + '" style="font-size:0.75rem; padding:0.15rem 0.5rem; height:auto;">' + statusText + '</span>' +
                    '</div>';
                  eventsList.appendChild(item);
                });
              }
            }

            // Render Recent Payouts
            var payoutsList = document.getElementById('vd-host-payouts-list');
            if (payoutsList) {
              payoutsList.innerHTML = '';
              var payouts = stats.payouts_history || [];
              if (payouts.length === 0) {
                payoutsList.innerHTML = '<p style="margin:0; font-size:0.9rem; color:var(--text-muted);">No payout requests submitted yet.</p>';
              } else {
                payouts.forEach(function (p) {
                  var payoutDate = new Date(p.created_at);
                  var formattedDate = isNaN(payoutDate.getTime()) ? 'N/A' : payoutDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                  var badgeClass = 'au-status--active';
                  if (p.status === 'failed') badgeClass = 'au-status--suspended';
                  else if (p.status === 'pending') badgeClass = 'au-status--warning';
                  var statusText = p.status.charAt(0).toUpperCase() + p.status.slice(1);

                  var item = document.createElement('div');
                  item.style.cssText = 'display:flex; justify-content:space-between; align-items:center; padding:0.65rem 0.85rem; background:rgba(255,255,255,0.01); border:1px solid rgba(255,255,255,0.03); border-radius:8px;';
                  item.innerHTML = 
                    '<div>' +
                      '<span style="font-weight:600; font-size:0.92rem; display:block;">' + (p.amount_formatted || formatMoney(p.amount)) + '</span>' +
                      '<span style="font-size:0.8rem; color:var(--text-muted);">For ' + (p.event_name || 'Event') + '</span>' +
                    '</div>' +
                    '<div style="display:flex; align-items:center; gap:0.75rem;">' +
                      '<span style="font-size:0.8rem; color:var(--text-muted);">' + formattedDate + '</span>' +
                      '<span class="au-status-badge ' + badgeClass + '" style="font-size:0.75rem; padding:0.15rem 0.5rem; height:auto;">' + statusText + '</span>' +
                    '</div>';
                  payoutsList.appendChild(item);
                });
              }
            }
          } else if (user.role_slug === 'admin' || user.role_slug === 'super-admin') {
            if (vdSectionHost) vdSectionHost.classList.add('hidden');
            if (vdSectionAttendee) vdSectionAttendee.classList.add('hidden');
          } else {
            if (vdSectionHost) vdSectionHost.classList.add('hidden');
            if (vdSectionAttendee) vdSectionAttendee.classList.remove('hidden');

            if (vdTicketsBought) vdTicketsBought.textContent = stats.tickets_bought || 0;
            if (vdTotalSpent) vdTotalSpent.textContent = stats.total_spent_formatted || formatMoney(stats.total_spent || 0);
            if (vdLastPurchase) {
              vdLastPurchase.textContent = (stats.last_purchase_date && stats.last_purchase_date !== 'N/A') ? stats.last_purchase_date : 'Never';
            }
          }

          // Hide loading, show content
          if (viewModalLoading) viewModalLoading.classList.add('hidden');
          if (viewModalContent) viewModalContent.classList.remove('hidden');
        })
        .catch(function (err) {
          console.error(err);
          alert('Error fetching user profile details.');
          if (viewModal) viewModal.classList.add('hidden');
        });
      return;
    }
  });

  // Cancel and close buttons
  if (suspendCancelBtn) {
    suspendCancelBtn.addEventListener('click', function () {
      if (suspendModal) suspendModal.classList.add('hidden');
    });
  }
  if (reactivateCancelBtn) {
    reactivateCancelBtn.addEventListener('click', function () {
      if (reactivateModal) reactivateModal.classList.add('hidden');
    });
  }
  if (verifyCancelBtn) {
    verifyCancelBtn.addEventListener('click', function () {
      if (verifyModal) verifyModal.classList.add('hidden');
    });
  }
  if (resendCancelBtn) {
    resendCancelBtn.addEventListener('click', function () {
      if (resendModal) resendModal.classList.add('hidden');
    });
  }
  if (viewCloseBtn) {
    viewCloseBtn.addEventListener('click', function () {
      if (viewModal) viewModal.classList.add('hidden');
    });
  }
  if (viewCloseXBtn) {
    viewCloseXBtn.addEventListener('click', function () {
      if (viewModal) viewModal.classList.add('hidden');
    });
  }

  // Close modals on overlay click
  [suspendModal, reactivateModal, verifyModal, resendModal, viewModal].forEach(function (modal) {
    if (!modal) return;
    modal.addEventListener('click', function (e) {
      if (e.target === modal) {
        modal.classList.add('hidden');
      }
    });
  });

  // Close modals on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      if (suspendModal && !suspendModal.classList.contains('hidden')) {
        suspendModal.classList.add('hidden');
      }
      if (reactivateModal && !reactivateModal.classList.contains('hidden')) {
        reactivateModal.classList.add('hidden');
      }
      if (verifyModal && !verifyModal.classList.contains('hidden')) {
        verifyModal.classList.add('hidden');
      }
      if (resendModal && !resendModal.classList.contains('hidden')) {
        resendModal.classList.add('hidden');
      }
      if (viewModal && !viewModal.classList.contains('hidden')) {
        viewModal.classList.add('hidden');
      }
    }
  });

  // ─── Client-Side Instant Search ─────────────────────────
  // This provides instant filtering while the user types.
  // The form also submits to the server via GET for full server-side search.

  var searchInput = document.getElementById('users-search');
  var tableBody = document.getElementById('users-tbody');
  var cardsList = document.getElementById('users-cards');
  var emptyState = document.getElementById('users-empty');

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      var query = (searchInput.value || '').toLowerCase().trim();

      // Filter table rows
      if (tableBody) {
        var rows = tableBody.querySelectorAll('tr');
        var visibleRows = 0;

        rows.forEach(function (row) {
          var name = (row.querySelector('.au-user-name') || {}).textContent || '';
          var email = (row.querySelector('.au-email-cell') || {}).textContent || '';
          var match = name.toLowerCase().indexOf(query) !== -1 ||
                      email.toLowerCase().indexOf(query) !== -1;

          row.style.display = match ? '' : 'none';
          if (match) visibleRows++;
        });

        // Toggle empty state
        if (emptyState) {
          emptyState.classList.toggle('hidden', visibleRows > 0 || query === '');
        }
      }

      // Filter mobile cards
      if (cardsList) {
        var cards = cardsList.querySelectorAll('.au-user-card');
        cards.forEach(function (card) {
          var name = (card.querySelector('.au-user-name') || {}).textContent || '';
          var email = (card.querySelector('.au-card-email') || {}).textContent || '';
          var match = name.toLowerCase().indexOf(query) !== -1 ||
                      email.toLowerCase().indexOf(query) !== -1;
          card.style.display = match ? '' : 'none';
        });
      }
    });
  }

})();