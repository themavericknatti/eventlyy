<?php

/**
 * Paystack Page
 *
 * This file contains the HTML markup for the Paystack payment page of the application.
 *
 * @package Evently
 * @subpackage Views/Paystack
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

?>

@extends('layouts/main')

@section('style')
<style>
    .redirect-page {
        display: grid;
        place-items: center;
        min-height: 80vh;
        padding: 2rem 1rem;
        text-align: center
    }

    .redirect-card {
        max-width: 480px;
        width: 100%;
        padding: 2.5rem 2rem;
        border-radius: 28px;
        background: rgb(var(--card));
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.10);
        border: 1px solid rgba(var(--muted), 0.08);
        animation: fade-up var(--duration-slow) var(--ease-out) both
    }

    .provider-icon {
        width: 72px;
        height: 72px;
        border-radius: 20px;
        display: grid;
        place-items: center;
        margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, rgba(35, 182, 107, 0.15), rgba(35, 182, 107, 0.05));
        border: 1px solid rgba(35, 182, 107, 0.2);
        animation: fade-up var(--duration-slow) var(--ease-out) 80ms both
    }

    .provider-icon svg {
        width: 36px;
        height: 36px;
        color: rgb(35, 182, 107)
    }

    .redirect-card h1 {
        font-size: clamp(1.5rem, 3vw, 2rem);
        margin: 0 0 .5rem;
        letter-spacing: -0.02em;
        animation: fade-up var(--duration-slow) var(--ease-out) 140ms both
    }

    .redirect-card .lede {
        color: rgb(var(--muted));
        margin: 0 0 1.5rem;
        font-size: 1rem;
        animation: fade-up var(--duration-slow) var(--ease-out) 200ms both
    }

    .order-badge {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .55rem 1.15rem;
        border-radius: 999px;
        background: rgba(var(--muted), 0.06);
        border: 1px solid rgba(var(--muted), 0.1);
        font-size: .95rem;
        font-weight: 600;
        color: rgb(var(--text));
        margin-bottom: 1.75rem;
        animation: fade-up var(--duration-slow) var(--ease-out) 260ms both
    }

    .order-badge span {
        color: rgb(var(--muted));
        font-weight: 400
    }

    .redirect-card .checkout-btn {
        animation: fade-up var(--duration-slow) var(--ease-out) 320ms both
    }

    .redirect-hint {
        color: rgb(var(--muted));
        font-size: .9rem;
        margin: 1rem 0 0;
        animation: fade-up var(--duration-slow) var(--ease-out) 380ms both
    }

    .redirect-card .checkout-btn {
        background: linear-gradient(135deg, rgb(35, 182, 107), rgb(60, 200, 130));
        box-shadow: 0 12px 36px rgba(35, 182, 107, 0.28)
    }

    .redirect-card .checkout-btn:hover {
        filter: brightness(1.06);
        transform: translateY(-2px);
        box-shadow: 0 16px 40px rgba(35, 182, 107, 0.35)
    }

    .provider-label {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .35rem .75rem;
        border-radius: 999px;
        background: rgba(35, 182, 107, 0.08);
        color: rgb(35, 182, 107);
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 1rem;
        animation: fade-up var(--duration-slow) var(--ease-out) 60ms both
    }

    .security-note {
        display: flex;
        align-items: center;
        gap: .5rem;
        justify-content: center;
        margin-top: 1.25rem;
        padding: .65rem 1rem;
        border-radius: 12px;
        background: rgba(var(--muted), 0.04);
        border: 1px solid rgba(var(--muted), 0.08);
        font-size: .85rem;
        color: rgb(var(--muted));
        animation: fade-up var(--duration-slow) var(--ease-out) 440ms both
    }

    .security-note svg {
        flex-shrink: 0;
        width: 16px;
        height: 16px;
        color: rgb(var(--success))
    }

    @keyframes pulse-ring {
        0% {
            box-shadow: 0 0 0 0 rgba(35, 182, 107, 0.25)
        }

        70% {
            box-shadow: 0 0 0 14px rgba(35, 182, 107, 0)
        }

        100% {
            box-shadow: 0 0 0 0 rgba(35, 182, 107, 0)
        }
    }

    .provider-icon {
        animation: fade-up var(--duration-slow) var(--ease-out) 80ms both, pulse-ring 2.5s ease infinite 1s
    }
</style>
@endsection

@section('title')
Paystack - Complete Your Payment.
@endsection

@section('content')
<div class="redirect-card">
    <div class="provider-label">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="9" fill="currentColor" />
        </svg>
        Paystack Gateway
    </div>
    <div class="provider-icon">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" fill="none" />
            <path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
        </svg>
    </div>
    <h1>Complete your payment</h1>
    <p class="lede">You're being securely redirected to Paystack to process your payment.</p>
    <div class="order-badge"><span>Order</span> <strong id="order-id">#12345</strong></div>
    <button id="complete" class="checkout-btn">Simulate successful payment</button>
    <p class="redirect-hint">This is a mock payment page for demo purposes.</p>
    <div class="security-note">
        <svg viewBox="0 0 24 24" fill="none">
            <path d="M12 2L3 7v5c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5z" fill="currentColor" />
        </svg>
        256-bit SSL encrypted · Secure checkout
    </div>
</div>
@endsection

@section('script')
<script>
    document.getElementById('complete').addEventListener('click', () => {
        const btn = document.getElementById('complete');
        btn.textContent = 'Processing…';
        btn.style.pointerEvents = 'none';
        btn.style.opacity = '0.7';
        setTimeout(() => {
            window.location.href = 'success?provider=paystack&order=12345';
        }, 1200);
    });
</script>
@endsection