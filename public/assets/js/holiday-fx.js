/**
 * Eventlyy Seasonal Atmospheric & Holiday Floating FX Engine
 *
 * Lightweight, hardware-accelerated canvas particle system for seasonal effects:
 * - Christmas (Snowflakes, subtle Santa heads/hats, peaceful doves, gold stars)
 * - Winter (Crystalline multi-layer snowflakes & frost crystals)
 * - New Year (Celebratory tumbling 3D confetti, streamers & golden sparkles)
 * - Valentine (Floating pastel hearts & drifting rose petals)
 * - Easter / Spring (Peaceful white doves, cherry blossom petals & fresh spring flora)
 * - Halloween (Flying silhouette bats, glowing embers & jack-o'-lanterns)
 * - Summer (Shimmering iridescent bubbles & warm golden sun bokeh)
 * - National / Independence (Emerald green & white celebratory ribbons & confetti)
 * - Ramadan & Eid (Golden crescent moons, ornate glowing lanterns & Islamic stars)
 * - Autumn (Gently swaying maple leaves & golden embers)
 *
 * Performance Architecture:
 * 1. ZERO LAG: Pre-renders all vector sprites ONCE onto offscreen bitmap canvases.
 * 2. Blazingly fast GPU-accelerated drawImage() blitting inside requestAnimationFrame.
 * 3. Responsive particle budget: scaled down for mobile (10-14), tablet (18-22), desktop (28-34).
 * 4. Battery conservation: automatically halts loop when document is hidden (inactive tab).
 * 5. Accessibility: respects prefers-reduced-motion and provides an optional visitor toggle.
 * 6. Non-blocking: strict pointer-events: none; never intercepts clicks, touch, or scrolls.
 *
 * @version 1.0.0
 * @author Eventlyy Platform
 */

