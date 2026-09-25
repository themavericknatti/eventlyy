/*
========================================================
 Project: Evently
 File: host-dashboard.js
 Author: Celio Natti
 Version: 1.0.0
 Created: 2026-06-19
 Description: Handles the dashboard page for hosts, including UI interactions for event management.
========================================================
*/

// host-dashboard.js — Handles dynamic filtering and pagination for host dashboard events
document.addEventListener('DOMContentLoaded', function () {
    // Elements
    const tabContainer = document.querySelector('.dashboard-tabs');
    const paginationContainer = document.getElementById('events-pagination');

    // Check if developer wants server-side (PHP) pagination/filtering
    const isServerFiltered = tabContainer && tabContainer.getAttribute('data-server-filtered') === 'true';
    const isServerPaginated = paginationContainer && paginationContainer.getAttribute('data-server-paginated') === 'true';

    // If server-side handles everything, bypass client-side script completely
    if (isServerFiltered && isServerPaginated) {
        console.log('Evently: Server-side pagination & filtering active. Bypassing client JS.');
        return;
    }

    const CARDS_PER_PAGE = 6;
    let currentPage = 1;
    let currentFilter = 'all';

    const tabButtons = document.querySelectorAll('.tab-btn');
    const eventCards = document.querySelectorAll('.host-event-card');
    const pageNumbersContainer = document.getElementById('page-numbers');
    const prevBtn = document.getElementById('prev-page');
    const nextBtn = document.getElementById('next-page');
    const emptyState = document.getElementById('empty-events');

    // Update tab counts on load
    function updateTabBadges() {
        let counts = { all: 0, live: 0, upcoming: 0, past: 0, mine: 0, others: 0 };
        eventCards.forEach(card => {
            counts.all++;
            const status = card.getAttribute('data-status');
            if (counts[status] !== undefined) {
                counts[status]++;
            }
            const owner = card.getAttribute('data-owner');
            if (owner === 'mine') counts.mine++;
            else if (owner === 'others') counts.others++;
        });

        const badgeAll = document.getElementById('badge-all');
        const badgeLive = document.getElementById('badge-live');
        const badgeUpcoming = document.getElementById('badge-upcoming');
        const badgePast = document.getElementById('badge-past');
        const badgeMine = document.getElementById('badge-mine');
        const badgeOthers = document.getElementById('badge-others');

        if (badgeAll) badgeAll.textContent = counts.all;
        if (badgeLive) badgeLive.textContent = counts.live;
        if (badgeUpcoming) badgeUpcoming.textContent = counts.upcoming;
        if (badgePast) badgePast.textContent = counts.past;
        if (badgeMine) badgeMine.textContent = counts.mine;
        if (badgeOthers) badgeOthers.textContent = counts.others;
    }

    // Get list of currently filtered cards
    function getFilteredCards() {
        if (currentFilter === 'all') {
            return Array.from(eventCards);
        }
        // Ownership filters use data-owner attribute
        if (currentFilter === 'mine' || currentFilter === 'others') {
            return Array.from(eventCards).filter(card => card.getAttribute('data-owner') === currentFilter);
        }
        return Array.from(eventCards).filter(card => card.getAttribute('data-status') === currentFilter);
    }

    // Apply filtering and pagination display
    function render() {
        const activeCards = getFilteredCards();
        const totalCards = activeCards.length;
        const totalPages = Math.ceil(totalCards / CARDS_PER_PAGE) || 1;

        // Boundary check for current page
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        // Toggle Empty State
        if (totalCards === 0) {
            emptyState.style.display = '';
            paginationContainer.style.display = 'none';
        } else {
            emptyState.style.display = 'none';
            paginationContainer.style.display = totalCards > CARDS_PER_PAGE ? 'flex' : 'none';
        }

        // Hide all cards first
        eventCards.forEach(card => card.style.display = 'none');

        // Show cards for current page
        const startIdx = (currentPage - 1) * CARDS_PER_PAGE;
        const endIdx = startIdx + CARDS_PER_PAGE;
        const pageCards = activeCards.slice(startIdx, endIdx);

        pageCards.forEach(card => {
            // Use CSS display grids or flex as originally intended
            const status = card.getAttribute('data-status');
            card.style.display = '';
        });

        // Render page numbers
        renderPaginationButtons(totalPages);
    }

    function renderPaginationButtons(totalPages) {
        if (pageNumbersContainer) {
            pageNumbersContainer.innerHTML = '';
        }

        for (let i = 1; i <= totalPages; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = 'page-button';
            if (i === currentPage) {
                pageBtn.setAttribute('aria-current', 'true');
            }
            pageBtn.textContent = i;
            pageBtn.addEventListener('click', () => {
                currentPage = i;
                render();
                scrollToContainer();
            });
            if (pageNumbersContainer) {
                pageNumbersContainer.appendChild(pageBtn);
            }
        }

        // Update Prev / Next button states
        if (prevBtn) prevBtn.disabled = currentPage === 1;
        if (nextBtn) nextBtn.disabled = currentPage === totalPages;
    }

    function scrollToContainer() {
        const section = document.querySelector('.host-events-grid');
        if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // Add click listeners to tabs
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Toggle active states
            tabButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            currentFilter = btn.getAttribute('data-filter');
            currentPage = 1; // reset page
            render();
        });
    });

    // Next and Prev handlers
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                render();
                scrollToContainer();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            const activeCards = getFilteredCards();
            const totalPages = Math.ceil(activeCards.length / CARDS_PER_PAGE);
            if (currentPage < totalPages) {
                currentPage++;
                render();
                scrollToContainer();
            }
        });
    }

    // Init
    updateTabBadges();
    render();
});
