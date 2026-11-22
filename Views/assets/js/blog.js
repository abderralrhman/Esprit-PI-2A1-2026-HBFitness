/* ============================================================================
 * HB FITNESS — Blog Model | Interactive Blog Helpers (blog.js)
 * ============================================================================ */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Live character counter for textareas with data-max-chars
    document.querySelectorAll('textarea[data-max-chars]').forEach((textarea) => {
        const max = parseInt(textarea.getAttribute('data-max-chars') || '1000', 10);
        const counterId = textarea.getAttribute('data-counter-target');
        const counter = counterId ? document.getElementById(counterId) : null;
        if (!counter) return;

        const update = () => {
            const len = textarea.value.trim().length;
            counter.textContent = `${len} / ${max} characters`;
            counter.classList.remove('near-limit', 'over-limit');
            if (len > max) {
                counter.classList.add('over-limit');
            } else if (len > max * 0.85) {
                counter.classList.add('near-limit');
            }
        };
        update();
        textarea.addEventListener('input', update);
    });

    // 2. Smooth scroll to comment form button
    document.querySelectorAll('[data-scroll-to]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            const sel = btn.getAttribute('data-scroll-to');
            const target = sel ? document.querySelector(sel) : null;
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const input = target.querySelector('textarea');
                if (input) setTimeout(() => input.focus(), 400);
            }
        });
    });
});
