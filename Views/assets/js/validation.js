/* ============================================================================
 * HB FITNESS — Blog Model | Client-Side Form Validation (validation.js)
 * Works alongside strict PHP server-side validation in Controllers/.
 * ============================================================================ */

document.addEventListener('DOMContentLoaded', () => {
    const showFieldError = (field, message) => {
        field.classList.add('is-invalid');
        let feedback = field.parentElement.querySelector('.hb-client-error');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'hb-client-error text-coral small mt-1';
            field.parentElement.appendChild(feedback);
        }
        feedback.textContent = message;
    };

    const clearFieldError = (field) => {
        field.classList.remove('is-invalid');
        const feedback = field.parentElement.querySelector('.hb-client-error');
        if (feedback) feedback.remove();
    };

    // Login Form Validation
    const loginForm = document.querySelector('form[data-validate="login"]');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            let valid = true;
            const email = loginForm.querySelector('input[name="email"]');
            const password = loginForm.querySelector('input[name="password"]');

            if (email) {
                clearFieldError(email);
                const val = email.value.trim();
                if (!val || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                    showFieldError(email, 'Please enter a valid email address.');
                    valid = false;
                }
            }
            if (password) {
                clearFieldError(password);
                if (password.value.length < 1) {
                    showFieldError(password, 'Password is required.');
                    valid = false;
                }
            }
            if (!valid) e.preventDefault();
        });
    }

    // Article Create / Update Validation
    document.querySelectorAll('form[data-validate="article"]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            let valid = true;
            const title = form.querySelector('input[name="title"]');
            const content = form.querySelector('textarea[name="content"]');
            const recipeId = form.querySelector('input[name="recipe_id"]');

            if (title) {
                clearFieldError(title);
                const len = title.value.trim().length;
                if (len < 5 || len > 180) {
                    showFieldError(title, 'Article title must be between 5 and 180 characters.');
                    valid = false;
                }
            }
            if (content) {
                clearFieldError(content);
                if (content.value.trim().length < 20) {
                    showFieldError(content, 'Article content must be at least 20 characters long.');
                    valid = false;
                }
            }
            if (recipeId && recipeId.value.trim() !== '') {
                clearFieldError(recipeId);
                if (!/^[1-9]\d*$/.test(recipeId.value.trim())) {
                    showFieldError(recipeId, 'Optional Recipe ID must be a positive whole number.');
                    valid = false;
                }
            }
            if (!valid) e.preventDefault();
        });
    });

    // Comment Create / Update Validation
    document.querySelectorAll('form[data-validate="comment"]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            let valid = true;
            const content = form.querySelector('textarea[name="content"]');
            if (content) {
                clearFieldError(content);
                const len = content.value.trim().length;
                if (len < 3 || len > 1000) {
                    showFieldError(content, 'Comment must be between 3 and 1000 characters.');
                    valid = false;
                }
            }
            if (!valid) e.preventDefault();
        });
    });
});
