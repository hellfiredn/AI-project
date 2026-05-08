/* CodeDeal — Frontend behaviours */
(function () {
    'use strict';

    /* ---------- Mobile nav toggle ---------- */
    const navToggle = document.querySelector('.cd-nav__toggle');
    const navList   = document.querySelector('.cd-nav__list');
    if (navToggle && navList) {
        navToggle.addEventListener('click', () => navList.classList.toggle('is-open'));
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.cd-nav')) navList.classList.remove('is-open');
        });
    }

    /* ---------- Toast helper ---------- */
    let toastEl;
    function toast(msg) {
        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.className = 'cd-toast';
            document.body.appendChild(toastEl);
        }
        toastEl.textContent = msg;
        toastEl.classList.add('is-show');
        clearTimeout(toast._t);
        toast._t = setTimeout(() => toastEl.classList.remove('is-show'), 2000);
    }

    /* ---------- Copy coupon code ---------- */
    function copyText(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise((resolve, reject) => {
            const ta = document.createElement('textarea');
            ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); resolve(); }
            catch (e) { reject(e); }
            finally { document.body.removeChild(ta); }
        });
    }

    function bindCouponButtons(selector) {
        document.querySelectorAll(selector).forEach((btn) => {
            btn.addEventListener('click', () => {
                const code = btn.dataset.code || btn.textContent.trim();
                if (!code) { toast('Mã trống — không có gì để copy.'); return; }
                copyText(code)
                    .then(() => toast('✓ Đã copy mã: ' + code))
                    .catch(() => toast('Không thể copy. Vui lòng copy thủ công: ' + code));
            });
        });
    }
    bindCouponButtons('.cd-coupon__code');
    bindCouponButtons('.cd-coupon-big__code');

    /* ---------- Hero slider ---------- */
    document.querySelectorAll('[data-cd-slider]').forEach((root) => {
        const slides = root.querySelectorAll('.cd-slider__slide');
        const dots   = root.querySelectorAll('.cd-slider__dot');
        const prev   = root.querySelector('.cd-slider__nav--prev');
        const next   = root.querySelector('.cd-slider__nav--next');
        const interval = parseInt(root.dataset.interval || '5000', 10);
        if (slides.length <= 1) return;

        let idx = 0; let timer;
        const go = (n) => {
            idx = (n + slides.length) % slides.length;
            slides.forEach((s, i) => s.classList.toggle('is-active', i === idx));
            dots.forEach((d, i)   => d.classList.toggle('is-active', i === idx));
        };
        const start = () => { stop(); timer = setInterval(() => go(idx + 1), interval); };
        const stop  = () => { if (timer) clearInterval(timer); };

        if (prev) prev.addEventListener('click', () => { go(idx - 1); start(); });
        if (next) next.addEventListener('click', () => { go(idx + 1); start(); });
        dots.forEach((d, i) => d.addEventListener('click', () => { go(i); start(); }));
        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', start);
        start();
    });

    /* ---------- Countdown timer ---------- */
    const countdowns = document.querySelectorAll('.cd-countdown');
    if (countdowns.length) {
        const tick = () => {
            const now = Date.now();
            countdowns.forEach((cd) => {
                const end = parseInt(cd.dataset.end || '0', 10);
                if (!end) return;
                let diff = Math.max(0, end - now);
                const d = Math.floor(diff / 86400000); diff -= d * 86400000;
                const h = Math.floor(diff / 3600000);  diff -= h * 3600000;
                const m = Math.floor(diff / 60000);    diff -= m * 60000;
                const s = Math.floor(diff / 1000);
                const set = (sel, val) => {
                    const el = cd.querySelector('[data-' + sel + ']');
                    if (el) el.textContent = String(val).padStart(2, '0');
                };
                set('d', d); set('h', h); set('m', m); set('s', s);
                if (end <= now) cd.classList.add('is-expired');
            });
        };
        tick(); setInterval(tick, 1000);
    }

    /* ---------- Newsletter (demo) ---------- */
    document.querySelectorAll('.cd-newsletter').forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = form.querySelector('input[type=email]').value;
            if (email) { toast('✓ Đã đăng ký: ' + email); form.reset(); }
        });
    });
})();
