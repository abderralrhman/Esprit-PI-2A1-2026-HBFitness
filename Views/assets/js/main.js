/**
 * Views/assets/js/main.js
 * Dynamic Frontoffice & Backoffice UI Interactions + Transitions (HB FITNESS)
 */
document.addEventListener('DOMContentLoaded', () => {
    initParticleCanvas();
    initTypedHeroWord();
    initScrollReveal();
    initAnimatedCounters();
    initNavbarScroll();
    initMobileSidebar();
    initPasswordToggles();
    initAutoDismissFlash();
});

/* 1. Connected Particle Canvas (#hb-canvas) */
function initParticleCanvas() {
    const canvas = document.getElementById('hb-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let width  = (canvas.width = window.innerWidth);
    let height = (canvas.height = window.innerHeight);

    window.addEventListener('resize', () => {
        width  = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    });

    const nodes = Array.from({ length: 34 }, () => ({
        x: Math.random() * width,
        y: Math.random() * height,
        vx: (Math.random() - 0.5) * 0.6,
        vy: (Math.random() - 0.5) * 0.6,
        r: Math.random() * 2.2 + 1.3,
        color: Math.random() > 0.4 ? '#E8405E' : '#B4B4B4'
    }));

    function draw() {
        ctx.clearRect(0, 0, width, height);
        for (let i = 0; i < nodes.length; i++) {
            const n = nodes[i];
            n.x += n.vx;
            n.y += n.vy;
            if (n.x < 0 || n.x > width) n.vx *= -1;
            if (n.y < 0 || n.y > height) n.vy *= -1;

            ctx.beginPath();
            ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
            ctx.fillStyle = n.color;
            ctx.fill();

            for (let j = i + 1; j < nodes.length; j++) {
                const m = nodes[j];
                const dist = Math.hypot(n.x - m.x, n.y - m.y);
                if (dist < 140) {
                    ctx.beginPath();
                    ctx.moveTo(n.x, n.y);
                    ctx.lineTo(m.x, m.y);
                    ctx.strokeStyle = `rgba(145, 3, 41, ${0.28 * (1 - dist / 140)})`;
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }
            }
        }
        requestAnimationFrame(draw);
    }
    draw();
}

/* 2. Dynamic Typing / Rotating Hero Word */
function initTypedHeroWord() {
    const el = document.getElementById('typedWord');
    if (!el) return;
    const words = ['STRONGER', 'FASTER', 'FITTER', 'HEALTHIER', 'UNSTOPPABLE'];
    let w = 0;
    let c = words[0].length;
    let deleting = true;

    function tick() {
        const word = words[w];
        el.textContent = word.substring(0, c);
        if (deleting) {
            c--;
            if (c < 0) {
                deleting = false;
                w = (w + 1) % words.length;
                c = 0;
            }
        } else {
            c++;
            if (c > words[w].length) {
                deleting = true;
                setTimeout(tick, 1500);
                return;
            }
        }
        setTimeout(tick, deleting ? 55 : 105);
    }
    setTimeout(tick, 1600);
}

/* 3. Smooth Scroll Reveal Transitions */
function initScrollReveal() {
    const els = document.querySelectorAll('.reveal');
    if (!els.length) return;
    if (!('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('visible'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    els.forEach((el, idx) => {
        el.style.transitionDelay = `${(idx % 4) * 0.08}s`;
        io.observe(el);
    });
}

/* 4. Smooth Animated KPI Number Counters */
function initAnimatedCounters() {
    document.querySelectorAll('.counter-value').forEach((counter) => {
        const target = parseInt(counter.getAttribute('data-target') || '0', 10);
        const duration = 950;
        const start = performance.now();

        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            counter.textContent = Math.floor(eased * target).toLocaleString();
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                counter.textContent = target.toLocaleString();
            }
        }
        requestAnimationFrame(step);
    });
}

/* 5. Navbar Scroll Elevation */
function initNavbarScroll() {
    const nav = document.querySelector('.fo-navbar');
    if (!nav) return;
    window.addEventListener('scroll', () => {
        nav.classList.toggle('scrolled', window.scrollY > 12);
    });
}

/* 6. Mobile Backoffice Sidebar Toggle */
function initMobileSidebar() {
    const toggleBtn = document.getElementById('boSidebarToggle');
    const sidebar   = document.getElementById('boSidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
    }
}

/* 7. Password Show/Hide Toggles */
function initPasswordToggles() {
    document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.getAttribute('data-toggle-password'));
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = `<i class="fa-solid ${show ? 'fa-eye-slash' : 'fa-eye'}"></i>`;
        });
    });
}

/* 8. Auto-dismiss Flash Alerts */
function initAutoDismissFlash() {
    document.querySelectorAll('.hb-flash-auto').forEach((el) => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s ease';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 6000);
    });
}
