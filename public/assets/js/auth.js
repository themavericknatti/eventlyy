/*
========================================================
 Project: Evently
 File: auth.js
 Author: Celio Natti
 Version: 1.0.0
 Created: 2026-07-02
 Description: Client-side JavaScript for authentication forms.
========================================================
*/

// Minimal client-side helpers for authentication forms (optional)
(function(){
  // Password matching helper for Reset Password page
  const resetForm = document.getElementById('reset-form');
  if(resetForm){
    resetForm.addEventListener('submit', function(e) {
      const pw = document.getElementById('reset-password');
      const pwConfirm = document.getElementById('reset-password-confirm');
      
      if(pw && pwConfirm && pw.value !== pwConfirm.value) {
        e.preventDefault();
        
        // Find or create notification container
        const card = resetForm.closest('.auth-card');
        if(card) {
          let msg = card.querySelector('.auth-message');
          if(!msg) {
            msg = document.createElement('div');
            msg.className = 'auth-message error';
            card.insertBefore(msg, card.querySelector('.auth-card-header').nextSibling);
          }
          msg.textContent = 'Passwords do not match. Please verify and try again.';
          msg.style.display = 'block';
        }
      }
    });
  }
})();
