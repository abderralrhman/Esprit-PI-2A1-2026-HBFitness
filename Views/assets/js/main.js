/* ============================================================================
 * HB FITNESS — Blog Model | Core UI & Animations (main.js)
 * ============================================================================ */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Navbar scroll transition
    const navbar = document.querySelector('.hb-navbar');
    if (navbar) {
        const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 24);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    // 2. Scroll reveal observer
    const reveals = document.querySelectorAll('.reveal');
    if (reveals.length > 0 && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        reveals.forEach((el) => observer.observe(el));
    } else {
        reveals.forEach((el) => el.classList.add('is-visible'));
    }

    // 3. Typed word animation in Hero
    const typedEl = document.getElementById('hb-typed-word');
    if (typedEl) {
        const words = ['STRONGER', 'SMARTER', 'FASTER', 'TOGETHER'];
        let wordIdx = 0;
        let charIdx = 0;
        let deleting = false;

        const tick = () => {
            const word = words[wordIdx];
            if (!deleting) {
                charIdx++;
                typedEl.textContent = word.slice(0, charIdx);
                if (charIdx === word.length) {
                    deleting = true;
                    setTimeout(tick, 1700);
                    return;
                }
                setTimeout(tick, 95);
            } else {
                charIdx--;
                typedEl.textContent = word.slice(0, charIdx);
                if (charIdx === 0) {
                    deleting = false;
                    wordIdx = (wordIdx + 1) % words.length;
                    setTimeout(tick, 350);
                    return;
                }
                setTimeout(tick, 55);
            }
        };
        tick();
    }

    // 4. Animated counters
    document.querySelectorAll('[data-count]').forEach((el) => {
        const target = parseInt(el.getAttribute('data-count') || '0', 10);
        if (isNaN(target) || target <= 0) {
            el.textContent = String(target || 0);
            return;
        }
        const duration = 700;
        const start = performance.now();
        const step = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = String(Math.round(target * eased));
            if (progress < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    });

    // 5. Subtle crimson particle background canvas
    const canvas = document.getElementById('hb-canvas');
    if (canvas && canvas.getContext) {
        const ctx = canvas.getContext('2d');
        let width = 0;
        let height = 0;
        const particles = [];
        const count = 34;

        const resize = () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        };
        resize();
        window.addEventListener('resize', resize);

        for (let i = 0; i < count; i++) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                r: Math.random() * 2 + 0.8,
                vx: (Math.random() - 0.5) * 0.35,
                vy: (Math.random() - 0.5) * 0.35,
                alpha: Math.random() * 0.45 + 0.15,
            });
        }

        const draw = () => {
            ctx.clearRect(0, 0, width, height);
            for (const p of particles) {
                p.x += p.vx;
                p.y += p.vy;
                if (p.x < 0) p.x = width;
                if (p.x > width) p.x = 0;
                if (p.y < 0) p.y = height;
                if (p.y > height) p.y = 0;

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(232, 64, 94, ${p.alpha})`;
                ctx.fill();
            }
            requestAnimationFrame(draw);
        };
        draw();
    }

    // 6. Backoffice mobile sidebar toggle
    const sidebar = document.getElementById('hb-sidebar');
    const backdrop = document.getElementById('hb-sidebar-backdrop');
    const toggleBtn = document.getElementById('hb-sidebar-toggle');
    if (sidebar && toggleBtn) {
        const toggle = () => {
            sidebar.classList.toggle('open');
            if (backdrop) backdrop.classList.toggle('show');
        };
        toggleBtn.addEventListener('click', toggle);
        if (backdrop) backdrop.addEventListener('click', toggle);
    }

    // 7. Password visibility toggle
    document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-toggle-password');
            const input = targetId ? document.getElementById(targetId) : null;
            if (!input) return;
            const isPwd = input.type === 'password';
            input.type = isPwd ? 'text' : 'password';
            const icon = btn.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', !isPwd);
                icon.classList.toggle('fa-eye-slash', isPwd);
            }
        });
    });

    // 8. Auto-dismiss flash alert after 6 seconds
    const flash = document.querySelector('[data-auto-dismiss]');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.4s ease';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 420);
        }, 6000);
    }
});
