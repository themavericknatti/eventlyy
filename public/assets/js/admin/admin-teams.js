/*
========================================================
 Project: Evently
 File: admin-teams.js
 Author: Celio Natti
 Version: 1.1.0
 Created: 2026-07-02
 Description: Client-side JavaScript for admin teams management.
========================================================
*/

(function() {
  'use strict';

  // ─── Modal Elements ──────────────────────────────────────
  var removeModal = document.getElementById('remove-modal');
  var removeMemberName = document.getElementById('remove-member-name');
  var removeMemberId = document.getElementById('remove-member-id');
  var removeCancelBtn = document.getElementById('remove-cancel-btn');

  var unassignModal = document.getElementById('unassign-modal');
  var unassignMemberName = document.getElementById('unassign-member-name');
  var unassignEventName = document.getElementById('unassign-event-name');
  var unassignAssignmentId = document.getElementById('unassign-assignment-id');
  var unassignMemberId = document.getElementById('unassign-member-id');
  var unassignEventId = document.getElementById('unassign-event-id');
  var unassignCancelBtn = document.getElementById('unassign-cancel-btn');

  var detailModal = document.getElementById('staff-detail-modal');
  var detailCloseBtn = document.getElementById('staff-detail-close');
  var detailContent = document.getElementById('staff-detail-content');

  // Delegate action button clicks (View, Remove & Unassign)
  document.addEventListener('click', function(e) {
    // View Staff Detail
    var viewBtn = e.target.closest('.au-view-staff-btn');
    if (viewBtn) {
      e.preventDefault();
      var staffId = viewBtn.getAttribute('data-staff-id');
      if (!staffId) return;

      if (detailModal) detailModal.classList.remove('hidden');
      if (detailContent) detailContent.innerHTML = '<div style="text-align:center; padding:2.5rem 1rem; color:rgb(var(--muted));"><div style="font-size:2rem; margin-bottom:0.5rem;">⏳</div><div>Loading staff profile...</div></div>';

      fetch('/admin/teams/detail?id=' + encodeURIComponent(staffId), {
        headers: { 'Accept': 'application/json' }
      })
      .then(function(res) { return res.json(); })
      .then(function(resData) {
        if (!resData.success || !resData.data) {
          if (detailContent) detailContent.innerHTML = '<div style="text-align:center; color:#ef4444; padding:2rem;">Failed to load staff details.</div>';
          return;
        }

        var staff = resData.data.staff;
        var assignments = resData.data.assignments || [];
        var upcomingEvents = resData.data.upcoming_events || [];
        var roles = resData.data.roles || [];

        var hue = Math.abs(crc32(staff.name)) % 360;
        var initials = staff.name.substring(0, 2).toUpperCase();
        var isActive = (staff.status === 'active');
        var statusBadge = isActive 
          ? '<span class="au-status-badge au-status--active" style="background:rgba(16, 185, 129, 0.12); color:#059669; border:1px solid rgba(16, 185, 129, 0.28); font-weight:700;">✓ Active</span>' 
          : '<span class="au-status-badge au-status--suspended" style="font-weight:700;">Inactive</span>';

        // 1. Staff Identity Card
        var html = '' +
          '<div style="display:flex; gap:1.25rem; align-items:center; margin-bottom:1.5rem; background:rgba(var(--muted),0.06); padding:1.25rem; border-radius:16px; flex-wrap:wrap; border:1px solid rgba(var(--muted),0.12);">' +
            '<span class="au-avatar" style="--avatar-hue:' + hue + '; width:58px; height:58px; font-size:1.35rem; flex-shrink:0;">' + initials + '</span>' +
            '<div style="min-width:0; flex:1;">' +
              '<div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.5rem;">' +
                '<div>' +
                  '<h3 style="margin:0 0 0.2rem 0; font-size:1.2rem; font-weight:800; word-break:break-word;">' + escapeHtml(staff.name) + '</h3>' +
                  '<div style="font-size:0.88rem; color:rgb(var(--muted)); word-break:break-all;">' + escapeHtml(staff.email) + '</div>' +
                '</div>' +
                '<div>' + statusBadge + '</div>' +
              '</div>' +
              '<div style="margin-top:0.6rem; display:flex; align-items:center; flex-wrap:wrap; gap:0.75rem; font-size:0.78rem; color:rgb(var(--muted));">' +
                '<span>📅 Registered: ' + formatDate(staff.created_at) + '</span>' +
                '<span>•</span>' +
                '<span>ID: #' + staff.id + '</span>' +
              '</div>' +
            '</div>' +
          '</div>';

        // 2. Active Deployments Section
        html += '<div style="margin-bottom:1.75rem;">' +
          '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">' +
            '<h4 style="margin:0; font-size:1.05rem; font-weight:800;">Current Event Deployments (' + assignments.length + ')</h4>' +
          '</div>';

        if (assignments.length === 0) {
          html += '<div style="color:rgb(var(--muted)); padding:1.5rem; text-align:center; background:rgba(var(--muted),0.03); border-radius:14px; border:1px dashed rgba(var(--muted),0.2); font-size:0.88rem;">' +
            '<div style="font-size:1.5rem; margin-bottom:0.35rem;">🏖️</div>' +
            '<div>This staff member is currently <strong>unassigned</strong> and available for upcoming events.</div>' +
          '</div>';
        } else {
          // Responsive assignment list
          html += '<div style="display:flex; flex-direction:column; gap:0.65rem;">';
          assignments.forEach(function(asg) {
            var rolePill = getRolePill(asg.role_name, asg.role_slug);
            html += '<div style="display:flex; justify-content:space-between; align-items:center; padding:0.85rem 1rem; border-radius:12px; background:rgba(var(--muted),0.04); border:1px solid rgba(var(--muted),0.1); flex-wrap:wrap; gap:0.75rem;">' +
              '<div style="min-width:0; flex:1;">' +
                '<strong style="display:block; font-size:0.92rem; color:rgb(var(--text));">' + escapeHtml(asg.event_name) + '</strong>' +
                '<div style="font-size:0.78rem; color:rgb(var(--muted)); margin-top:0.2rem; display:flex; gap:0.5rem; flex-wrap:wrap;">' +
                  '<span>📅 ' + formatDate(asg.event_date) + '</span>' +
                  (asg.event_location ? '<span>📍 ' + escapeHtml(asg.event_location) + '</span>' : '') +
                '</div>' +
              '</div>' +
              '<div style="display:flex; align-items:center; gap:0.65rem;">' +
                rolePill +
                '<button type="button" class="admin-action-btn danger au-unassign-staff-btn" style="padding:0.35rem 0.75rem; font-size:0.78rem; height:auto;" ' +
                  'data-assignment-id="' + asg.assignment_id + '" ' +
                  'data-member-id="' + staff.id + '" ' +
                  'data-member-name="' + escapeHtml(staff.name) + '" ' +
                  'data-event-name="' + escapeHtml(asg.event_name) + '">Unassign</button>' +
              '</div>' +
            '</div>';
          });
          html += '</div>';
        }
        html += '</div>';

        // 3. Quick Assign to Upcoming Event Form (Inline in modal)
        if (isActive && upcomingEvents.length > 0) {
          html += '<div style="margin-top:1.5rem; padding:1.15rem 1.25rem; border-radius:14px; background:rgba(var(--brand),0.04); border:1px solid rgba(var(--brand),0.15);">' +
            '<h4 style="margin:0 0 0.5rem 0; font-size:0.95rem; font-weight:800; color:rgb(var(--text)); display:flex; align-items:center; gap:0.4rem;">' +
              '<span>➕</span> Deploy to Another Upcoming Event' +
            '</h4>' +
            '<form method="POST" action="/admin/teams" style="margin-top:0.75rem;">' +
              '<input type="hidden" name="csrf_token" value="' + getCsrfToken() + '" />' +
              '<input type="hidden" name="action" value="assign_member" />' +
              '<input type="hidden" name="member_id" value="' + staff.id + '" />' +
              '<div style="display:grid; grid-template-columns:1fr 1fr; gap:0.65rem; margin-bottom:0.75rem;" class="modal-assign-split">' +
                '<div>' +
                  '<label style="display:block; font-size:0.78rem; font-weight:700; margin-bottom:0.25rem; color:rgb(var(--text));">Upcoming Event</label>' +
                  '<select name="event_id" required class="team-form-select" style="margin-top:0; font-size:0.85rem; padding:0.5rem 0.75rem;">' +
                    '<option value="">-- Choose event --</option>';
                    upcomingEvents.forEach(function(ev) {
                      var dateStr = ev.date ? ' (' + formatDate(ev.date) + ')' : '';
                      html += '<option value="' + ev.id + '">' + escapeHtml(ev.name) + dateStr + '</option>';
                    });
          html += '</select>' +
                '</div>' +
                '<div>' +
                  '<label style="display:block; font-size:0.78rem; font-weight:700; margin-bottom:0.25rem; color:rgb(var(--text));">RBAC Role</label>' +
                  '<select name="role_id" required class="team-form-select" style="margin-top:0; font-size:0.85rem; padding:0.5rem 0.75rem;">' +
                    '<option value="">-- Choose role --</option>';
                    roles.forEach(function(r) {
                      html += '<option value="' + r.id + '">' + escapeHtml(r.name) + '</option>';
                    });
          html += '</select>' +
                '</div>' +
              '</div>' +
              '<button type="submit" class="admin-action-btn primary" style="width:100%; justify-content:center; padding:0.6rem 1rem; font-weight:700; border-radius:10px;">' +
                'Confirm &amp; Assign to Event' +
              '</button>' +
            '</form>' +
          '</div>';
        } else if (!isActive) {
          html += '<div style="padding:0.75rem 1rem; border-radius:12px; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); font-size:0.82rem; color:#dc2626; font-weight:600;">' +
            '⚠️ This staff member is currently inactive. Activate their account to assign new events.' +
          '</div>';
        }

        if (detailContent) detailContent.innerHTML = html;
      })
      .catch(function(err) {
        console.error(err);
        if (detailContent) detailContent.innerHTML = '<div style="text-align:center; color:#ef4444; padding:2rem;">An error occurred loading details.</div>';
      });
      return;
    }

    // Remove Staff Modal Trigger
    var removeBtn = e.target.closest('.au-remove-staff-btn');
    if (removeBtn) {
      e.preventDefault();
      var memberId = removeBtn.getAttribute('data-member-id') || '';
      var memberName = removeBtn.getAttribute('data-member-name') || 'this staff member';
      
      if (removeMemberName) removeMemberName.textContent = memberName;
      if (removeMemberId) removeMemberId.value = memberId;
      if (removeModal) removeModal.classList.remove('hidden');
      return;
    }

    // Unassign Staff Modal Trigger
    var unassignBtn = e.target.closest('.au-unassign-staff-btn');
    if (unassignBtn) {
      e.preventDefault();
      var assignmentId = unassignBtn.getAttribute('data-assignment-id') || '';
      var uMemberId = unassignBtn.getAttribute('data-member-id') || '';
      var uEventId = unassignBtn.getAttribute('data-event-id') || '';
      var uMemberName = unassignBtn.getAttribute('data-member-name') || 'this staff member';
      var uEventName = unassignBtn.getAttribute('data-event-name') || 'the event';

      if (unassignMemberName) unassignMemberName.textContent = uMemberName;
      if (unassignEventName) unassignEventName.textContent = uEventName;
      if (unassignAssignmentId) unassignAssignmentId.value = assignmentId;
      if (unassignMemberId) unassignMemberId.value = uMemberId;
      if (unassignEventId) unassignEventId.value = uEventId;
      
      // Close detail modal if open
      if (detailModal) detailModal.classList.add('hidden');
      if (unassignModal) unassignModal.classList.remove('hidden');
      return;
    }
  });

  // Cancel & Close buttons
  if (removeCancelBtn) {
    removeCancelBtn.addEventListener('click', function() {
      if (removeModal) removeModal.classList.add('hidden');
    });
  }
  if (unassignCancelBtn) {
    unassignCancelBtn.addEventListener('click', function() {
      if (unassignModal) unassignModal.classList.add('hidden');
    });
  }
  if (detailCloseBtn) {
    detailCloseBtn.addEventListener('click', function() {
      if (detailModal) detailModal.classList.add('hidden');
    });
  }

  // Close modals on overlay clicks
  [removeModal, unassignModal, detailModal].forEach(function(modal) {
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
      [removeModal, unassignModal, detailModal].forEach(function(m) {
        if (m && !m.classList.contains('hidden')) {
          m.classList.add('hidden');
        }
      });
    }
  });

  // ─── Form Input Validation ──────────────────────────────
  var addForm = document.getElementById('add-team-form');
  var assignForm = document.getElementById('assign-team-form');
  var addMessage = document.getElementById('add-message');
  var assignMessage = document.getElementById('assign-message');

  function showStatus(el, text, type) {
    if (!el) return;
    el.textContent = text;
    el.className = 'auth-message ' + type;
    el.style.display = 'block';
    setTimeout(function() {
      el.style.display = 'none';
    }, 4000);
  }

  if (addForm) {
    addForm.addEventListener('submit', function(e) {
      var nameVal = (document.getElementById('member-name') || {}).value || '';
      var emailVal = (document.getElementById('member-email') || {}).value || '';
      
      if (!nameVal.trim() || !emailVal.trim()) {
        e.preventDefault();
        showStatus(addMessage, 'Please fill in all staff details.', 'error');
      }
    });
  }

  if (assignForm) {
    assignForm.addEventListener('submit', function(e) {
      var staffSelect = document.getElementById('assign-member-select');
      var eventSelect = document.getElementById('assign-event-select');
      var roleSelect = document.getElementById('assign-role-select');

      if (!staffSelect.value || !eventSelect.value || !roleSelect.value) {
        e.preventDefault();
        showStatus(assignMessage, 'Please select staff member, event, and RBAC role.', 'error');
      }
    });
  }

  // Helper utilities
  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function formatDate(str) {
    if (!str) return 'N/A';
    var d = new Date(str);
    if (isNaN(d.getTime())) return str;
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function crc32(str) {
    str = String(str || '');
    var table = [];
    for (var i = 0; i < 256; i++) {
      var c = i;
      for (var j = 0; j < 8; j++) {
        c = (c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1);
      }
      table[i] = c;
    }
    var crc = -1;
    for (var k = 0; k < str.length; k++) {
      crc = (crc >>> 8) ^ table[(crc ^ str.charCodeAt(k)) & 0xFF];
    }
    return (crc ^ (-1)) >>> 0;
  }

  function getRolePill(roleName, roleSlug) {
    var slug = String(roleSlug || roleName || '').toLowerCase();
    var bg = 'rgba(var(--muted), 0.1)';
    var color = 'rgb(var(--text))';
    var border = 'rgba(var(--muted), 0.2)';
    var icon = '👤';

    if (slug.indexOf('scanner') !== -1) {
      bg = 'rgba(2, 132, 199, 0.12)';
      color = '#0284c7';
      border = 'rgba(2, 132, 199, 0.28)';
      icon = '🎫';
    } else if (slug.indexOf('organizer') !== -1 && slug.indexOf('staff') !== -1) {
      bg = 'rgba(139, 92, 246, 0.12)';
      color = '#8b5cf6';
      border = 'rgba(139, 92, 246, 0.28)';
      icon = '🏷️';
    } else if (slug.indexOf('organizer') !== -1) {
      bg = 'rgba(16, 185, 129, 0.12)';
      color = '#059669';
      border = 'rgba(16, 185, 129, 0.28)';
      icon = '👑';
    } else if (slug.indexOf('support') !== -1) {
      bg = 'rgba(245, 158, 11, 0.12)';
      color = '#d97706';
      border = 'rgba(245, 158, 11, 0.28)';
      icon = '🎧';
    } else if (slug.indexOf('tech') !== -1 || slug.indexOf('ops') !== -1) {
      bg = 'rgba(99, 102, 241, 0.12)';
      color = '#4f46e5';
      border = 'rgba(99, 102, 241, 0.28)';
      icon = '⚙️';
    } else if (slug.indexOf('finance') !== -1) {
      bg = 'rgba(20, 184, 166, 0.12)';
      color = '#0d9488';
      border = 'rgba(20, 184, 166, 0.28)';
      icon = '💼';
    }

    return '<span style="background:' + bg + '; color:' + color + '; border:1px solid ' + border + '; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:0.25rem;">' +
      '<span>' + icon + '</span> <span>' + escapeHtml(roleName) + '</span>' +
    '</span>';
  }

  function getCsrfToken() {
    var csrfInput = document.querySelector('input[name="csrf_token"]') || document.querySelector('input[name="_csrf"]');
    if (csrfInput && csrfInput.value) {
      return csrfInput.value;
    }
    var metaCsrf = document.querySelector('meta[name="csrf-token"]');
    return metaCsrf ? metaCsrf.getAttribute('content') : '';
  }

})();
