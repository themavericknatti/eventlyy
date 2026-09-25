@extends('layouts/main')

@section('title')
Rate Event: {{ $event->name }} - Evently
@endsection

@section('content')
<div class="container" style="max-width: 640px; margin: 3rem auto; padding: 0 1rem;">
    <div class="auth-card" style="background: var(--card-bg, #ffffff); border-radius: 20px; padding: 2.5rem; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid rgba(var(--muted), 0.15);">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <span style="font-size: 3rem; display: block; margin-bottom: 0.5rem;">⭐</span>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-color, #0f172a); margin: 0 0 0.5rem;">Rate & Review Event</h1>
            <p style="color: var(--muted-color, #64748b); font-size: 0.95rem; margin: 0;">
                Share your feedback for <strong>{{ $event->name }}</strong> hosted by <strong>{{ $event->organizer_name ?? 'the host' }}</strong>
            </p>
        </div>

        <?php $submitted = isset($submitted) ? $submitted : false; ?>
        <?php if ($submitted): ?>
            <div style="text-align: center; padding: 2rem 1rem;">
                <div style="width: 64px; height: 64px; background: rgba(16, 185, 129, 0.15); color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem;">✓</div>
                <h2 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.5rem;">Thank You for Your Feedback!</h2>
                <p style="color: #64748b; margin-bottom: 1.5rem;">Your rating has been submitted successfully and will help improve future events.</p>
                <?php $evtId = !empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id); ?>
                <a href="{{ route('event/' . $evtId) }}" class="btn-primary" style="display: inline-block; padding: 0.75rem 1.75rem; border-radius: 12px; text-decoration: none; font-weight: 700;">Back to Event Page</a>
            </div>
        <?php else: ?>

            <?php
            $flatErrors = [];
            $rawErrors = isset($errors)
                ? (($errors instanceof \Ace\ViewData) ? $errors->toArray() : (array)$errors)
                : [];
            if (!empty($rawErrors)) {
                foreach ($rawErrors as $field => $errs) {
                    $errList = ($errs instanceof \Ace\ViewData) ? $errs->toArray() : (array)$errs;
                    foreach ($errList as $err) {
                        if (is_string($err) && !empty($err)) {
                            $flatErrors[] = $err;
                        }
                    }
                }
            }
            ?>
            <?php if (!empty($flatErrors)): ?>
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.9rem;">
                    <ul style="margin: 0; padding-left: 1.2rem;">
                        <?php foreach ($flatErrors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php $evtId = !empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id); ?>
            <form method="POST" action="<?= route('event/' . $evtId . '/feedback') ?>">
                <?= csrf_field() ?>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-weight: 700; margin-bottom: 0.4rem; font-size: 0.9rem;">Your Name</label>
                    <input type="text" name="attendee_name" required placeholder="Jane Doe" value="<?= e($_POST['attendee_name'] ?? '') ?>" class="form-input" style="width: 100%; padding: 0.75rem 1rem; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.95rem;">
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-weight: 700; margin-bottom: 0.4rem; font-size: 0.9rem;">Your Ticket Email</label>
                    <input type="email" name="attendee_email" required placeholder="jane@example.com" value="<?= e($_POST['attendee_email'] ?? '') ?>" class="form-input" style="width: 100%; padding: 0.75rem 1rem; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.95rem;">
                    <small style="color: #64748b; font-size: 0.8rem; margin-top: 0.25rem; display: block;">Must match the email address used to purchase or receive your ticket.</small>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 700; margin-bottom: 0.5rem; font-size: 0.9rem;">Rating (1 to 5 Stars)</label>
                    <div class="star-rating-selector" style="display: flex; gap: 0.5rem; justify-content: center; background: #f8fafc; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= (($_POST['rating'] ?? '5') == $i) ? 'checked' : '' ?> style="display: none;">
                            <label for="star<?= $i ?>" class="star-lbl" data-value="<?= $i ?>" style="font-size: 2.2rem; cursor: pointer; color: #cbd5e1; transition: color 0.15s ease;">★</label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 700; margin-bottom: 0.4rem; font-size: 0.9rem;">Your Feedback & Comments (Optional)</label>
                    <textarea name="comment" rows="4" placeholder="Tell us what you enjoyed or what could be improved..." class="form-input" style="width: 100%; padding: 0.75rem 1rem; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.95rem; resize: vertical;"><?= e($_POST['comment'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 0.85rem; border-radius: 12px; font-weight: 700; font-size: 1rem; background: #0B7366; color: white; border: none; cursor: pointer;">
                    Submit Feedback &amp; Rating
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const labels = Array.from(document.querySelectorAll('.star-lbl'));
    const inputs = Array.from(document.querySelectorAll('input[name="rating"]'));

    function updateStars(val) {
        labels.forEach(lbl => {
            const v = parseInt(lbl.getAttribute('data-value'));
            lbl.style.color = v <= val ? '#f59e0b' : '#cbd5e1';
        });
    }

    // Set initial
    const checked = document.querySelector('input[name="rating"]:checked');
    if (checked) updateStars(parseInt(checked.value));

    labels.forEach(lbl => {
        lbl.addEventListener('mouseover', function() {
            updateStars(parseInt(this.getAttribute('data-value')));
        });

        lbl.addEventListener('click', function() {
            const v = parseInt(this.getAttribute('data-value'));
            const radio = document.getElementById('star' + v);
            if (radio) radio.checked = true;
            updateStars(v);
        });
    });

    const selector = document.querySelector('.star-rating-selector');
    if (selector) {
        selector.addEventListener('mouseleave', function() {
            const currChecked = document.querySelector('input[name="rating"]:checked');
            if (currChecked) updateStars(parseInt(currChecked.value));
        });
    }
});
</script>
@endsection
