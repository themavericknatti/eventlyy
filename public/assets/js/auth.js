/*
========================================================
 Project: Eventlyy
 File: auth.js
 Author: Celio Natti
 Version: 1.1.0
 Description: Client-side JavaScript for authentication forms,
 password visibility toggle, and client validations.
========================================================
*/

(function() {
  // 1. Password Visibility Toggle (Show / Hide Password)
  function initPasswordToggles() {
    document.querySelectorAll('.password-toggle-btn').forEach(function(btn) {
      if (btn.dataset.initialized) return;
      btn.dataset.initialized = 'true';

      btn.addEventListener('click', function(e) {
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
          eyeShow.style.display = isCurrentlyPassword ? 'none' : 'inline-block';
          eyeHide.style.display = isCurrentlyPassword ? 'inline-block' : 'none';
        }

        const label = isCurrentlyPassword ? 'Hide password' : 'Show password';
        btn.setAttribute('aria-label', label);
        btn.setAttribute('aria-pressed', isCurrentlyPassword ? 'true' : 'false');
        btn.setAttribute('title', label);

        // Keep cursor position at the end of input
        try {
          const len = input.value.length;
          input.setSelectionRange(len, len);
        } catch (err) {}
      });
    });
  }

  // 2. Password matching helper for Reset Password & Register forms
  function initPasswordMatchChecks() {
    const resetForm = document.getElementById('reset-form');
    if (resetForm) {
      resetForm.addEventListener('submit', function(e) {
        const pw = document.getElementById('reset-password');
        const pwConfirm = document.getElementById('reset-password-confirm');

        if (pw && pwConfirm && pw.value !== pwConfirm.value) {
          e.preventDefault();
          showAuthMessage(resetForm, 'Passwords do not match. Please verify and try again.', 'error');
          pwConfirm.focus();
        }
      });
    }

    const regForm = document.getElementById('register-form');
    if (regForm) {
      regForm.addEventListener('submit', function(e) {
        const pw = document.getElementById('reg-password');
        const pwConfirm = document.getElementById('reg-password-confirmation');

        if (pw && pwConfirm && pw.value !== pwConfirm.value) {
          e.preventDefault();
          showAuthMessage(regForm, 'Passwords do not match. Please verify and try again.', 'error');
          pwConfirm.focus();
        }
      });
    }
  }

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
  }

  // Auto-init on DOMContentLoaded or immediately if already loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      initPasswordToggles();
      initPasswordMatchChecks();
    });
  } else {
    initPasswordToggles();
    initPasswordMatchChecks();
  }
})();
