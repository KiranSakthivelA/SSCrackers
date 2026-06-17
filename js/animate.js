// ================================================
// SS CRACKERS – LIQUID GLASS ANIMATIONS ENGINE
// Ripple | Tilt | Parallax | Orbs | Reveals
// ================================================

(function () {
  'use strict';

  // ---- Run after DOM ready ----
  document.addEventListener('DOMContentLoaded', () => {
    initRipple();
    initTilt();
    initParallax();
    initEnhancedReveal();
    initHeaderScroll();
    initCursorGlow();
    initSectionTransitions();
  });

  // ================================================
  // 1. LIQUID RIPPLE on all buttons
  // ================================================
  function initRipple() {
    const rippleTargets = document.querySelectorAll(
      '.btn, .filter-btn, .cart-header-btn, .btn-proceed, .btn-gold, .btn-add-cart, .table-order-btn'
    );

    rippleTargets.forEach(el => {
      el.addEventListener('click', function (e) {
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const size = Math.max(rect.width, rect.height) * 2;

        const wave = document.createElement('span');
        wave.className = 'ripple-wave';
        wave.style.cssText = `
          width: ${size}px;
          height: ${size}px;
          left: ${x - size / 2}px;
          top: ${y - size / 2}px;
        `;
        this.appendChild(wave);
        setTimeout(() => wave.remove(), 700);
      });
    });
  }

  // ================================================
  // 2. MAGNETIC 3D TILT on cards
  // ================================================
  function initTilt() {
    const tiltEls = document.querySelectorAll('.cat-card, .why-card, .product-card');

    tiltEls.forEach(el => {
      el.classList.add('tilt-card');

      el.addEventListener('mousemove', function (e) {
        const rect = this.getBoundingClientRect();
        const cx = rect.left + rect.width / 2;
        const cy = rect.top + rect.height / 2;
        const dx = (e.clientX - cx) / (rect.width / 2);
        const dy = (e.clientY - cy) / (rect.height / 2);
        const rotX = -dy * 6;
        const rotY =  dx * 6;
        this.style.transform = `perspective(800px) rotateX(${rotX}deg) rotateY(${rotY}deg) translateY(-6px) scale(1.02)`;
      });

      el.addEventListener('mouseleave', function () {
        this.style.transform = 'perspective(800px) rotateX(0) rotateY(0) translateY(0) scale(1)';
      });
    });
  }

  // ================================================
  // 3. HERO PARALLAX on scroll
  // ================================================
  function initParallax() {
    const heroBgs = document.querySelectorAll('.hero-bg');
    if (!heroBgs.length) return;

    let ticking = false;

    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(() => {
          const scrollY = window.scrollY;
          heroBgs.forEach(bg => {
            bg.style.transform = `translateY(${scrollY * 0.38}px)`;
          });
          ticking = false;
        });
        ticking = true;
      }
    });
  }

  // ================================================
  // 4. FLOATING GOLD ORBS (background atmosphere)
  // ================================================
  function initGoldOrbs() {
    const sections = document.querySelectorAll('.categories-section, .why-section, .products-section');

    sections.forEach(section => {
      section.style.position = 'relative';
      section.style.overflow = 'hidden';

      const orbs = [
        { size: 300, x: '-5%',  y: '-10%', color: 'rgba(212,175,55,0.12)',  delay: '0s',   dur: '14s' },
        { size: 200, x: '80%', y: '60%',  color: 'rgba(0,79,46,0.08)',     delay: '3s',   dur: '18s' },
        { size: 150, x: '50%', y: '20%',  color: 'rgba(212,175,55,0.08)',  delay: '6s',   dur: '11s' },
      ];

      orbs.forEach(orb => {
        const el = document.createElement('div');
        el.className = 'gold-orb';
        el.style.cssText = `
          width: ${orb.size}px;
          height: ${orb.size}px;
          left: ${orb.x};
          top: ${orb.y};
          background: ${orb.color};
          animation: orbFloat ${orb.dur} ${orb.delay} ease-in-out infinite;
        `;
        section.insertBefore(el, section.firstChild);
      });
    });
  }

  // ================================================
  // 5. ENHANCED SCROLL REVEAL with IntersectionObserver
  // ================================================
  function initEnhancedReveal() {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const delay = el.dataset.delay || 0;
          setTimeout(() => {
            el.classList.add('visible');
          }, delay * 1000);
          observer.unobserve(el);
        }
      });
    }, {
      threshold: 0.02,
      rootMargin: '0px 0px 50px 0px'
    });

    // Add reveal class to all section content
    const revealSelectors = [
      '.section-header',
      '.cat-card',
      '.why-card',
      '.stat-card',
      '.safety-card',
      '.offer-banner',
      '.contact-info',
      '.contact-form-wrap',
      '.pricelist-section',
      '.footer-col',
    ];

    document.querySelectorAll(revealSelectors.join(', ')).forEach((el, i) => {
      if (!el.classList.contains('reveal')) {
        el.classList.add('reveal');
      }
      el.dataset.delay = (i % 4) * 0.06;
      observer.observe(el);
    });

    // Also apply directional reveal to contact grid
    const contactInfo = document.querySelector('.contact-info');
    const contactForm = document.querySelector('.contact-form-wrap');
    if (contactInfo) contactInfo.classList.add('reveal-left');
    if (contactForm) contactForm.classList.add('reveal-right');
  }

  // ================================================
  // 6. HEADER GLASS INTENSIFY on scroll
  // ================================================
  function initHeaderScroll() {
    const header = document.getElementById('mainHeader');
    if (!header) return;

    let lastScroll = 0;

    window.addEventListener('scroll', () => {
      const scrollY = window.scrollY;

      // Glass intensify
      if (scrollY > 20) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }

      // Hide/show header on fast scroll
      if (scrollY > lastScroll + 10 && scrollY > 200) {
        header.style.transform = 'translateY(-100%)';
        header.style.transition = 'transform 0.35s cubic-bezier(0.22,1,0.36,1)';
      } else if (scrollY < lastScroll - 5) {
        header.style.transform = 'translateY(0)';
      }

      lastScroll = scrollY;
    }, { passive: true });
  }

  // ================================================
  // 7. SOFT CURSOR GLOW (desktop)
  // ================================================
  function initCursorGlow() {
    if (window.matchMedia('(pointer: coarse)').matches) return; // skip mobile

    const glow = document.createElement('div');
    glow.id = 'cursor-glow';
    glow.style.cssText = `
      position: fixed;
      width: 300px; height: 300px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(212,175,55,0.07) 0%, transparent 70%);
      pointer-events: none;
      z-index: 9999;
      transition: transform 0.12s ease;
      transform: translate(-50%, -50%);
      top: 0; left: 0;
    `;
    document.body.appendChild(glow);

    let mx = 0, my = 0;
    let gx = 0, gy = 0;

    document.addEventListener('mousemove', e => {
      mx = e.clientX;
      my = e.clientY;
    });

    function animateGlow() {
      gx += (mx - gx) * 0.08;
      gy += (my - gy) * 0.08;
      glow.style.left = gx + 'px';
      glow.style.top  = gy + 'px';
      requestAnimationFrame(animateGlow);
    }
    animateGlow();

    // Expand on hovering interactive elements
    document.querySelectorAll('a, button, .cat-card, .product-card').forEach(el => {
      el.addEventListener('mouseenter', () => {
        glow.style.width  = '420px';
        glow.style.height = '420px';
        glow.style.background = 'radial-gradient(circle, rgba(212,175,55,0.12) 0%, transparent 70%)';
      });
      el.addEventListener('mouseleave', () => {
        glow.style.width  = '300px';
        glow.style.height = '300px';
        glow.style.background = 'radial-gradient(circle, rgba(212,175,55,0.07) 0%, transparent 70%)';
      });
    });
  }

  // ================================================
  // 8. SMOOTH SECTION BLUR TRANSITION on scroll
  // ================================================
  function initSectionTransitions() {
    const sections = document.querySelectorAll('.section');
    if (!sections.length) return;

    const obs = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.filter  = 'blur(0)';
        } else {
          entry.target.style.opacity = '0.96';
          entry.target.style.filter  = 'blur(0.3px)';
        }
      });
    }, { threshold: 0.15 });

    sections.forEach(s => {
      s.style.transition = 'opacity 0.5s ease, filter 0.5s ease';
      obs.observe(s);
    });
  }



  // ================================================
  // 10. UPDATE PARTICLE COLORS to match new theme
  // ================================================
  function updateParticleColors() {
    const container = document.getElementById('particles');
    if (!container) return;

    // Clear old particles and re-create with new colors
    container.innerHTML = '';
    const colors = [
      'rgba(212,175,55,0.7)',   // Gold
      'rgba(0,104,80,0.5)',      // Jade
      'rgba(240,208,96,0.6)',   // Gold light
      'rgba(0,79,46,0.4)',       // Emerald
      'rgba(212,175,55,0.4)',   // Gold dim
    ];

    for (let i = 0; i < 22; i++) {
      const p = document.createElement('div');
      p.className = 'particle';
      const size  = Math.random() * 10 + 5;
      const color = colors[Math.floor(Math.random() * colors.length)];
      p.style.cssText = `
        width: ${size}px;
        height: ${size}px;
        background: ${color};
        border-radius: 50%;
        position: absolute;
        left: ${Math.random() * 100}%;
        top:  ${Math.random() * 100}%;
        animation-delay: ${Math.random() * 5}s;
        animation-duration: ${Math.random() * 4 + 4}s;
      `;
      container.appendChild(p);
    }
  }

  // ================================================
  // 11. SMOOTH FILTER CHANGE with fade
  // ================================================
  window.filterCategoryAnimated = function (cat) {
    const grid = document.getElementById('productsGrid');
    if (grid) {
      grid.style.opacity = '0';
      grid.style.transform = 'translateY(12px)';
      grid.style.filter = 'blur(4px)';
      grid.style.transition = 'opacity 0.25s ease, transform 0.25s ease, filter 0.25s ease';
      setTimeout(() => {
        if (typeof filterCategory === 'function') filterCategory(cat);
        grid.style.opacity = '1';
        grid.style.transform = 'translateY(0)';
        grid.style.filter = 'blur(0)';
      }, 260);
    } else {
      if (typeof filterCategory === 'function') filterCategory(cat);
    }
  };

  // ================================================
  // 12. ADD CART MICRO-ANIMATION
  // ================================================
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-add-cart');
    if (!btn) return;

    // Pop the cart icon in header
    const cartBtn = document.querySelector('.cart-header-btn');
    if (cartBtn) {
      cartBtn.style.transform = 'scale(1.18)';
      cartBtn.style.transition = 'transform 0.15s ease';
      setTimeout(() => {
        cartBtn.style.transform = 'scale(1)';
      }, 200);
    }
  });

})();
