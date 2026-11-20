/**
 * HB FITNESS Interactive JS Animations & UI Controller
 * Graphic Charter: Black (#030303), Crimson (#910329), Silver (#B4B4B4), Charcoal (#1A1A1D), Coral Red (#E8405E)
 */

document.addEventListener('DOMContentLoaded', () => {
    initFitnessParticles();
    initAnimatedCounters();
    initProgressBars();
    initModuleTabs();
    initTableSearch();
    initAutoDismissToast();
});

/* 1. Interactive Connected Athletic Pulse Nodes Canvas Background */
function initFitnessParticles() {
    const canvas = document.getElementById('fitness-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let width = (canvas.width = window.innerWidth);
    let height = (canvas.height = window.innerHeight);

    window.addEventListener('resize', () => {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    });

    const nodeCount = 36;
    const nodes = Array.from({ length: nodeCount }, () => ({
        x: Math.random() * width,
        y: Math.random() * height,
        vx: (Math.random() - 0.5) * 0.65,
        vy: (Math.random() - 0.5) * 0.65,
        radius: Math.random() * 2.5 + 1.5,
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
            ctx.arc(n.x, n.y, n.radius, 0, Math.PI * 2);
            ctx.fillStyle = n.color;
            ctx.fill();

            for (let j = i + 1; j < nodes.length; j++) {
                const m = nodes[j];
                const dx = n.x - m.x;
                const dy = n.y - m.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 145) {
                    ctx.beginPath();
                    ctx.moveTo(n.x, n.y);
                    ctx.lineTo(m.x, m.y);
                    ctx.strokeStyle = `rgba(145, 3, 41, ${0.28 * (1 - dist / 145)})`;
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }
            }
        }
        requestAnimationFrame(draw);
    }
    draw();
}

/* 2. Smooth Number Counter Animation */
function initAnimatedCounters() {
    const counters = document.querySelectorAll('.counter-value');
    counters.forEach(counter => {
        const target = parseFloat(counter.getAttribute('data-target') || '0');
        const suffix = counter.getAttribute('data-suffix') || '';
        const duration = 1050;
        const startTime = performance.now();

        function updateCount(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(eased * target);
            counter.textContent = current.toLocaleString() + suffix;
            if (progress < 1) {
                requestAnimationFrame(updateCount);
            } else {
                counter.textContent = target.toLocaleString() + suffix;
            }
        }
        requestAnimationFrame(updateCount);
    });
}

/* 3. Animated Progress Bars */
function initProgressBars() {
    setTimeout(() => {
        document.querySelectorAll('.progress-fill').forEach(bar => {
            const pct = bar.getAttribute('data-progress') || '0';
            bar.style.width = Math.min(100, parseFloat(pct)) + '%';
        });
    }, 150);
}

/* 4. Smooth Tab Navigation Between Modules */
function initModuleTabs() {
    const buttons = document.querySelectorAll('[data-tab-target]');
    const sections = document.querySelectorAll('.module-section');

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-tab-target');
            buttons.forEach(b => b.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));

            btn.classList.add('active');
            const targetSec = document.getElementById(targetId);
            if (targetSec) {
                targetSec.classList.add('active');
                initProgressBars();
            }
        });
    });
}

/* 5. Instant Live Filter for Tables & Cards */
function initTableSearch() {
    document.querySelectorAll('[data-search-target]').forEach(input => {
        input.addEventListener('input', () => {
            const query = input.value.toLowerCase().trim();
            const selector = input.getAttribute('data-search-target');
            document.querySelectorAll(selector).forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });
}

/* 6. Auto-Dismiss Toast Notifications */
function initAutoDismissToast() {
    const toast = document.querySelector('.toast-alert');
    if (toast) {
        setTimeout(() => {
            toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 400);
        }, 4800);
    }
}
