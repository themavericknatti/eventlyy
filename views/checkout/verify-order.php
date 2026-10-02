<?php
/**
 * View: Public Self-Service Order & Ticket Verification (Disconnection Protection)
 *
 * Allows buyers whose browsers crashed, closed, or timed out during payment
 * to immediately verify their Paystack charge, retrieve ticket codes, and resend their email passes.
 */
?>
<div class="container" style="max-width: 680px; margin: 3.5rem auto; padding: 0 1.25rem;">
    <div style="background: rgb(var(--card)); border: 1px solid rgba(var(--muted), 0.15); border-radius: 20px; padding: 2.5rem 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.06);">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 64px; height: 64px; background: rgba(99, 102, 241, 0.12); color: #6366f1; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 1rem;">
                🔍
            </div>
            <h1 style="font-size: 1.65rem; font-weight: 800; color: rgb(var(--text)); margin: 0 0 0.5rem;">
                Verify Payment & Retrieve Tickets
            </h1>
            <p style="font-size: 0.9rem; color: rgb(var(--muted)); margin: 0; line-height: 1.5;">
                Were you debited by your bank or Paystack, but got disconnected before receiving your tickets? Enter your payment reference below to instantly activate and retrieve your passes.
            </p>
        </div>

        <!-- Flash Messages -->
        <?php if ($flashSuccess = \Ace\Application::$app->session->getFlash('success')): ?>
            <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); color: #065f46; padding: 1rem 1.25rem; border-radius: 12px; font-size: 0.88rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.65rem;">
                <span>✅</span>
                <span><?= e($flashSuccess) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flashError = \Ace\Application::$app->session->getFlash('error')): ?>
            <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #991b1b; padding: 1rem 1.25rem; border-radius: 12px; font-size: 0.88rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.65rem;">
                <span>⚠️</span>
                <span><?= e($flashError) ?></span>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="<?= route('order/verify') ?>" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <?= csrf_field() ?>

            <div>
                <label for="reference" style="display: block; font-size: 0.85rem; font-weight: 700; color: rgb(var(--text)); margin-bottom: 0.45rem;">
                    Paystack Payment Reference or Code
                </label>
                <input 
                    type="text" 
                    id="reference" 
                    name="reference" 
                    value="<?= e($reference ?? '') ?>" 
                    placeholder="e.g. PAY_66d5..., EV-..., or your bank transaction ref" 
                    required
                    style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid rgba(var(--muted), 0.25); border-radius: 12px; font-size: 0.95rem; font-family: monospace; background: rgb(var(--card)); color: rgb(var(--text)); box-sizing: border-box;"
                >
                <span style="display: block; font-size: 0.75rem; color: rgb(var(--muted)); margin-top: 0.35rem;">
                    You can find your transaction reference in your Paystack payment receipt or bank debit SMS/email.
                </span>
            </div>

            <button 
                type="submit" 
                class="admin-action-btn primary" 
                style="padding: 0.85rem 1.5rem; font-size: 0.95rem; font-weight: 700; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border: none; cursor: pointer; background: rgb(var(--brand)); color: #fff;"
            >
                🔄 Verify with Paystack & Fetch Tickets
            </button>
        </form>

        <!-- Information Accordion / Tips -->
        <div style="margin-top: 2.25rem; padding-top: 1.5rem; border-top: 1px solid rgba(var(--muted), 0.12); display: flex; flex-direction: column; gap: 0.75rem;">
            <div style="font-size: 0.82rem; color: rgb(var(--muted)); line-height: 1.5;">
                <strong style="color: rgb(var(--text)); display: block; margin-bottom: 0.2rem;">💡 How does automatic protection work?</strong>
                Our platform connects directly to Paystack's real-time verification gateway. Once confirmed, your tickets are instantly generated and automatically delivered to your email with scannable QR passes.
            </div>
            <div style="font-size: 0.82rem; color: rgb(var(--muted)); line-height: 1.5;">
                <strong style="color: rgb(var(--text)); display: block; margin-bottom: 0.2rem;">📩 Need assistance?</strong>
                If your money was debited but cannot be verified, please email our support team with your receipt or transaction code for immediate assistance.
            </div>
        </div>

    </div>
</div>
