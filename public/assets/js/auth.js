/**
 * Eventlyy - Authentication Suite JavaScript
 * Handles password toggling, interactive role selection, live password strength meter,
 * match verification, and double-submit prevention with theme awareness.
 *
 * @version 2.0.0
 * @author Celio Natti
 */

(function () {
    'use strict';

    // 1. Password Visibility Toggle (Show / Hide Password)
    function initPasswordToggles() {
        document.querySelectorAll('.password-toggle-btn').forEach(function (btn) {
            if (btn.dataset.initialized) return;
            btn.dataset.initialized = 'true';

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const wrapper = btn.closest('.password-input-wrapper');
                if (!wrapper) return;
                const input = wrapper.querySelector('input');
                if (!input) return;

                const isCurrentlyPassword = input.type === 'password';
                input.type = isCurrentlyPassword ? 'text' : 'password';

                const eyeShow = btn.querySelector('.eye-show');
                const eyeHide = btn.querySelector('.eye-hide');

                if (eyeShow && eyeHide) {
                    eyeShow.style.display = isCurrentlyPassword ? 'none' : 'block';
                    eyeHide.style.display = isCurrentlyPassword ? 'block' : 'none';
                }

                const label = isCurrentlyPassword ? 'Hide password' : 'Show password';
                btn.setAttribute('aria-label', label);
                btn.setAttribute('aria-pressed', isCurrentlyPassword ? 'true' : 'false');
                btn.setAttribute('title', label);

                try {
                    const len = input.value.length;
                    input.setSelectionRange(len, len);
                    input.focus();
                } catch (err) {}
            });
        });
    }

    // 2. Interactive Role Picker (Attendee vs Event Host)
    function initRolePicker() {
        const picker = document.querySelector('.auth-role-picker');
        const roleInput = document.getElementById('reg-role');
        if (!picker || !roleInput) return;

        const options = picker.querySelectorAll('.role-card-option');
        options.forEach(function (opt) {
            opt.addEventListener('click', function () {
                const selectedRole = opt.dataset.role;
                if (!selectedRole) return;

                roleInput.value = selectedRole;

                options.forEach(function (el) {
                    const isSelected = el === opt;
                    el.classList.toggle('active', isSelected);
                    el.setAttribute('aria-checked', isSelected ? 'true' : 'false');
                });
            });

            // Keyboard accessibility
            opt.addEventListener('keydown', function (e) {
                if (e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    opt.click();
                }
            });
        });
    }

    // 3. Live Password Strength Meter
    function initPasswordStrengthMeter() {
        const pwInput = document.getElementById('reg-password') || document.getElementById('reset-password');
        const container = document.getElementById('pw-strength-container');
        const caption = document.getElementById('pw-strength-text');
        if (!pwInput || !container) return;

        const bars = container.querySelectorAll('.pw-bar');

        function evaluateStrength(val) {
            if (!val || val.length === 0) return { score: 0, text: 'Password strength' };
            let score = 0;
            if (val.length >= 8) score++;
            if (val.length >= 12) score++;
            if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score++;
            if (/\d/.test(val)) score++;
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(val)) score++;

            // Normalize score from 0 to 4
            let normalized = 1;
            if (score <= 1) normalized = 1;
            else if (score === 2) normalized = 2;
            else if (score === 3 || score === 4) normalized = 3;
            else if (score >= 5) normalized = 4;

            const labels = {
                0: 'Password strength',
                1: 'Weak: Add numbers, symbols & uppercase',
                2: 'Fair: Could be stronger',
                3: 'Good: Nice and secure',
                4: 'Excellent: Strong, battle-tested password'
            };

            return { score: normalized, text: labels[normalized] };
        }

        pwInput.addEventListener('input', function () {
            const val = pwInput.value;
            if (val.length === 0) {
                container.classList.remove('active');
                bars.forEach(b => b.className = 'pw-bar');
                if (caption) caption.textContent = 'Password strength';
                return;
            }

            container.classList.add('active');
            const result = evaluateStrength(val);

            bars.forEach(function (bar, idx) {
                bar.className = 'pw-bar';
                if (idx < result.score) {
                    bar.classList.add('fill-' + result.score);
                }
            });

            if (caption) {
                caption.textContent = result.text;
            }
        });
    }

    // 4. Live Password Match Verification
    function initPasswordMatchChecks() {
        // Register form
        const regPw = document.getElementById('reg-password');
        const regPwConfirm = document.getElementById('reg-password-confirmation');
        const regBadge = document.getElementById('pw-match-badge');

        if (regPw && regPwConfirm && regBadge) {
            const checkMatch = function () {
                if (!regPwConfirm.value) {
                    regBadge.style.display = 'none';
                    return;
                }
                regBadge.style.display = 'inline-flex';
                if (regPw.value === regPwConfirm.value) {
                    regBadge.className = 'pw-match-badge match';
                    regBadge.textContent = '✓ Passwords match';
                } else {
                    regBadge.className = 'pw-match-badge mismatch';
                    regBadge.textContent = 'Passwords do not match';
                }
            };

            regPwConfirm.addEventListener('input', checkMatch);
            regPw.addEventListener('input', function () {
                if (regPwConfirm.value) checkMatch();
            });
        }

        // Reset password form
        const resetPw = document.getElementById('reset-password');
        const resetPwConfirm = document.getElementById('reset-password-confirm');
        const resetBadge = document.getElementById('pw-match-badge');

        if (resetPw && resetPwConfirm && resetBadge) {
            const checkResetMatch = function () {
                if (!resetPwConfirm.value) {
                    resetBadge.style.display = 'none';
                    return;
                }
                resetBadge.style.display = 'inline-flex';
                if (resetPw.value === resetPwConfirm.value) {
                    resetBadge.className = 'pw-match-badge match';
                    resetBadge.textContent = '✓ Passwords match';
                } else {
                    resetBadge.className = 'pw-match-badge mismatch';
                    resetBadge.textContent = 'Passwords do not match';
                }
            };

            resetPwConfirm.addEventListener('input', checkResetMatch);
            resetPw.addEventListener('input', function () {
                if (resetPwConfirm.value) checkResetMatch();
            });
        }

        // Form submission checks
        ['register-form', 'reset-form'].forEach(function (formId) {
            const form = document.getElementById(formId);
            if (!form) return;

            form.addEventListener('submit', function (e) {
                const pw = form.querySelector('input[type="password"][name="password"]');
                const pwConfirm = form.querySelector('input[type="password"][name="passwordConfirm"], input[type="password"][name="password_confirm"]');

                if (pw && pwConfirm && pw.value !== pwConfirm.value) {
                    e.preventDefault();
                    showAuthMessage(form, 'Passwords do not match. Please verify both fields.', 'error');
                    pwConfirm.focus();
                }
            });
        });
    }

    // 5. Submit Button Double-Click Prevention & Loading State
    function initSubmitPrevention() {
        document.querySelectorAll('form').forEach(function (form) {
            if (!form.closest('.auth-card')) return;

            form.addEventListener('submit', function (e) {
                if (form.dataset.submitting === 'true') {
                    e.preventDefault();
                    return;
                }

                // If form is valid
                if (form.checkValidity && !form.checkValidity()) {
                    return;
                }

                const btn = form.querySelector('.auth-submit-btn');
                if (btn) {
                    form.dataset.submitting = 'true';
                    btn.classList.add('is-submitting');
                    btn.setAttribute('aria-busy', 'true');

                    // Fallback to restore button after 8 seconds in case of network issue
                    setTimeout(function () {
                        form.dataset.submitting = 'false';
                        btn.classList.remove('is-submitting');
                        btn.removeAttribute('aria-busy');
                    }, 8000);
                }
            });
        });
    }

    // 6. Inline Dynamic Alert Messenger
    function showAuthMessage(form, message, type) {
        const card = form.closest('.auth-card');
        if (!card) return;
        let msg = card.querySelector('.auth-message');
        if (!msg) {
            msg = document.createElement('div');
            msg.className = 'auth-message ' + type;
            const header = card.querySelector('.auth-card-header');
            if (header && header.nextSibling) {
                card.insertBefore(msg, header.nextSibling);
            } else {
                card.prepend(msg);
            }
        } else {
            msg.className = 'auth-message ' + type;
        }
        msg.textContent = message;
        msg.style.display = 'flex';
        try {
            msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch (e) {}
    }

    // Initialize all components
    function init() {
        initPasswordToggles();
        initRolePicker();
        initPasswordStrengthMeter();
        initPasswordMatchChecks();
        initSubmitPrevention();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
