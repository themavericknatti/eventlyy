/*
========================================================
 Project: Evently
 File: app.js
 Author: Celio Natti
 Version: 1.0.0
 Created: 2026-06-19
 Description: Handles UI interactions, form validation,
 API requests, and frontend logic.
========================================================
*/

// Theme toggling and small interactive behaviors
(function(){
  const root = document.documentElement;
  const themeButtons = Array.from(document.querySelectorAll('[data-theme-toggle]'));

  function setTheme(name){
    root.setAttribute('data-theme', name);
    try{ localStorage.setItem('theme', name); }catch(e){}
  }

  function updateThemeButtons(){
    const isDark = root.getAttribute('data-theme') === 'dark';
    themeButtons.forEach(btn => {
      btn.setAttribute('aria-pressed', String(isDark));
      btn.classList.toggle('is-dark', isDark);
    });
  }

  // Initialize theme from storage or system
  const saved = localStorage.getItem('theme');
  if(saved){ setTheme(saved); }
  else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches){ setTheme('dark'); }
  else { setTheme('light'); }

  updateThemeButtons();

  themeButtons.forEach(button => button.addEventListener('click', ()=>{
    const current = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    setTheme(current);
    updateThemeButtons();
    const announcer = document.getElementById('theme-announcer');
    if(announcer) announcer.textContent = `Switched to ${current} mode`;
  }));

  // Reflect visual class when theme changes (in case of external changes)
  const obs = new MutationObserver(()=>{
    const isDark = root.getAttribute('data-theme') === 'dark';
    themeButtons.forEach(btn => btn.classList.toggle('is-dark', isDark));
  });
  obs.observe(root, {attributes:true, attributeFilter:['data-theme']});

  // Active navigation states are handled natively in the HTML/CSS markup (e.g. by adding class="active" or aria-current="true" dynamically from the PHP backend). This avoids JS overriding the server-rendered templates.

  // Mobile menu toggle helper
  function setupMobileMenu(btnId, bottomClass){
    const btn = document.getElementById(btnId);
    const bottom = document.querySelector(bottomClass);
    if(!btn || !bottom) return;
    btn.addEventListener('click', ()=>{
      const open = bottom.classList.toggle('visible');
      btn.setAttribute('aria-expanded', String(open));
    });
    bottom.addEventListener('click', (e)=>{
      if(e.target.closest('.nav-item') || e.target.closest('.nav-link')){
        bottom.classList.remove('visible');
        btn.setAttribute('aria-expanded','false');
      }
    });
    document.addEventListener('keydown', (e)=>{
      if(e.key === 'Escape'){
        bottom.classList.remove('visible');
        btn.setAttribute('aria-expanded','false');
      }
    });
  }

  setupMobileMenu('host-mobile-menu', '.host-bottom');
  setupMobileMenu('admin-mobile-menu', '.admin-bottom');

  // Carousel interaction bindings (slides are static HTML)
  const carousel = document.getElementById('carousel');

  if (carousel) {
    const slides = document.getElementById('slides');
    const prev = carousel.querySelector('.carousel-prev');
    const next = carousel.querySelector('.carousel-next');
    const indicators = document.getElementById('carousel-indicators');
    const playPause = document.getElementById('play-pause');

    let currentIndex = 0;
    let autoplayId = null;
    let autoplayEnabled = true;
    let playing = false;
    const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const AUTOPLAY_MS = 4000;

    function slideCount(){ return slides ? slides.children.length : 0; }

    function scrollToIndex(i){
      const item = slides.children[i];
      if(!item) return;
      const left = item.offsetLeft - (parseFloat(getComputedStyle(slides).paddingLeft) || 0);
      const behavior = prefersReduced ? 'auto' : 'smooth';
      slides.scrollTo({left, behavior});
      updateIndicators(i);
      updateButtons();
      currentIndex = i;
    }

    function updateIndicators(active){
      if(!indicators) return;
      Array.from(indicators.children).forEach((b, idx)=> b.setAttribute('aria-selected', String(idx===active)));
    }

    function getCurrentIndex(){
      if(!slides || slideCount() === 0) return 0;
      const center = slides.scrollLeft + slides.clientWidth / 2;
      let nearest = 0; let min = Infinity;
      Array.from(slides.children).forEach((child, idx)=>{
        const c = child.offsetLeft + child.offsetWidth/2;
        const d = Math.abs(center - c);
        if(d < min){ min = d; nearest = idx; }
      });
      return nearest;
    }

    // Bind indicator clicks (static buttons)
    if(indicators){
      Array.from(indicators.children).forEach((btn, i)=>{
        btn.addEventListener('click', ()=> scrollToIndex(i));
        btn.addEventListener('keydown', (e)=>{ if(e.key==='Enter' || e.key===' ') { e.preventDefault(); btn.click(); } });
      });
    }

    // Sync indicators on scroll
    let raf = null;
    if(slides){
      slides.addEventListener('scroll', ()=>{
        if(raf) cancelAnimationFrame(raf);
        raf = requestAnimationFrame(()=>{
          const idx = getCurrentIndex();
          updateIndicators(idx);
          updateButtons();
          currentIndex = idx;
        });
      }, {passive:true});
    }

    function updateButtons(){
      const max = slideCount() - 1;
      if(prev) prev.disabled = currentIndex <= 0;
      if(next) next.disabled = currentIndex >= max;
    }

    prev?.addEventListener('click', ()=> scrollToIndex(Math.max(0, currentIndex - 1)));
    next?.addEventListener('click', ()=> scrollToIndex(Math.min(slideCount() - 1, currentIndex + 1)));

    // Autoplay
    function updateAutoplayUI(){
      if(playPause) playPause.setAttribute('aria-pressed', String(playing));
      if(playPause) playPause.textContent = playing ? '⏸' : '▶';
    }

    function startAutoplay(){
      if(prefersReduced || !autoplayEnabled || slideCount() === 0) return;
      stopAutoplay();
      playing = true;
      updateAutoplayUI();
      autoplayId = setInterval(()=>{
        const nextIndex = (currentIndex + 1) % slideCount();
        scrollToIndex(nextIndex);
      }, AUTOPLAY_MS);
    }

    function stopAutoplay(){
      if(autoplayId) { clearInterval(autoplayId); autoplayId = null; }
      playing = false;
      updateAutoplayUI();
    }

    playPause?.addEventListener('click', ()=>{
      if(playing) stopAutoplay(); else startAutoplay();
    });

    // Pause autoplay on user interaction, resume on leave
    if(slides){
      ['mouseenter','touchstart','focusin'].forEach(evt => slides.addEventListener(evt, ()=>{ if(playing) stopAutoplay(); }, {passive:true}));
      ['mouseleave','touchend','focusout'].forEach(evt => slides.addEventListener(evt, ()=>{ if(autoplayEnabled && !prefersReduced) startAutoplay(); }, {passive:true}));
    }
    document.addEventListener('visibilitychange', ()=>{ if(document.hidden) stopAutoplay(); else startAutoplay(); });

    // Initialize carousel state
    setTimeout(()=>{ updateButtons(); startAutoplay(); }, 80);
  }

  // Header background on scroll to avoid content showing through
  const header = document.querySelector('.top-nav');
  function onScroll(){
    if(!header) return;
    header.classList.toggle('scrolled', window.scrollY > 8);
  }
  window.addEventListener('scroll', onScroll, {passive:true});
  onScroll();

  // ─── Scroll Reveal: IntersectionObserver ───────────────────
  if('IntersectionObserver' in window){
    // Reveal sections marked with .reveal
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if(entry.isIntersecting){
          entry.target.classList.add('visible');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

    // Stagger event list items on scroll
    const itemObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry, i) => {
        if(entry.isIntersecting){
          const delay = (Array.from(document.querySelectorAll('.event-item')).indexOf(entry.target) % 6) * 80;
          setTimeout(() => {
            entry.target.classList.add('visible');
          }, delay);
          itemObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -20px 0px' });

    document.querySelectorAll('.event-item').forEach(el => {
      el.classList.add('reveal');
      itemObserver.observe(el);
    });
  }

  // ─── Scroll-To-Top Button Handler ───────────────────
  const scrollTopBtn = document.getElementById('scroll-to-top');
  if(scrollTopBtn){
    window.addEventListener('scroll', () => {
      if (window.scrollY > 300) {
        scrollTopBtn.classList.add('visible');
      } else {
        scrollTopBtn.classList.remove('visible');
      }
    }, {passive: true});

    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
})();

/* ════════════════════════════════════════════════════════════
   High-Performance 3D Depth, Tilt, & Progressive Image System
   - Zero-dependency, GPU-accelerated (transform3d)
   - 60/120fps requestAnimationFrame scheduling
   - Respects prefers-reduced-motion & touch battery
   ════════════════════════════════════════════════════════════ */
(function init3DDepthSystem() {
  const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const isFinePointer = window.matchMedia && window.matchMedia('(pointer: fine)').matches;

  // 1. Reveal-on-Scroll Smooth Entrance Transitions
  const revealElements = document.querySelectorAll('.reveal-on-scroll');
  if (revealElements.length > 0) {
    if (prefersReduced || !('IntersectionObserver' in window) || window.innerWidth <= 768) {
      revealElements.forEach(el => el.classList.add('revealed'));
    } else {
      const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
            observer.unobserve(entry.target);
          }
        });
      }, {
        threshold: 0.04,
        rootMargin: '0px 0px 50px 0px'
      });

      revealElements.forEach(el => revealObserver.observe(el));
    }
  }

  // 2. Hero Subtle Scroll Pitch (Desktop fine-pointer only; no vertical translation to prevent overlapping following content)
  const hero3D = document.querySelector('.hm-hero-3d');
  if (hero3D && !prefersReduced && isFinePointer) {
    let ticking = false;
    const maxScroll = 500;

    function updateHeroScroll() {
      if (window.innerWidth < 1024) {
        hero3D.style.transform = '';
        ticking = false;
        return;
      }

      const scrollY = window.pageYOffset || document.documentElement.scrollTop;
      if (scrollY <= maxScroll) {
        // Subtle tilt without downwards translateY displacement to prevent layout overlap
        const progress = Math.min(scrollY / maxScroll, 1);
        const rotateX = progress * 4; // subtle 4 deg back-pitch
        hero3D.style.transform = `rotateX(${rotateX}deg)`;
      } else {
        hero3D.style.transform = '';
      }
      ticking = false;
    }

    window.addEventListener('scroll', () => {
      if (!ticking) {
        window.requestAnimationFrame(updateHeroScroll);
        ticking = true;
      }
    }, { passive: true });

    window.addEventListener('resize', () => {
      if (window.innerWidth < 1024) {
        hero3D.style.transform = '';
      }
    }, { passive: true });
  }

  // 3. Physics 3D Tilt on Hover with Specular Glare Sheen
  if (isFinePointer && !prefersReduced) {
    const cards = document.querySelectorAll('.card-3d');

    cards.forEach(card => {
      let bounds = null;
      let rafId = null;

      function onPointerEnter() {
        bounds = card.getBoundingClientRect();
      }

      function onPointerMove(e) {
        if (!bounds) bounds = card.getBoundingClientRect();

        const x = e.clientX - bounds.left;
        const y = e.clientY - bounds.top;

        if (rafId) cancelAnimationFrame(rafId);
        rafId = requestAnimationFrame(() => {
          const width = bounds.width;
          const height = bounds.height;

          // Max rotation: 7 degrees for subtle elegant depth
          const rotateY = ((x / width) - 0.5) * 14;
          const rotateX = ((y / height) - 0.5) * -14;

          card.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-4px)`;

          // Specular glare positioning
          const percentX = ((x / width) * 100).toFixed(1);
          const percentY = ((y / height) * 100).toFixed(1);
          card.style.setProperty('--glare-x', `${percentX}%`);
          card.style.setProperty('--glare-y', `${percentY}%`);
        });
      }

      function onPointerLeave() {
        if (rafId) cancelAnimationFrame(rafId);
        card.style.transform = '';
        bounds = null;
      }

      card.addEventListener('pointerenter', onPointerEnter, { passive: true });
      card.addEventListener('pointermove', onPointerMove, { passive: true });
      card.addEventListener('pointerleave', onPointerLeave, { passive: true });
    });
  }

  // 4. Progressive Blur-Up Image Loading
  function initProgressiveImages() {
    const imgWrappers = document.querySelectorAll('.progressive-img-wrap');
    imgWrappers.forEach(wrap => {
      const img = wrap.querySelector('img');
      if (!img) return;

      function markLoaded() {
        img.classList.add('is-loaded');
        wrap.classList.add('has-loaded');
      }

      if (img.complete && img.naturalWidth !== 0) {
        markLoaded();
      } else {
        img.addEventListener('load', markLoaded, { once: true });
        img.addEventListener('error', () => {
          // Gracefully unhide even on error so alt text / placeholder can render
          wrap.classList.add('has-loaded');
          img.classList.add('is-loaded');
        }, { once: true });
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProgressiveImages);
  } else {
    initProgressiveImages();
  }
})();

