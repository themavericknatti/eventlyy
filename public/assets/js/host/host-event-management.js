/*
========================================================
 Project: Evently
 File: host-event-management.js
 Author: Celio Natti
 Version: 1.0.0
 Created: 2026-06-19
 Description: Handles the event management page for hosts, including UI interactions for event actions (create, edit, delete).
========================================================
*/

// host-event-management.js — Minimal UI logic for event management page
(function () {
    // ── Modal ─────────────────────────────────────────────
    var backdrop = document.getElementById('modal-backdrop');
    var modalTitle = document.getElementById('modal-title');
    var modalBody = document.getElementById('modal-body');
    var modalClose = document.getElementById('modal-close');
    var modalCancel = document.getElementById('modal-cancel');
    var modalConfirm = document.getElementById('modal-confirm');
    var pendingAction = null;
    var rowDataCache = [];

    function openModal(title, bodyHTML, confirmLabel, onConfirm) {
        if (!backdrop) return;
        modalTitle.textContent = title;
        modalBody.innerHTML = bodyHTML;
        if (confirmLabel) {
            modalConfirm.textContent = confirmLabel;
            modalConfirm.style.display = '';
        } else {
            modalConfirm.style.display = 'none';
        }
        pendingAction = onConfirm || null;
        backdrop.classList.remove('hidden');
        backdrop.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        if (!backdrop) return;
        backdrop.classList.add('hidden');
        backdrop.setAttribute('aria-hidden', 'true');
        pendingAction = null;
    }

    if (modalClose) modalClose.addEventListener('click', closeModal);
    if (modalCancel) modalCancel.addEventListener('click', closeModal);
    if (modalConfirm) {
        modalConfirm.addEventListener('click', function () {
            if (typeof pendingAction === 'function') {
                var result = pendingAction();
                if (result === false) return; // Keep modal open if validation failed
            }
            closeModal();
        });
    }

    // Close on backdrop click (outside modal card)
    if (backdrop) {
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) closeModal();
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && backdrop && !backdrop.classList.contains('hidden')) {
            closeModal();
        }
    });

    // ── Toast helper ──────────────────────────────────────
    function showToast(message, type) {
        var container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        var toast = document.createElement('div');
        toast.className = 'toast ' + (type || 'info');
        toast.textContent = message;
        container.appendChild(toast);
        requestAnimationFrame(function () { toast.classList.add('visible'); });
        setTimeout(function () {
            toast.classList.remove('visible');
            setTimeout(function () { toast.remove(); }, 300);
        }, 3000);
    }
    window.showToast = showToast;

    // ── Export Report Button ──────────────────────────────
    var exportBtn = document.getElementById('export-report-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            openModal(
                '📊 Export Report',
                '<p>Choose a format to export the event report for <strong>Summer Beats Festival</strong>.</p>' +
                '<div style="display:flex;gap:0.5rem;margin-top:1rem;flex-wrap:wrap;">' +
                '<button class="btn primary export-option" data-format="csv" style="flex:1;min-width:100px;">CSV</button>' +
                '<button class="btn export-option" data-format="pdf" style="flex:1;min-width:100px;">PDF</button>' +
                '<button class="btn export-option" data-format="xlsx" style="flex:1;min-width:100px;">Excel</button>' +
                '</div>',
                null, // hide the default confirm button
                null
            );

            // Handle format buttons inside the modal
            var options = modalBody.querySelectorAll('.export-option');
            options.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var format = btn.getAttribute('data-format');
                    closeModal();
                    showToast('Exporting report as ' + format.toUpperCase() + '...', 'info');
                    // PHP: Replace with actual export logic
                    // window.location.href = 'export.php?format=' + format + '&event_id=...';
                });
            });
        });
    }


    // ── Issue Ticket Button ──────────────────────────
    var addTicketManualBtn = document.getElementById('add-ticket-manual-btn');
    if (addTicketManualBtn) {
        addTicketManualBtn.addEventListener('click', function () {
            var template = document.getElementById('manual-ticket-modal-template');
            var bodyHTML = template ? template.innerHTML : '';

            openModal(
                '🎟️ Issue Ticket',
                bodyHTML,
                'Issue Ticket',
                function () {
                    var form = document.getElementById('manual-ticket-form');
                    if (!form) return;

                    // Native form validation check
                    if (!form.reportValidity()) {
                        return false; // keeps modal open
                    }

                    // In PHP development, the form submits natively to PHP.
                    // The developer can set the form's action attribute to their controller/endpoint.
                    form.submit();
                }
            );
        });
    }

    // ── Search Tickets (Lively & PHP-friendly) ────────────
    var searchForm = document.getElementById('tickets-search-form');
    var searchInput = document.getElementById('search-tickets');
    var ticketsTable = document.getElementById('tickets-table');
    var clearBtn = document.getElementById('search-clear-btn');

    if (searchInput && ticketsTable) {
        var isServerSearch = searchForm && searchForm.getAttribute('data-server-search') === 'true';

        // Store original text of the rows to reset highlighting cleanly
        var initialRows = ticketsTable.querySelectorAll('tbody tr');
        initialRows.forEach(function (row, idx) {
            var cells = row.querySelectorAll('td');
            if (cells.length >= 2) {
                rowDataCache[idx] = {
                    nameCell: cells[0],
                    nameOriginal: cells[0].innerHTML,
                    emailCell: cells[1],
                    emailOriginal: cells[1].innerHTML
                };
            }
        });

        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function highlightText(cell, originalHTML, query) {
            if (!query) {
                cell.innerHTML = originalHTML;
                return;
            }
            // Simple text replacement inside text nodes (ignoring links/tags)
            var escapedQuery = escapeRegExp(query);
            var reg = new RegExp('(' + escapedQuery + ')', 'gi');

            // If the cell contains an anchor tag, highlight inside it
            var anchor = cell.querySelector('a');
            if (anchor) {
                var txt = anchor.textContent;
                var newTxt = txt.replace(reg, '<span class="highlight-text">$1</span>');
                anchor.innerHTML = newTxt;
            } else {
                var txt = cell.textContent;
                var newTxt = txt.replace(reg, '<span class="highlight-text">$1</span>');
                cell.innerHTML = newTxt;
            }
        }

        function performSearch() {
            var query = searchInput.value.trim();
            var queryLower = query.toLowerCase();

            // Toggle clear button visibility
            if (clearBtn) {
                clearBtn.style.display = query ? 'flex' : 'none';
            }

            if (isServerSearch) {
                // Let natural PHP backend handles it, no client row filtering
                return;
            }

            var currentRows = ticketsTable.querySelectorAll('tbody tr');
            var visibleCount = 0;
            currentRows.forEach(function (row, idx) {
                var text = row.textContent.toLowerCase();
                var match = !queryLower || text.indexOf(queryLower) !== -1;
                row.style.display = match ? '' : 'none';

                if (match) {
                    visibleCount++;
                    // Apply highlight
                    var cache = rowDataCache[idx];
                    if (cache) {
                        highlightText(cache.nameCell, cache.nameOriginal, query);
                        highlightText(cache.emailCell, cache.emailOriginal, query);
                    }
                } else {
                    // Reset highlight
                    var cache = rowDataCache[idx];
                    if (cache) {
                        cache.nameCell.innerHTML = cache.nameOriginal;
                        cache.emailCell.innerHTML = cache.emailOriginal;
                    }
                }
            });

            var emptyState = document.getElementById('no-tickets');
            if (emptyState) {
                emptyState.style.display = (visibleCount === 0 && currentRows.length > 0) || currentRows.length === 0 ? '' : 'none';
            }
        }

        // Input event for live visual feedback
        searchInput.addEventListener('input', performSearch);

        // Clear button functionality
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                performSearch();
                searchInput.focus();

                // If server search was performed, submit form to reload all items
                if (isServerSearch && searchForm) {
                    searchForm.submit();
                }
            });
        }

        // Trigger initial search filter check in case input is pre-populated on load
        performSearch();
    }

})();
