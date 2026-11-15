/*
========================================================
 Project: Evently
 File: create-event.js
 Author: Celio Natti
 Version: 1.0.0
 Created: 2026-06-19
 Description: Handles the create event page for hosts, including dynamic ticket and guest rows, form validation, and slug generation. Also includes date and time pickers using Flatpickr.
========================================================
*/

document.addEventListener('DOMContentLoaded', () => {
    const ticketList = document.getElementById('ticket-list');
    const addTicketBtn = document.getElementById('add-ticket-btn');
    const guestList = document.getElementById('guest-list');
    const addGuestBtn = document.getElementById('add-guest-btn');
    const addonList = document.getElementById('addon-list');
    const addAddonBtn = document.getElementById('add-addon-btn');
    const organizerPhone = document.getElementById('organizer-phone');
    const organizerSocial = document.getElementById('organizer-social');
    const form = document.getElementById('create-event-form') || document.getElementById('edit-event-form');
    const authMessage = document.querySelector('.auth-message');

    // ── Image Preview Handler ──────────────────────────────
    const eventImageInput = document.getElementById('event-image');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');
    const removeImageBtn = document.getElementById('remove-image-btn');
    const currentImageWrapper = document.getElementById('current-image-wrapper');

    if (eventImageInput && imagePreview && imagePreviewContainer) {
        eventImageInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size exceeds 5MB limit. Please choose a smaller image.');
                    this.value = '';
                    imagePreviewContainer.classList.add('hidden');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imagePreviewContainer.classList.remove('hidden');
                    if (currentImageWrapper) {
                        currentImageWrapper.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            } else {
                imagePreviewContainer.classList.add('hidden');
                if (currentImageWrapper) {
                    currentImageWrapper.style.display = 'flex';
                }
            }
        });

        if (removeImageBtn) {
            removeImageBtn.addEventListener('click', function () {
                eventImageInput.value = '';
                imagePreview.src = '';
                imagePreviewContainer.classList.add('hidden');
                if (currentImageWrapper) {
                    currentImageWrapper.style.display = 'flex';
                }
            });
        }
    }

    // ── Custom URL / slug auto-generation ──────────────────
    const customUrlInput = document.getElementById('custom-url');
    let customTouched = false;

    function slugify(val) {
        return (val || '').toLowerCase().trim()
            .replace(/['"`]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    /** Generate a unique slug by appending a short suffix when needed */
    function generateUniqueSlug(base) {
        let slug = slugify(base);
        if (!slug) slug = 'event-' + Date.now().toString(36);
        return slug;
    }

    if (customUrlInput) {
        customUrlInput.addEventListener('input', () => customTouched = true);
    }

    const eventNameInput = document.getElementById('event-name');
    if (eventNameInput && customUrlInput) {
        eventNameInput.addEventListener('input', () => {
            if (customTouched) return; // don't overwrite if user manually edited
            customUrlInput.value = slugify(eventNameInput.value || '');
        });
    }

    // ── Phone / social validation & masking ────────────────
    function digitsCount(val) { return (val || '').replace(/\D/g, '').length; }
    function validatePhone(val) { const d = digitsCount(val); return d === 0 || (d >= 7 && d <= 15); }
    function validateSocial(val) { if (!val) return true; val = val.trim(); if (val.startsWith('@')) return /^@[\w.]{2,30}$/.test(val); try { const u = new URL(val); return u.protocol === 'http:' || u.protocol === 'https:'; } catch (e) { return false; } }

    function formatPhoneInput(input) {
        if (!input) return;
        let v = input.value || '';
        const leadingPlus = v.trim().startsWith('+');
        let nums = v.replace(/[^\d]/g, '');
        if (nums.length === 0) { input.value = ''; return; }
        if (nums.length > 10) {
            const cc = nums.slice(0, nums.length - 10);
            const rest = nums.slice(-10);
            input.value = (leadingPlus ? '+' : '') + cc + ' ' + rest.slice(0, 3) + ' ' + rest.slice(3, 6) + ' ' + rest.slice(6);
        } else if (nums.length > 6) {
            input.value = (leadingPlus ? '+' : '') + nums.slice(0, 3) + ' ' + nums.slice(3, 6) + ' ' + nums.slice(6);
        } else if (nums.length > 3) {
            input.value = (leadingPlus ? '+' : '') + nums.slice(0, 3) + ' ' + nums.slice(3);
        } else {
            input.value = (leadingPlus ? '+' : '') + nums;
        }
    }

    function attachPhoneMask(input) {
        if (!input) return;
        input.addEventListener('input', () => formatPhoneInput(input));
        input.addEventListener('blur', () => {
            if (input.value && !validatePhone(input.value)) input.classList.add('invalid');
            else input.classList.remove('invalid');
        });
    }

    // ── Dynamic ticket rows ────────────────────────────────
    function updateTicketNumbers() {
        if (!ticketList) return;
        const rows = ticketList.querySelectorAll('.ticket-card, .ticket-row-item');
        rows.forEach((row, i) => {
            const numEl = row.querySelector('.ticket-tier-number');
            if (numEl) {
                const nameInput = row.querySelector('[name="ticket-name[]"]');
                const name = nameInput && nameInput.value.trim() ? `: ${nameInput.value.trim()}` : '';
                numEl.textContent = `Tier #${i + 1}${name}`;
            }
        });
    }

    function createTicketRow() {
        const row = document.createElement('div');
        row.className = 'ticket-row-item ticket-card';
        const currency = (ticketList && ticketList.dataset.currency) ? ticketList.dataset.currency : '$';
        const currentCount = ticketList ? ticketList.querySelectorAll('.ticket-row-item, .ticket-card').length + 1 : 1;
        row.innerHTML = `
            <input type="hidden" name="ticket-id[]" value="0">
            <div class="ticket-card-header">
                <div class="ticket-card-badge">
                    <span>🎟️</span>
                    <span class="ticket-tier-number">Tier #${currentCount}</span>
                </div>
                <button type="button" class="remove-ticket-row-btn ticket-card-remove-btn" title="Remove ticket tier">
                    <span class="remove-icon">✕</span>
                    <span class="remove-text">Remove Tier</span>
                </button>
            </div>
            <div class="ticket-primary-grid">
                <div class="event-field-group" style="margin-bottom:0;">
                    <label class="event-label">Tier Name <span class="required-star">*</span></label>
                    <input type="text" name="ticket-name[]" class="event-input" required placeholder="e.g. VIP Access, General Admission, Table for 5">
                </div>
                <div class="event-field-group" style="margin-bottom:0;">
                    <label class="event-label">Price (${currency}) <span class="required-star">*</span></label>
                    <input type="number" step="0.01" name="ticket-price[]" class="event-input" required placeholder="0.00" min="0">
                </div>
                <div class="event-field-group" style="margin-bottom:0;">
                    <label class="event-label">Total Capacity <span class="required-star">*</span></label>
                    <input type="number" name="ticket-qty[]" class="event-input" required placeholder="e.g. 100" min="1">
                </div>
            </div>
            <div class="ticket-access-shelf">
                <div class="ticket-subfield">
                    <div class="ticket-subfield-header">
                        <span>👥</span>
                        <label class="event-label" style="margin:0;">Admits (Per Ticket)</label>
                    </div>
                    <input type="number" name="ticket-admits[]" class="event-input" value="1" min="1" placeholder="1 (people/ticket)">
                    <span class="ticket-subfield-hint">People entering per 1 ticket (1 = Single, 2 = Couple, 5 = Table).</span>
                </div>
                <div class="ticket-subfield">
                    <div class="ticket-subfield-header">
                        <span>🔒</span>
                        <label class="event-label" style="margin:0;">Max Per Person</label>
                    </div>
                    <input type="number" name="ticket-max-per-person[]" class="event-input" value="" min="1" placeholder="Unlimited">
                    <span class="ticket-subfield-hint">Purchase limit per customer email (leave blank for unlimited).</span>
                </div>
            </div>
        `;
        return row;
    }

    if (addTicketBtn && ticketList) {
        addTicketBtn.addEventListener('click', () => {
            ticketList.appendChild(createTicketRow());
            updateTicketNumbers();
        });

        // Start with one ticket row by default ONLY if list is empty (e.g. create event)
        if (ticketList.children.length === 0 && document.getElementById('create-event-form')) {
            ticketList.appendChild(createTicketRow());
        }

        // Delegate removal of ticket rows
        ticketList.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.remove-ticket-row-btn') || e.target.closest('.ticket-remove') || e.target.closest('.ticket-remove-btn') || e.target.closest('.ticket-card-remove-btn');
            if (removeBtn) {
                if (removeBtn.disabled) return;
                const rows = ticketList.querySelectorAll('.ticket-row-item, .ticket-row');
                if (rows.length > 1) {
                    removeBtn.closest('.ticket-row-item, .ticket-row').remove();
                    updateTicketNumbers();
                } else {
                    alert('You must have at least 1 ticket tier for the event.');
                }
            }
        });
    }

    // ── Dynamic guest / performer rows ─────────────────────
    function updateGuestNumbers() {
        if (!guestList) return;
        const rows = guestList.querySelectorAll('.guest-card, .guest-row');
        rows.forEach((row, i) => {
            const numEl = row.querySelector('.guest-card-title');
            if (numEl) {
                numEl.textContent = `Guest / Performer #${i + 1}`;
            }
        });
    }

    function createGuestRow() {
        const row = document.createElement('div');
        row.className = 'guest-row guest-card';
        const currentCount = guestList ? guestList.querySelectorAll('.guest-row, .guest-card').length + 1 : 1;
        row.innerHTML = `
            <div class="guest-card-header">
                <div class="guest-card-avatar">
                    <span class="guest-card-avatar-circle">👤</span>
                    <span class="guest-card-title">Guest / Performer #${currentCount}</span>
                </div>
                <button type="button" class="guest-remove guest-card-remove-btn" title="Remove guest">
                    <span>✕</span> Remove
                </button>
            </div>
            <div class="guest-card-grid">
                <div class="event-field-group">
                    <label class="event-label">Name <span class="required-star">*</span></label>
                    <input type="text" name="guest-name[]" class="event-input" placeholder="Artist or Speaker Name" required>
                </div>
                <div class="event-field-group">
                    <label class="event-label">Role / Title <span class="optional-tag">(Optional)</span></label>
                    <input type="text" name="guest-role[]" class="event-input" placeholder="e.g. Headliner, Keynote, Panelist">
                </div>
                <div class="event-field-group">
                    <label class="event-label">Contact Phone <span class="optional-tag">(Optional)</span></label>
                    <input type="tel" name="guest-phone[]" class="event-input" placeholder="+1 555 555 5555">
                </div>
                <div class="event-field-group">
                    <label class="event-label">Social / Website <span class="optional-tag">(Optional)</span></label>
                    <input type="text" name="guest-social[]" class="event-input" placeholder="@handle or https://...">
                </div>
            </div>
        `;
        const gp = row.querySelector('[name="guest-phone[]"]');
        const gs = row.querySelector('[name="guest-social[]"]');
        attachPhoneMask(gp);
        if (gs) { gs.addEventListener('blur', () => { if (!validateSocial(gs.value)) gs.classList.add('invalid'); else gs.classList.remove('invalid'); }); }
        return row;
    }

    if (addGuestBtn && guestList) {
        addGuestBtn.addEventListener('click', () => {
            guestList.appendChild(createGuestRow());
            updateGuestNumbers();
        });

        // Delegate removal of guest rows
        guestList.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.guest-remove') || e.target.closest('.guest-card-remove-btn');
            if (removeBtn) {
                removeBtn.closest('.guest-row').remove();
                updateGuestNumbers();
            }
        });
    }

    // ── Dynamic ticket add-on & merchandise rows ───────────
    function updateAddonNumbers() {
        if (!addonList) return;
        const rows = addonList.querySelectorAll('.addon-card, .addon-row-item');
        rows.forEach((row, i) => {
            const numEl = row.querySelector('.addon-tier-number');
            if (numEl) {
                const nameInput = row.querySelector('[name="addon-name[]"]');
                const name = nameInput && nameInput.value.trim() ? `: ${nameInput.value.trim()}` : '';
                numEl.textContent = `Item #${i + 1}${name}`;
            }
        });
    }

    function createAddonRow() {
        const row = document.createElement('div');
        row.className = 'addon-row-item addon-card';
        const currency = (addonList && addonList.dataset.currency) ? addonList.dataset.currency : '$';
        const currentCount = addonList ? addonList.querySelectorAll('.addon-row-item, .addon-card').length + 1 : 1;
        row.innerHTML = `
            <input type="hidden" name="addon-id[]" value="0">
            <div class="addon-card-header">
                <div class="addon-card-badge">
                    <span>🛍️</span>
                    <span class="addon-tier-number">Item #${currentCount}</span>
                </div>
                <button type="button" class="addon-card-remove-btn" title="Remove item">
                    <span class="remove-icon">✕</span>
                    <span class="remove-text">Remove</span>
                </button>
            </div>
            <div class="addon-primary-grid">
                <div class="event-field-group">
                    <label class="event-label">Item / Merch Name <span class="required-star">*</span></label>
                    <input type="text" name="addon-name[]" class="event-input" required placeholder="e.g. Official Tour T-Shirt, VIP Parking Pass, Drink Tokens">
                </div>
                <div class="event-field-group">
                    <label class="event-label">Price (${currency}) <span class="required-star">*</span></label>
                    <input type="number" step="0.01" name="addon-price[]" class="event-input" required placeholder="0.00" min="0">
                </div>
                <div class="event-field-group">
                    <label class="event-label">Inventory / Stock</label>
                    <input type="number" name="addon-quantity[]" class="event-input" min="1" placeholder="Unlimited">
                    <span class="event-help-text" style="font-size:0.72rem;">Leave empty for unlimited.</span>
                </div>
            </div>
            <div class="event-field-group">
                <label class="event-label">Description / Pickup Instructions</label>
                <textarea name="addon-description[]" class="event-input" rows="2" placeholder="e.g. 100% premium cotton. Collect at the official merchandise booth upon entry with your ticket QR code."></textarea>
            </div>
            <div class="addon-media-row" style="display:flex; align-items:center; gap:1rem; margin-top:0.85rem; padding:0.75rem 1rem; background:rgba(var(--muted),0.05); border:1px dashed rgba(var(--muted),0.25); border-radius:12px;">
                <div class="addon-img-preview-box" style="width:64px; height:64px; border-radius:10px; background:rgba(var(--muted),0.1); border:1px solid rgba(var(--muted),0.2); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                    <span class="addon-img-placeholder" style="font-size:1.6rem; opacity:0.6;">🛍️</span>
                </div>
                <div style="flex:1; min-width:0;">
                    <label class="event-label" style="margin-bottom:0.25rem; font-size:0.82rem; font-weight:700;">Item Image / Photo <span style="font-size:0.72rem; color:rgb(var(--muted)); font-weight:normal;">(JPG, PNG, WebP up to 5MB)</span></label>
                    <input type="file" name="addon-image[]" accept="image/jpeg,image/png,image/webp,image/gif" class="addon-file-input event-input" style="font-size:0.8rem; padding:0.4rem 0.6rem;">
                    <input type="hidden" name="addon-image-existing[]" value="">
                </div>
            </div>
        `;

        const nameInput = row.querySelector('[name="addon-name[]"]');
        if (nameInput) {
            nameInput.addEventListener('input', () => updateAddonNumbers());
        }

        return row;
    }

    if (addAddonBtn && addonList) {
        addAddonBtn.addEventListener('click', () => {
            addonList.appendChild(createAddonRow());
            updateAddonNumbers();
        });

        // Delegate removal of addon rows
        addonList.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.addon-card-remove-btn');
            if (removeBtn) {
                if (removeBtn.disabled) return;
                const card = removeBtn.closest('.addon-row-item, .addon-card');
                if (card) {
                    card.remove();
                    updateAddonNumbers();
                }
            }
        });

        // Delegate live image file preview
        addonList.addEventListener('change', (e) => {
            if (e.target && e.target.classList.contains('addon-file-input')) {
                const file = e.target.files && e.target.files[0];
                const card = e.target.closest('.addon-row-item, .addon-card');
                const previewBox = card ? card.querySelector('.addon-img-preview-box') : null;
                if (file && previewBox) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        previewBox.innerHTML = `<img src="${evt.target.result}" alt="Preview" style="width:100%; height:100%; object-fit:cover;" />`;
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        // Update titles on input
        addonList.querySelectorAll('[name="addon-name[]"]').forEach(input => {
            input.addEventListener('input', () => updateAddonNumbers());
        });
    }

    // ── Organizer contact masking ──────────────────────────
    attachPhoneMask(organizerPhone);
    if (organizerSocial) {
        organizerSocial.addEventListener('blur', () => {
            if (!validateSocial(organizerSocial.value)) organizerSocial.classList.add('invalid');
            else organizerSocial.classList.remove('invalid');
        });
    }

    // ── Input Invalid Styling & Helpers ───────────────────
    function clearInvalidErrors() {
        document.querySelectorAll('.invalid').forEach(el => el.classList.remove('invalid'));
        document.querySelectorAll('.has-field-error').forEach(el => el.classList.remove('has-field-error'));
        if (authMessage) {
            authMessage.style.display = 'none';
            authMessage.innerHTML = '';
            authMessage.className = 'auth-message';
        }
    }

    function markInvalid(input, errorText) {
        if (!input) return;
        input.classList.add('invalid');

        // Handle flatpickr inputs
        if (input._flatpickr && input._flatpickr.altInput) {
            input._flatpickr.altInput.classList.add('invalid');
        }

        const wrapper = input.closest('.picker-wrapper') || input.closest('.event-field-group') || input.closest('label');
        if (wrapper) wrapper.classList.add('has-field-error');

        showMessage(errorText, 'error');

        if (input._flatpickr && input._flatpickr.altInput) {
            input._flatpickr.altInput.focus();
        } else {
            input.focus();
        }
    }

    // Auto-clear invalid state on input / change
    document.addEventListener('input', e => {
        if (e.target && e.target.classList.contains('invalid')) {
            e.target.classList.remove('invalid');
            const wrapper = e.target.closest('.picker-wrapper') || e.target.closest('label');
            if (wrapper) wrapper.classList.remove('has-field-error');
        }
    });

    document.addEventListener('change', e => {
        if (e.target && e.target.classList.contains('invalid')) {
            e.target.classList.remove('invalid');
            const wrapper = e.target.closest('.picker-wrapper') || e.target.closest('label');
            if (wrapper) wrapper.classList.remove('has-field-error');
        }
    });

    // ── Client-side validation before native form submit ───
    if (form) {
        form.addEventListener('submit', e => {
            clearInvalidErrors();

            // Finalize the custom-url slug value before submit
            if (customUrlInput) {
                const raw = customUrlInput.value.trim();
                customUrlInput.value = generateUniqueSlug(raw || (eventNameInput ? eventNameInput.value : ''));
            }

            // Check Event Name
            if (eventNameInput && !eventNameInput.value.trim()) {
                e.preventDefault();
                markInvalid(eventNameInput, 'Please enter an event title/name.');
                return;
            }

            // Check Date & Time
            const eventDate = document.getElementById('event-date');
            const eventTime = document.getElementById('event-time');
            if (eventDate && !eventDate.value.trim()) {
                e.preventDefault();
                markInvalid(eventDate, 'Please select an event date.');
                if (eventDate._flatpickr) eventDate._flatpickr.open();
                return;
            }
            if (eventTime && !eventTime.value.trim()) {
                e.preventDefault();
                markInvalid(eventTime, 'Please select an event start time.');
                if (eventTime._flatpickr) eventTime._flatpickr.open();
                return;
            }

            // Check Category
            const eventCategory = document.getElementById('event-category');
            if (eventCategory && !eventCategory.value) {
                e.preventDefault();
                markInvalid(eventCategory, 'Please select an event category.');
                return;
            }

            // Check Venue & City
            const eventVenue = document.getElementById('event-venue');
            const eventCity = document.getElementById('event-city');
            if (eventVenue && !eventVenue.value.trim()) {
                e.preventDefault();
                markInvalid(eventVenue, 'Please specify the event venue address.');
                return;
            }
            if (eventCity && !eventCity.value.trim()) {
                e.preventDefault();
                markInvalid(eventCity, 'Please specify the event city.');
                return;
            }

            // Check Description
            const eventDesc = document.getElementById('event-description');
            if (eventDesc && !eventDesc.value.trim()) {
                e.preventDefault();
                markInvalid(eventDesc, 'Please provide an event description.');
                return;
            }

            // Check Ticket Tiers
            if (ticketList) {
                const ticketRows = ticketList.querySelectorAll('.ticket-row-item, .ticket-row');
                if (ticketRows.length === 0) {
                    e.preventDefault();
                    showMessage('You must add at least one ticket tier for this event.', 'error');
                    if (addTicketBtn) addTicketBtn.focus();
                    return;
                }

                for (let i = 0; i < ticketRows.length; i++) {
                    const row = ticketRows[i];
                    const nameInput = row.querySelector('[name="ticket-name[]"]');
                    const priceInput = row.querySelector('[name="ticket-price[]"]');
                    const qtyInput = row.querySelector('[name="ticket-qty[]"]');

                    if (!nameInput || !nameInput.value.trim()) {
                        e.preventDefault();
                        markInvalid(nameInput, `Ticket tier #${i + 1} requires a tier name (e.g. VIP, General Admission).`);
                        return;
                    }
                    if (!priceInput || priceInput.value === '' || parseFloat(priceInput.value) < 0) {
                        e.preventDefault();
                        markInvalid(priceInput, `Ticket tier "${nameInput.value}" requires a valid price (0.00 or greater).`);
                        return;
                    }
                    if (!qtyInput || qtyInput.value === '' || parseInt(qtyInput.value, 10) < 1) {
                        e.preventDefault();
                        markInvalid(qtyInput, `Ticket tier "${nameInput.value}" must have a capacity of at least 1 ticket.`);
                        return;
                    }
                }
            }

            // Check Organizer contacts
            if (organizerPhone && !validatePhone(organizerPhone.value)) {
                e.preventDefault();
                markInvalid(organizerPhone, 'Organizer phone number appears invalid. Please check the digits.');
                return;
            }
            if (organizerSocial && !validateSocial(organizerSocial.value)) {
                e.preventDefault();
                markInvalid(organizerSocial, 'Organizer website/social link looks invalid. Use a full URL (https://...) or handle (@handle).');
                return;
            }

            // Validate guest contacts
            if (guestList) {
                const rows = guestList.querySelectorAll('.guest-row');
                for (const row of rows) {
                    const phone = row.querySelector('[name="guest-phone[]"]');
                    const social = row.querySelector('[name="guest-social[]"]');
                    if (phone && !validatePhone(phone.value)) {
                        e.preventDefault();
                        markInvalid(phone, 'One or more guest phone numbers look invalid.');
                        return;
                    }
                    if (social && !validateSocial(social.value)) {
                        e.preventDefault();
                        markInvalid(social, 'One or more guest social links look invalid.');
                        return;
                    }
                }
            }

            // If all checks pass, show loading animation, disable submit button, and submit
            const isEdit = form.id === 'edit-event-form';
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="btn-spinner"></span> ${isEdit ? 'Saving Changes...' : 'Publishing Event...'}`;
            }

            showProcessingOverlay(
                isEdit ? 'Updating Event Details' : 'Publishing Your Event',
                isEdit ? 'Saving your updated event information and syncing ticket tiers. Please wait...' : 'Uploading event media and finalizing ticket tiers. Please wait...'
            );
        });
    }

    // ── Helpers ────────────────────────────────────────────
    function showProcessingOverlay(title, desc) {
        let overlay = document.querySelector('.evently-processing-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'evently-processing-overlay';
            overlay.innerHTML = `
                <div class="evently-processing-card">
                    <div class="evently-spinner-ring"></div>
                    <h3 class="evently-processing-title">${title || 'Processing...'}</h3>
                    <p class="evently-processing-desc">${desc || 'Please wait a moment while we process your request.'}</p>
                </div>
            `;
            document.body.appendChild(overlay);
        } else {
            const titleEl = overlay.querySelector('.evently-processing-title');
            const descEl = overlay.querySelector('.evently-processing-desc');
            if (titleEl) titleEl.textContent = title || 'Processing...';
            if (descEl) descEl.textContent = desc || 'Please wait a moment.';
        }
        requestAnimationFrame(() => {
            overlay.classList.add('active');
        });
    }

    function showMessage(text, type = 'success') {
        if (!authMessage) return;
        const icon = type === 'error' ? '⚠️ ' : (type === 'success' ? '✅ ' : 'ℹ️ ');
        authMessage.innerHTML = `<span>${icon}</span> <span>${text}</span>`;
        authMessage.className = 'auth-message ' + type;
        authMessage.style.display = 'flex';
        authMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // ── Flatpickr Date & Time Pickers ─────────────────────
    // Initializes premium calendar and time dropdowns that match
    // the Eventlyy design system. Date format is PHP-compatible (Y-m-d).
    if (typeof flatpickr !== 'undefined') {
        // Detect current theme for initial config
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';

        // Date Picker
        var dateInput = document.getElementById('event-date');
        var datePicker = null;
        if (dateInput) {
            datePicker = flatpickr(dateInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',        // Display: "July 15, 2026"
                minDate: 'today',
                disableMobile: true,         // Always use the custom dropdown, even on mobile
                animate: true,
                monthSelectorType: 'dropdown',
                prevArrow: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>',
                nextArrow: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>',
                onReady: function (selectedDates, dateStr, instance) {
                    // Add custom class for Eventlyy theming
                    instance.calendarContainer.classList.add('evently-flatpickr');
                }
            });
        }

        // Time Picker
        var timeInput = document.getElementById('event-time');
        var timePicker = null;
        if (timeInput) {
            timePicker = flatpickr(timeInput, {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                altInput: true,
                altFormat: 'h:i K',         // Display: "7:30 PM"
                time_24hr: false,
                minuteIncrement: 15,
                disableMobile: true,
                animate: true,
                onReady: function (selectedDates, dateStr, instance) {
                    instance.calendarContainer.classList.add('evently-flatpickr');
                }
            });
        }

        // Make SVG icons clickable to open the picker
        document.querySelectorAll('.picker-wrapper').forEach(function (wrapper) {
            var icon = wrapper.querySelector('.picker-icon');
            var input = wrapper.querySelector('input');
            if (!icon || !input) return;

            icon.style.pointerEvents = 'auto';
            icon.style.cursor = 'pointer';

            icon.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                // Find the flatpickr instance on this input
                var fp = input._flatpickr;
                if (fp) {
                    fp.open();
                } else {
                    input.focus();
                }
            });
        });

        // Listen for theme changes to update the picker styling dynamically
        var themeObserver = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.attributeName === 'data-theme') {
                    // Flatpickr containers auto-inherit from CSS variables,
                    // but we toggle a class for any extra overrides
                    var containers = document.querySelectorAll('.evently-flatpickr');
                    var nowDark = document.documentElement.getAttribute('data-theme') === 'dark';
                    containers.forEach(function (el) {
                        el.classList.toggle('evently-dark', nowDark);
                    });
                }
            });
        });
        themeObserver.observe(document.documentElement, { attributes: true });
    }

    // ── Recurrence Settings Handler ─────────────────────────
    const recurringToggle = document.getElementById('is-recurring-toggle');
    const recurrencePanel = document.getElementById('recurrence-options-panel');
    const recurrenceType = document.getElementById('recurrence-type');
    const recurrenceInterval = document.getElementById('recurrence-interval');
    const recurrenceIntervalUnit = document.getElementById('recurrence-interval-unit');
    const recurrenceEndType = document.getElementById('recurrence-end-type');
    const groupCount = document.getElementById('group-recurrence-count');
    const groupEndDate = document.getElementById('group-recurrence-end-date');
    const recurrenceCount = document.getElementById('recurrence-count');
    const recurrenceEndDate = document.getElementById('recurrence-end-date');
    const previewText = document.getElementById('recurrence-preview-text');

    if (recurringToggle && recurrencePanel) {
        let recEndPicker = null;
        if (typeof flatpickr !== 'undefined' && recurrenceEndDate) {
            recEndPicker = flatpickr(recurrenceEndDate, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',
                minDate: 'today',
                disableMobile: true,
                animate: true,
                monthSelectorType: 'dropdown',
                prevArrow: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>',
                nextArrow: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>',
                onReady: function (selectedDates, dateStr, instance) {
                    instance.calendarContainer.classList.add('evently-flatpickr');
                },
                onChange: function () {
                    updateRecurrencePreview();
                }
            });
        }

        recurringToggle.addEventListener('change', () => {
            recurrencePanel.style.display = recurringToggle.checked ? 'block' : 'none';
            if (recurringToggle.checked) {
                updateRecurrencePreview();
            }
        });

        function updateUnitLabel() {
            if (!recurrenceType || !recurrenceIntervalUnit) return;
            const type = recurrenceType.value;
            if (type === 'daily') {
                recurrenceIntervalUnit.textContent = 'day(s)';
            } else if (type === 'weekly' || type === 'biweekly') {
                recurrenceIntervalUnit.textContent = type === 'biweekly' ? 'cycle(s) (2 wks each)' : 'week(s)';
            } else if (type === 'monthly') {
                recurrenceIntervalUnit.textContent = 'month(s)';
            }
        }

        function updateEndConditionFields() {
            if (!recurrenceEndType) return;
            const isDate = recurrenceEndType.value === 'date';
            if (groupCount) groupCount.style.display = isDate ? 'none' : 'block';
            if (groupEndDate) groupEndDate.style.display = isDate ? 'block' : 'none';
        }

        function updateRecurrencePreview() {
            if (!previewText) return;
            const eventDateVal = document.getElementById('event-date')?.value;
            if (!eventDateVal) {
                previewText.textContent = 'Select an initial event date above to view the schedule preview.';
                return;
            }

            const type = recurrenceType?.value || 'weekly';
            const interval = parseInt(recurrenceInterval?.value, 10) || 1;
            const endType = recurrenceEndType?.value || 'count';
            const count = parseInt(recurrenceCount?.value, 10) || 4;
            const endDateVal = recurrenceEndDate?.value;

            const typeNames = {
                daily: interval === 1 ? 'daily' : `every ${interval} days`,
                weekly: interval === 1 ? 'weekly' : `every ${interval} weeks`,
                biweekly: `every ${interval * 2} weeks`,
                monthly: interval === 1 ? 'monthly' : `every ${interval} months`
            };

            const typeLabel = typeNames[type] || 'weekly';

            if (endType === 'count') {
                previewText.innerHTML = `Repeats <strong>${typeLabel}</strong> for a total of <strong>${count} occurrences</strong> starting ${eventDateVal}.`;
            } else if (endDateVal) {
                previewText.innerHTML = `Repeats <strong>${typeLabel}</strong> starting ${eventDateVal} until <strong>${endDateVal}</strong>.`;
            } else {
                previewText.innerHTML = `Repeats <strong>${typeLabel}</strong> starting ${eventDateVal}. Please pick an end date.`;
            }
        }

        if (recurrenceType) {
            recurrenceType.addEventListener('change', () => {
                updateUnitLabel();
                updateRecurrencePreview();
            });
        }

        if (recurrenceInterval) {
            recurrenceInterval.addEventListener('input', updateRecurrencePreview);
        }

        if (recurrenceEndType) {
            recurrenceEndType.addEventListener('change', () => {
                updateEndConditionFields();
                updateRecurrencePreview();
            });
        }

        if (recurrenceCount) {
            recurrenceCount.addEventListener('input', updateRecurrencePreview);
        }

        const initialDateInput = document.getElementById('event-date');
        if (initialDateInput) {
            initialDateInput.addEventListener('change', () => {
                if (recEndPicker) {
                    recEndPicker.set('minDate', initialDateInput.value);
                }
                updateRecurrencePreview();
            });
        }

        updateUnitLabel();
        updateEndConditionFields();
        if (recurringToggle.checked) {
            updateRecurrencePreview();
        }
    }
});