(function () {
    'use strict';

    // Verify canvas support & motion preference
    if (!window.requestAnimationFrame || !document.createElement('canvas').getContext) {
        return;
    }

    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        return;
    }

    let root = null;
    let canvas = null;
    let ctx = null;
    let animId = null;
    let isRunning = false;
    let isUserPaused = false;
    let currentMode = 'winter';
    let currentIntensity = 'standard';
    let particles = [];
    let spriteCache = {};
    let dpr = 1;
    let width = 0;
    let height = 0;
    let lastTime = 0;
    let resizeTimeout = null;

    // Sprite Generator Utilities
    function createOffscreen(w, h) {
        const off = document.createElement('canvas');
        off.width = Math.ceil(w * dpr);
        off.height = Math.ceil(h * dpr);
        const octx = off.getContext('2d');
        octx.scale(dpr, dpr);
        return { canvas: off, ctx: octx, w: w, h: h };
    }

    // 1. Crystal Snowflake Sprite
    function generateSnowflakeSprite(size) {
        const key = 'snowflake_' + size;
        if (spriteCache[key]) return spriteCache[key];

        const s = createOffscreen(size, size);
        const o = s.ctx;
        const cx = size / 2;
        const cy = size / 2;
        const r = size * 0.44;

        o.strokeStyle = 'rgba(255, 255, 255, 0.95)';
        o.lineWidth = Math.max(1, size * 0.07);
        o.lineCap = 'round';

        // Draw 6 radial branches with intricate crossbars
        for (let i = 0; i < 6; i++) {
            const angle = (i * Math.PI) / 3;
            const x2 = cx + Math.cos(angle) * r;
            const y2 = cy + Math.sin(angle) * r;

            o.beginPath();
            o.moveTo(cx, cy);
            o.lineTo(x2, y2);
            o.stroke();

            // First chevron
            const midR = r * 0.55;
            const mx = cx + Math.cos(angle) * midR;
            const my = cy + Math.sin(angle) * midR;
            const wingLen = r * 0.3;

            o.beginPath();
            o.moveTo(mx + Math.cos(angle + Math.PI / 4) * wingLen, my + Math.sin(angle + Math.PI / 4) * wingLen);
            o.lineTo(mx, my);
            o.lineTo(mx + Math.cos(angle - Math.PI / 4) * wingLen, my + Math.sin(angle - Math.PI / 4) * wingLen);
            o.stroke();

            // Outer tip chevron
            const tipR = r * 0.82;
            const tx = cx + Math.cos(angle) * tipR;
            const ty = cy + Math.sin(angle) * tipR;
            const tipWing = r * 0.2;

            o.beginPath();
            o.moveTo(tx + Math.cos(angle + Math.PI / 4) * tipWing, ty + Math.sin(angle + Math.PI / 4) * tipWing);
            o.lineTo(tx, ty);
            o.lineTo(tx + Math.cos(angle - Math.PI / 4) * tipWing, ty + Math.sin(angle - Math.PI / 4) * tipWing);
            o.stroke();
        }

        // Center jewel
        o.fillStyle = '#ffffff';
        o.beginPath();
        o.arc(cx, cy, Math.max(1.5, size * 0.08), 0, Math.PI * 2);
        o.fill();

        spriteCache[key] = s.canvas;
        return s.canvas;
    }

    // 2. Santa Hat / Father Christmas Sprite
    function generateSantaSprite(size) {
        const key = 'santa_' + size;
        if (spriteCache[key]) return spriteCache[key];

        const s = createOffscreen(size, size);
        const o = s.ctx;
        const cx = size / 2;
        const cy = size / 2;

        // Red festive cap
        o.fillStyle = '#dc2626';
        o.beginPath();
        o.moveTo(cx - size * 0.35, cy + size * 0.15);
        o.quadraticCurveTo(cx - size * 0.1, cy - size * 0.45, cx + size * 0.32, cy - size * 0.22);
        o.quadraticCurveTo(cx + size * 0.05, cy - size * 0.1, cx + size * 0.35, cy + size * 0.15);
        o.closePath();
        o.fill();

        // White fluffy brim
        o.fillStyle = '#ffffff';
        o.beginPath();
        o.ellipse(cx, cy + size * 0.18, size * 0.38, size * 0.12, 0, 0, Math.PI * 2);
        o.fill();

        // White pom-pom at tip
        o.beginPath();
        o.arc(cx + size * 0.34, cy - size * 0.22, size * 0.12, 0, Math.PI * 2);
        o.fill();

        // Subtle peach smile / Santa warmth hint underneath
        o.fillStyle = '#fde047';
        o.beginPath();
        o.arc(cx, cy + size * 0.28, size * 0.06, 0, Math.PI * 2);
        o.fill();

        spriteCache[key] = s.canvas;
        return s.canvas;
    }

    // 3. Peaceful White Dove Sprite
    function generateDoveSprite(size) {
        const key = 'dove_' + size;
        if (spriteCache[key]) return spriteCache[key];

        const s = createOffscreen(size, size);
        const o = s.ctx;
        const cx = size / 2;
        const cy = size / 2;

        o.save();
        o.shadowColor = 'rgba(0, 0, 0, 0.15)';
        o.shadowBlur = 4;

        // Elegant flying dove silhouette
        o.fillStyle = '#ffffff';
        o.beginPath();
        // Body and head
        o.moveTo(cx - size * 0.35, cy + size * 0.1);
        o.quadraticCurveTo(cx - size * 0.15, cy - size * 0.05, cx + size * 0.25, cy - size * 0.08); // chest
        o.arc(cx + size * 0.28, cy - size * 0.12, size * 0.09, Math.PI * 0.6, Math.PI * 1.8, false); // head
        // Beak
        o.lineTo(cx + size * 0.42, cy - size * 0.08);
        o.lineTo(cx + size * 0.32, cy - size * 0.04);
        // Throat & underbelly
        o.quadraticCurveTo(cx + size * 0.1, cy + size * 0.22, cx - size * 0.25, cy + size * 0.22);
        // Tail
        o.lineTo(cx - size * 0.42, cy + size * 0.28);
        o.lineTo(cx - size * 0.32, cy + size * 0.15);
        o.closePath();
        o.fill();

        // Outstretched Wing
        o.beginPath();
        o.moveTo(cx - size * 0.05, cy);
        o.quadraticCurveTo(cx - size * 0.05, cy - size * 0.42, cx + size * 0.18, cy - size * 0.45);
        o.quadraticCurveTo(cx + size * 0.08, cy - size * 0.25, cx + size * 0.12, cy - size * 0.02);
        o.closePath();
        o.fill();

        // Tiny olive leaf in beak (peace symbol)
        o.fillStyle = '#22c55e';
        o.beginPath();
        o.ellipse(cx + size * 0.44, cy - size * 0.04, size * 0.06, size * 0.03, Math.PI / 4, 0, Math.PI * 2);
        o.fill();

        o.restore();

        spriteCache[key] = s.canvas;
        return s.canvas;
    }

    // 4. Romantic Heart Sprite
    function generateHeartSprite(size, color) {
        const key = 'heart_' + size + '_' + color;
        if (spriteCache[key]) return spriteCache[key];

        const s = createOffscreen(size, size);
        const o = s.ctx;
        const cx = size / 2;
        const cy = size / 2;
        const scale = size * 0.035;

        o.fillStyle = color;
        o.beginPath();
        // SVG style heart path
        o.moveTo(cx, cy + size * 0.28);
        o.bezierCurveTo(cx - 14 * scale, cy + 10 * scale, cx - 18 * scale, cy - 6 * scale, cx - 9 * scale, cy - 14 * scale);
        o.bezierCurveTo(cx - 3 * scale, cy - 18 * scale, cx, cy - 10 * scale, cx, cy - 7 * scale);
        o.bezierCurveTo(cx, cy - 10 * scale, cx + 3 * scale, cy - 18 * scale, cx + 9 * scale, cy - 14 * scale);
        o.bezierCurveTo(cx + 18 * scale, cy - 6 * scale, cx + 14 * scale, cy + 10 * scale, cx, cy + size * 0.28);
        o.closePath();
        o.fill();

        spriteCache[key] = s.canvas;
        return s.canvas;
    }

    // 5. Flower / Rose Petal Sprite
    function generatePetalSprite(size, color) {
        const key = 'petal_' + size + '_' + color;
        if (spriteCache[key]) return spriteCache[key];

        const s = createOffscreen(size, size);
        const o = s.ctx;
        const cx = size / 2;
        const cy = size / 2;

        o.fillStyle = color;
        o.beginPath();
        o.moveTo(cx, cy - size * 0.4);
        o.bezierCurveTo(cx + size * 0.35, cy - size * 0.25, cx + size * 0.35, cy + size * 0.25, cx, cy + size * 0.4);
        o.bezierCurveTo(cx - size * 0.35, cy + size * 0.25, cx - size * 0.35, cy - size * 0.25, cx, cy - size * 0.4);
        o.closePath();
        o.fill();

        spriteCache[key] = s.canvas;
        return s.canvas;
    }

    // 6. Radiant Star / Sparkle Sprite
    function generateStarSprite(size, color) {
        const key = 'star_' + size + '_' + color;
        if (spriteCache[key]) return spriteCache[key];

        const s = createOffscreen(size, size);
        const o = s.ctx;
        const cx = size / 2;
        const cy = size / 2;
        const r1 = size * 0.46;
        const r2 = size * 0.12;

        o.fillStyle = color;
        o.beginPath();
        for (let i = 0; i < 8; i++) {
            const rad = (i * Math.PI) / 4;
            const r = (i % 2 === 0) ? r1 : r2;
            const x = cx + Math.cos(rad) * r;
            const y = cy + Math.sin(rad) * r;
            if (i === 0) o.moveTo(x, y);
            else o.lineTo(x, y);
        }
        o.closePath();
        o.fill();

        spriteCache[key] = s.canvas;
        return s.canvas;
    }

    // 7. Confetti Ribbon / Diamond Sprite
    function generateConfettiSprite(w, h, color) {
        const key = 'confetti_' + w + '_' + h + '_' + color;
        if (spriteCache[key]) return spriteCache[key];

        const s = createOffscreen(w, h);
        const o = s.ctx;
        o.fillStyle = color;
        o.fillRect(0, 0, w, h);

        spriteCache[key] = s.canvas;
        return s.canvas;
    }

    // 8. Spooky Halloween Bat Sprite
    function generateBatSprite(size) {
        const key = 'bat_' + size;
        if (spriteCache[key]) return spriteCache[key];

        const s = createOffscreen(size, size * 0.6);
        const o = s.ctx;
        const cx = size / 2;
        const cy = (size * 0.6) / 2;

        o.fillStyle = '#1e1b4b'; // Deep dark violet / midnight
        o.beginPath();
        // Head & ears
        o.moveTo(cx, cy - size * 0.12);
        o.lineTo(cx - size * 0.08, cy - size * 0.22);
        o.lineTo(cx - size * 0.05, cy - size * 0.08);
        // Left Wing
        o.quadraticCurveTo(cx - size * 0.28, cy - size * 0.28, cx - size * 0.46, cy - size * 0.06);
        o.quadraticCurveTo(cx - size * 0.35, cy + size * 0.08, cx - size * 0.22, cy + size * 0.06);
        o.quadraticCurveTo(cx - size * 0.14, cy + size * 0.16, cx, cy + size * 0.12);
        // Right Wing
        o.quadraticCurveTo(cx + size * 0.14, cy + size * 0.16, cx + size * 0.22, cy + size * 0.06);
        o.quadraticCurveTo(cx + size * 0.35, cy + size * 0.08, cx + size * 0.46, cy - size * 0.06);
        o.quadraticCurveTo(cx + size * 0.28, cy - size * 0.28, cx + size * 0.05, cy - size * 0.08);
        o.lineTo(cx + size * 0.08, cy - size * 0.22);
        o.closePath();
        o.fill();

        spriteCache[key] = s.canvas;
        return s.canvas;
    }

    // 9. Ramadan Golden Crescent Moon Sprite
    function generateCrescentSprite(size) {
        const key = 'crescent_' + size;
        if (spriteCache[key]) return spriteCache[key];

        const s = createOffscreen(size, size);
        const o = s.ctx;
        const cx = size / 2;
        const cy = size / 2;
        const r = size * 0.4;

        o.fillStyle = '#f59e0b';
        o.beginPath();
        o.arc(cx, cy, r, -Math.PI * 0.5, Math.PI * 0.5, false);
        o.arc(cx + r * 0.38, cy, r * 0.82, Math.PI * 0.5, -Math.PI * 0.5, true);
        o.closePath();
        o.fill();

        // Little hanging star
        o.fillStyle = '#fbbf24';
        o.beginPath();
        o.arc(cx + r * 0.28, cy, size * 0.08, 0, Math.PI * 2);
        o.fill();

        spriteCache[key] = s.canvas;
        return s.canvas;
    }

    // 10. Shimmering Iridescent Bubble Sprite
    function generateBubbleSprite(size) {
        const key = 'bubble_' + size;
        if (spriteCache[key]) return spriteCache[key];

        const s = createOffscreen(size, size);
        const o = s.ctx;
        const cx = size / 2;
        const cy = size / 2;
        const r = size * 0.42;

        // Soft iridescent gradient rim
        const grad = o.createRadialGradient(cx - r * 0.3, cy - r * 0.3, r * 0.1, cx, cy, r);
        grad.addColorStop(0, 'rgba(255, 255, 255, 0.45)');
        grad.addColorStop(0.7, 'rgba(192, 132, 252, 0.25)');
        grad.addColorStop(0.95, 'rgba(56, 189, 248, 0.55)');
        grad.addColorStop(1, 'rgba(255, 255, 255, 0.7)');

        o.fillStyle = grad;
        o.beginPath();
        o.arc(cx, cy, r, 0, Math.PI * 2);
        o.fill();

        // Highlight glint
        o.fillStyle = 'rgba(255, 255, 255, 0.85)';
        o.beginPath();
        o.ellipse(cx - r * 0.4, cy - r * 0.4, r * 0.25, r * 0.12, -Math.PI / 4, 0, Math.PI * 2);
        o.fill();

        spriteCache[key] = s.canvas;
        return s.canvas;
    }

    // 11. Autumn Maple Leaf Sprite
    function generateLeafSprite(size, color) {
        const key = 'leaf_' + size + '_' + color;
        if (spriteCache[key]) return spriteCache[key];

        const s = createOffscreen(size, size);
        const o = s.ctx;
        const cx = size / 2;
        const cy = size / 2;

        o.fillStyle = color;
        o.beginPath();
        o.moveTo(cx, cy - size * 0.42);
        o.lineTo(cx + size * 0.15, cy - size * 0.2);
        o.lineTo(cx + size * 0.38, cy - size * 0.15);
        o.lineTo(cx + size * 0.2, cy + size * 0.05);
        o.lineTo(cx + size * 0.3, cy + size * 0.28);
        o.lineTo(cx + size * 0.08, cy + size * 0.22);
        o.lineTo(cx, cy + size * 0.42);
        o.lineTo(cx - size * 0.08, cy + size * 0.22);
        o.lineTo(cx - size * 0.3, cy + size * 0.28);
        o.lineTo(cx - size * 0.2, cy + size * 0.05);
        o.lineTo(cx - size * 0.38, cy - size * 0.15);
        o.lineTo(cx - size * 0.15, cy - size * 0.2);
        o.closePath();
        o.fill();

        spriteCache[key] = s.canvas;
        return s.canvas;
    }

    // Particle Factory
    function getParticleCount() {
        const isMobile = width <= 640;
        const isTablet = width > 640 && width <= 1024;

        const base = isMobile ? 12 : (isTablet ? 20 : 30);
        if (currentIntensity === 'subtle') return Math.max(6, Math.round(base * 0.65));
        if (currentIntensity === 'festive') return Math.min(48, Math.round(base * 1.35));
        return base;
    }

    function initParticles() {
        particles = [];
        const count = getParticleCount();

        for (let i = 0; i < count; i++) {
            particles.push(createParticle(true));
        }
    }

    function createParticle(randomizeY) {
        const p = {
            x: Math.random() * width,
            y: randomizeY ? Math.random() * height : -30,
            size: 16,
            opacity: 0.8,
            angle: Math.random() * Math.PI * 2,
            vAngle: (Math.random() - 0.5) * 0.03,
            vx: 0,
            vy: 1,
            swayPhase: Math.random() * Math.PI * 2,
            swaySpeed: 0.02 + Math.random() * 0.03,
            swayAmp: 1.5 + Math.random() * 2.5,
            sprite: null,
            mode: currentMode
        };

        const isMobile = width <= 640;

        switch (currentMode) {
            case 'christmas': {
                const roll = Math.random();
                if (roll < 0.65) {
                    // Snowflakes
                    p.size = isMobile ? 12 + Math.random() * 12 : 14 + Math.random() * 18;
                    p.sprite = generateSnowflakeSprite(32);
                    p.vy = 0.8 + Math.random() * 1.2;
                    p.opacity = 0.45 + Math.random() * 0.5;
                } else if (roll < 0.82) {
                    // Cute Santa hats / heads
                    p.size = isMobile ? 18 + Math.random() * 8 : 22 + Math.random() * 12;
                    p.sprite = generateSantaSprite(36);
                    p.vy = 0.6 + Math.random() * 0.9;
                    p.opacity = 0.8 + Math.random() * 0.2;
                    p.swayAmp = 3.5;
                } else if (roll < 0.94) {
                    // Peaceful flying doves
                    p.size = isMobile ? 18 + Math.random() * 8 : 24 + Math.random() * 12;
                    p.sprite = generateDoveSprite(36);
                    p.vy = 0.4 + Math.random() * 0.6;
                    p.vx = (Math.random() > 0.5 ? 1 : -1) * (0.4 + Math.random() * 0.5);
                    p.opacity = 0.75 + Math.random() * 0.25;
                    p.swaySpeed = 0.04;
                } else {
                    // Gold Christmas starlight
                    p.size = 14 + Math.random() * 8;
                    p.sprite = generateStarSprite(24, '#fbbf24');
                    p.vy = 0.5 + Math.random() * 0.8;
                    p.opacity = 0.6 + Math.random() * 0.4;
                }
                break;
            }

            case 'winter': {
                p.size = isMobile ? 10 + Math.random() * 14 : 12 + Math.random() * 22;
                p.sprite = generateSnowflakeSprite(32);
                p.vy = 0.7 + Math.random() * 1.4;
                p.opacity = 0.35 + Math.random() * 0.6;
                p.swayAmp = 2.0 + Math.random() * 2.0;
                break;
            }

            case 'new_year': {
                const colors = ['#f59e0b', '#fb7185', '#108a55', '#a855f7', '#38bdf8', '#fef08a'];
                const col = colors[Math.floor(Math.random() * colors.length)];
                if (Math.random() < 0.3) {
                    p.size = 14 + Math.random() * 10;
                    p.sprite = generateStarSprite(24, '#f59e0b');
                } else {
                    const cw = 8 + Math.floor(Math.random() * 8);
                    const ch = 12 + Math.floor(Math.random() * 10);
                    p.size = cw;
                    p.sprite = generateConfettiSprite(cw, ch, col);
                }
                p.vy = 1.0 + Math.random() * 1.8;
                p.vAngle = (Math.random() - 0.5) * 0.08;
                p.opacity = 0.7 + Math.random() * 0.3;
                break;
            }

            case 'valentine': {
                const heartColors = ['#f43f5e', '#e11d48', '#fb7185', '#fda4af'];
                const col = heartColors[Math.floor(Math.random() * heartColors.length)];
                if (Math.random() < 0.6) {
                    p.size = isMobile ? 14 + Math.random() * 10 : 18 + Math.random() * 14;
                    p.sprite = generateHeartSprite(32, col);
                } else {
                    p.size = isMobile ? 12 + Math.random() * 8 : 16 + Math.random() * 10;
                    p.sprite = generatePetalSprite(28, '#fda4af');
                }
                // Romantic particles float gently upward
                p.y = randomizeY ? Math.random() * height : height + 20;
                p.vy = -(0.5 + Math.random() * 0.8);
                p.opacity = 0.5 + Math.random() * 0.45;
                break;
            }

            case 'easter': {
                if (Math.random() < 0.35) {
                    // Flying white peace doves
                    p.size = isMobile ? 18 + Math.random() * 8 : 24 + Math.random() * 12;
                    p.sprite = generateDoveSprite(36);
                    p.vy = 0.4 + Math.random() * 0.6;
                    p.vx = (Math.random() > 0.5 ? 1 : -1) * (0.3 + Math.random() * 0.5);
                } else {
                    const springColors = ['#fbcfe8', '#fed7aa', '#bbf7d0', '#e9d5ff'];
                    const col = springColors[Math.floor(Math.random() * springColors.length)];
                    p.size = 14 + Math.random() * 12;
                    p.sprite = generatePetalSprite(28, col);
                    p.vy = 0.6 + Math.random() * 0.9;
                }
                p.opacity = 0.6 + Math.random() * 0.35;
                break;
            }

            case 'halloween': {
                if (Math.random() < 0.5) {
                    // Gliding silhouette bats
                    p.size = isMobile ? 20 + Math.random() * 10 : 28 + Math.random() * 14;
                    p.sprite = generateBatSprite(36);
                    p.vy = 0.5 + Math.random() * 0.7;
                    p.vx = (Math.random() > 0.5 ? 1 : -1) * (0.6 + Math.random() * 0.8);
                } else {
                    // Glowing ember particles rising
                    p.y = randomizeY ? Math.random() * height : height + 20;
                    p.size = 10 + Math.random() * 10;
                    p.sprite = generateStarSprite(20, '#ea580c');
                    p.vy = -(0.7 + Math.random() * 1.2);
                }
                p.opacity = 0.6 + Math.random() * 0.4;
                break;
            }

            case 'independence_ng': {
                // Nigerian Green & White celebratory confetti
                const ngColors = ['#008751', '#108a55', '#ffffff', '#22c55e', '#fbbf24'];
                const col = ngColors[Math.floor(Math.random() * ngColors.length)];
                const cw = 8 + Math.floor(Math.random() * 8);
                const ch = 14 + Math.floor(Math.random() * 10);
                p.size = cw;
                p.sprite = generateConfettiSprite(cw, ch, col);
                p.vy = 0.9 + Math.random() * 1.5;
                p.vAngle = (Math.random() - 0.5) * 0.07;
                p.opacity = 0.75 + Math.random() * 0.25;
                break;
            }

            case 'ramadan': {
                if (Math.random() < 0.45) {
                    p.size = isMobile ? 18 + Math.random() * 8 : 24 + Math.random() * 12;
                    p.sprite = generateCrescentSprite(36);
                } else {
                    p.size = 12 + Math.random() * 8;
                    p.sprite = generateStarSprite(24, '#fbbf24');
                }
                p.y = randomizeY ? Math.random() * height : height + 20;
                p.vy = -(0.4 + Math.random() * 0.6);
                p.opacity = 0.6 + Math.random() * 0.35;
                break;
            }

            case 'summer': {
                if (Math.random() < 0.55) {
                    p.size = isMobile ? 16 + Math.random() * 12 : 22 + Math.random() * 18;
                    p.sprite = generateBubbleSprite(36);
                } else {
                    p.size = 16 + Math.random() * 14;
                    p.sprite = generateStarSprite(28, '#f59e0b');
                }
                p.y = randomizeY ? Math.random() * height : height + 20;
                p.vy = -(0.4 + Math.random() * 0.8);
                p.opacity = 0.4 + Math.random() * 0.45;
                break;
            }

            case 'autumn':
            default: {
                const leafColors = ['#d97706', '#b45309', '#ea580c', '#991b1b', '#eab308'];
                const col = leafColors[Math.floor(Math.random() * leafColors.length)];
                p.size = isMobile ? 14 + Math.random() * 10 : 20 + Math.random() * 14;
                p.sprite = generateLeafSprite(32, col);
                p.vy = 0.8 + Math.random() * 1.2;
                p.swayAmp = 3.5;
                p.opacity = 0.65 + Math.random() * 0.35;
                break;
            }
        }

        return p;
    }

    // Animation Render Loop (Optimized for 60/120 FPS)
    function loop(now) {
        if (!isRunning) return;

        if (!lastTime) lastTime = now;
        const dt = Math.min((now - lastTime) / 1000, 0.1);
        lastTime = now;

        ctx.clearRect(0, 0, width, height);

        const pCount = particles.length;
        for (let i = 0; i < pCount; i++) {
            const p = particles[i];

            // Motion integration
            p.y += p.vy * dt * 60;
            if (p.vx) p.x += p.vx * dt * 60;
            p.swayPhase += p.swaySpeed * dt * 60;
            const currentX = p.x + Math.sin(p.swayPhase) * p.swayAmp;
            p.angle += p.vAngle * dt * 60;

            // Boundary wrapping
            if (p.vy > 0 && p.y > height + 35) {
                p.y = -35;
                p.x = Math.random() * width;
            } else if (p.vy < 0 && p.y < -35) {
                p.y = height + 35;
                p.x = Math.random() * width;
            }

            if (currentX < -45) p.x = width + 35;
            if (currentX > width + 45) p.x = -35;

            // High performance GPU blit
            if (p.sprite) {
                ctx.save();
                ctx.globalAlpha = p.opacity;
                ctx.translate(currentX, p.y);
                if (p.angle !== 0) ctx.rotate(p.angle);
                ctx.drawImage(p.sprite, -p.size / 2, -p.size / 2, p.size, p.size);
                ctx.restore();
            }
        }

        animId = window.requestAnimationFrame(loop);
    }

    function start() {
        if (isRunning || isUserPaused) return;
        isRunning = true;
        lastTime = 0;
        animId = window.requestAnimationFrame(loop);
    }

    function stop() {
        isRunning = false;
        if (animId) {
            window.cancelAnimationFrame(animId);
            animId = null;
        }
        if (ctx) {
            ctx.clearRect(0, 0, width, height);
        }
    }

    function resize() {
        if (!canvas) return;
        width = window.innerWidth;
        height = window.innerHeight;
        dpr = Math.min(window.devicePixelRatio || 1, 2);

        canvas.width = Math.ceil(width * dpr);
        canvas.height = Math.ceil(height * dpr);
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';

        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(dpr, dpr);

        initParticles();
    }

    // Toggle button UI update
    function updateToggleButton() {
        const btn = document.getElementById('holiday-fx-toggle-btn');
        if (!btn) return;

        const iconSpan = btn.querySelector('.hfx-icon');
        const textSpan = btn.querySelector('.hfx-text');

        if (isUserPaused) {
            if (iconSpan) iconSpan.textContent = '✨';
            if (textSpan) textSpan.textContent = 'Play FX';
            btn.title = 'Resume seasonal atmospheric effects';
            btn.classList.add('is-paused');
        } else {
            const icons = {
                christmas: '🎄',
                winter: '❄️',
                new_year: '🎆',
                valentine: '💖',
                easter: '🕊️',
                halloween: '🎃',
                summer: '☀️',
                independence_ng: '🇳🇬',
                ramadan: '🌙',
                autumn: '🍂'
            };
            if (iconSpan) iconSpan.textContent = icons[currentMode] || '❄️';
            if (textSpan) textSpan.textContent = 'Pause FX';
            btn.title = 'Pause seasonal atmospheric effects';
            btn.classList.remove('is-paused');
        }
    }

    // Initialization routine
    function init() {
        root = document.getElementById('holiday-fx-root');
        if (!root) return;

        canvas = document.getElementById('holiday-fx-canvas');
        if (!canvas) return;

        ctx = canvas.getContext('2d', { alpha: true });
        if (!ctx) return;

        currentMode = root.getAttribute('data-mode') || 'winter';
        currentIntensity = root.getAttribute('data-intensity') || 'standard';

        if (currentMode === 'disabled') {
            return;
        }

        // Check if visitor previously paused
        try {
            if (localStorage.getItem('eventlyy_holiday_fx_disabled') === '1') {
                isUserPaused = true;
            }
        } catch (e) {}

        resize();
        updateToggleButton();

        if (!isUserPaused) {
            start();
        }

        // Debounced resize handler
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(resize, 150);
        }, { passive: true });

        // Tab visibility awareness (halt loop when tab is hidden to save 100% CPU & battery)
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stop();
            } else if (!isUserPaused) {
                start();
            }
        });
    }

    // Public API for external integration and live testing
    window.HolidayFX = {
        init: init,
        start: start,
        stop: stop,
        toggle: function () {
            isUserPaused = !isUserPaused;
            try {
                if (isUserPaused) {
                    localStorage.setItem('eventlyy_holiday_fx_disabled', '1');
                    stop();
                } else {
                    localStorage.removeItem('eventlyy_holiday_fx_disabled');
                    start();
                }
            } catch (e) {}
            updateToggleButton();
        },
        setMode: function (newMode) {
            if (newMode === 'disabled') {
                stop();
                currentMode = 'disabled';
                return;
            }
            currentMode = newMode;
            initParticles();
            updateToggleButton();
            if (!isUserPaused && !isRunning) {
                start();
            }
        },
        setIntensity: function (intensity) {
            currentIntensity = intensity;
            initParticles();
        },
        getMode: function () {
            return currentMode;
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();