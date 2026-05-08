/* =========================================================
   WebWP Theme — main.js
   GSAP + ScrollTrigger entrance animations.
   ========================================================= */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof gsap === 'undefined') return;
        if (typeof ScrollTrigger !== 'undefined') gsap.registerPlugin(ScrollTrigger);

        /* ----- Hero intro ----- */
        const hero = document.querySelector('.hero');
        if (hero) {
            const tl = gsap.timeline({ defaults: { ease: 'power3.out', duration: .9 } });
            tl.fromTo('.hero h1', { opacity: 0, y: 26 }, { opacity: 1, y: 0 })
              .fromTo('.hero .hero-lead', { opacity: 0, y: 24 }, { opacity: 1, y: 0 }, '-=.6')
              .fromTo('.hero .hero-cta', { opacity: 0, y: 24 }, { opacity: 1, y: 0 }, '-=.6')
              .fromTo('.hero .hero-visual', { opacity: 0, scale: .96 }, { opacity: 1, scale: 1, duration: 1.1 }, '-=.85')
              .fromTo('.hero .float-card', { opacity: 0, y: 18 }, { opacity: 1, y: 0, stagger: .15 }, '-=.7');

            // Floating cards subtle float loop
            gsap.utils.toArray('.hero .float-card').forEach((el, i) => {
                gsap.to(el, {
                    y: i % 2 ? -10 : -16,
                    duration: 2.6 + i * .2,
                    yoyo: true,
                    repeat: -1,
                    ease: 'sine.inOut',
                });
            });
        }

        /* ----- Scroll-triggered reveals ----- */
        if (typeof ScrollTrigger !== 'undefined') {
            const revealFns = [
                { sel: '.gsap-reveal',       from: { opacity: 0, y: 30 } },
                { sel: '.gsap-reveal-left',  from: { opacity: 0, x: -40 } },
                { sel: '.gsap-reveal-right', from: { opacity: 0, x: 40 } },
                { sel: '.gsap-scale',        from: { opacity: 0, scale: .94 } },
            ];
            revealFns.forEach(({ sel, from }) => {
                gsap.utils.toArray(sel).forEach((el) => {
                    gsap.fromTo(el, from, {
                        opacity: 1, x: 0, y: 0, scale: 1,
                        duration: .9, ease: 'power3.out',
                        scrollTrigger: {
                            trigger: el,
                            start: 'top 85%',
                            toggleActions: 'play none none none',
                        },
                    });
                });
            });

            /* ----- Explore Course: click-to-expand pill accordion ----- */
            gsap.utils.toArray('[data-explore-row]').forEach((row) => {
                const pills = Array.from(row.querySelectorAll('[data-pill]'));
                if (!pills.length) return;

                const openPill = (pill) => {
                    if (pill.classList.contains('is-open')) return;
                    const currentlyOpen = row.querySelector('[data-pill].is-open');

                    // Close previous
                    if (currentlyOpen) {
                        const openLabel  = currentlyOpen.querySelector('.pill-label');
                        const openDetail = currentlyOpen.querySelector('.pill-detail');
                        gsap.to(currentlyOpen, {
                            flexBasis: '60px',
                            duration: 0.55,
                            ease: 'power3.inOut',
                            onStart() {
                                currentlyOpen.classList.remove('is-open');
                                currentlyOpen.setAttribute('aria-expanded', 'false');
                            },
                        });
                        gsap.to(openDetail, { opacity: 0, duration: 0.25, ease: 'power2.out' });
                        gsap.fromTo(openLabel, { opacity: 0 }, { opacity: 1, duration: 0.35, delay: 0.25, ease: 'power2.out' });
                    }

                    // Open new
                    const newLabel  = pill.querySelector('.pill-label');
                    const newDetail = pill.querySelector('.pill-detail');
                    gsap.to(pill, {
                        flexBasis: '310px',
                        duration: 0.6,
                        ease: 'power3.inOut',
                        onStart() {
                            pill.classList.add('is-open');
                            pill.setAttribute('aria-expanded', 'true');
                        },
                    });
                    gsap.fromTo(newLabel, { opacity: 1 }, { opacity: 0, duration: 0.2, ease: 'power2.out' });
                    gsap.fromTo(newDetail,
                        { opacity: 0, y: 8 },
                        { opacity: 1, y: 0, duration: 0.45, delay: 0.2, ease: 'power2.out' }
                    );
                };

                pills.forEach((pill) => {
                    pill.addEventListener('click', () => openPill(pill));
                    pill.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            openPill(pill);
                        }
                    });
                });
            });

            /* ----- Stat counters ----- */
            gsap.utils.toArray('.success-num[data-count], .stat .num[data-count]').forEach((el) => {
                const target = parseFloat(el.dataset.count);
                const suffix = el.dataset.suffix || '';
                const obj = { n: 0 };
                el.textContent = '0' + suffix;
                gsap.to(obj, {
                    n: target,
                    duration: 2,
                    ease: 'power1.out',
                    scrollTrigger: { trigger: el, start: 'top 85%' },
                    onUpdate() {
                        el.textContent = Math.round(obj.n) + suffix;
                    },
                });
            });
        }
    });
})();
