<?php

/**
 * Event Feedback & Patron Review — Archival Edition
 *
 * Vintage testimonial card: letterpress typography, antique star rating,
 * parchment form fields, and responsive multi-device layout.
 *
 * @package Eventlyy
 * @subpackage Views/Feedback
 * @author Celio Natti
 * @version 2.0.0
 */

$siteTitle = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/main')

@section('title')
Rate Event: {{ $event->name }} &mdash; Patron Review &bull; <?= e($siteTitle) ?>
@endsection

@section('content')
<div class="vn-fb-wrapper">
    <div class="vn-fb-card">
        
        <header class="vn-fb-header">
            <span class="vn-fb-stamp">✦ Patron Testimonial &bull; Event Inscription ✦</span>
            <h1 class="vn-fb-title">Rate &amp; Review Experience</h1>
            <p class="vn-fb-subtitle">
                Share your impressions for <strong>{{ $event->name }}</strong> convened by <strong>{{ $event->organizer_name ?? 'the host' }}</strong>
            </p>
        </header>

        <?php $submitted = isset($submitted) ? $submitted : false; ?>
        <?php if ($submitted): ?>
            <div class="vn-fb-success">
                <div class="vn-fb-success-seal">✓</div>
                <h2 class="vn-fb-success-title">Gratitude for Your Feedback</h2>
                <p class="vn-fb-success-desc">
                    Your assessment has been officially registered in the event chronicle and assists hosts in honing subsequent gatherings.
                </p>
                <?php $evtId = !empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id); ?>
                <a href="{{ route('event/' . $evtId) }}" class="vn-fb-btn" style="text-decoration:none;">
                    Return to Event Register &rarr;
                </a>
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
                <div class="vn-fb-errors">
                    <ul>
                        <?php foreach ($flatErrors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php $evtId = !empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id); ?>
            <form method="POST" action="<?= route('event/' . $evtId . '/feedback') ?>">
                <?= csrf_field() ?>

                <!-- Star Rating Console -->
                <div class="vn-fb-stars-wrap">
                    <span class="vn-fb-stars-label">Your Accord &bull; Rating (1 to 5 Stars)</span>
                    <div class="vn-fb-star-selector">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= (($_POST['rating'] ?? '5') == $i) ? 'checked' : '' ?> style="display: none;">
                            <label for="star<?= $i ?>" class="vn-fb-star-lbl star-lbl" data-value="<?= $i ?>">★</label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="vn-fb-field">
                    <label class="vn-fb-label" for="att-name">Your Full Name</label>
                    <input type="text" id="att-name" name="attendee_name" required placeholder="e.g. Eleanor Vance" value="<?= e($_POST['attendee_name'] ?? '') ?>" class="vn-fb-input">
                </div>

                <div class="vn-fb-field">
                    <label class="vn-fb-label" for="att-email">Ticket Holder Email Address</label>
                    <input type="email" id="att-email" name="attendee_email" required placeholder="e.g. eleanor@example.com" value="<?= e($_POST['attendee_email'] ?? '') ?>" class="vn-fb-input">
                    <small class="vn-fb-hint">Must match the email address associated with your issued admission pass.</small>
                </div>

                <div class="vn-fb-field">
                    <label class="vn-fb-label" for="att-comment">Patron Testimonial &amp; Comments (Optional)</label>
                    <textarea id="att-comment" name="comment" rows="4" placeholder="Detail what resonated with you or suggestions for subsequent occasions..." class="vn-fb-textarea"><?= e($_POST['comment'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="vn-fb-btn">
                    <span>Inscribe Testimonial &amp; Accord</span>
                    <span>&rarr;</span>
                </button>
            </form>
        <?php endif; ?>

    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const labels = Array.from(document.querySelectorAll('.star-lbl'));
    const inputs = Array.from(document.querySelectorAll('input[name="rating"]'));

    function updateStars(val) {
        labels.forEach(lbl => {
            const v = parseInt(lbl.getAttribute('data-value'), 10);
            if (v <= val) {
                lbl.style.color = 'var(--vn-brass)';
                lbl.style.textShadow = '0 0 12px var(--vn-brass-glow)';
            } else {
                lbl.style.color = 'var(--vn-border-dark)';
                lbl.style.textShadow = 'none';
            }
        });
    }

    // Set initial
    const checked = document.querySelector('input[name="rating"]:checked');
    if (checked) updateStars(parseInt(checked.value, 10));

    labels.forEach(lbl => {
        lbl.addEventListener('mouseover', function() {
            updateStars(parseInt(this.getAttribute('data-value'), 10));
        });

        lbl.addEventListener('click', function() {
            const v = parseInt(this.getAttribute('data-value'), 10);
            const radio = document.getElementById('star' + v);
            if (radio) radio.checked = true;
            updateStars(v);
        });
    });

    const selector = document.querySelector('.vn-fb-star-selector');
    if (selector) {
        selector.addEventListener('mouseleave', function() {
            const currChecked = document.querySelector('input[name="rating"]:checked');
            if (currChecked) updateStars(parseInt(currChecked.value, 10));
        });
    }
});
</script>
@endsection
