/**
 * Views/assets/js/validation.js
 * Client-side form validation (always paired with PHP server-side validation)
 */
document.addEventListener('DOMContentLoaded', () => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const nameRegex  = /^[\p{L}\s\-'\.]+$/u;
    const phoneRegex = /^\+?[0-9\s\-]{8,20}$/;

    function setFieldError(input, msg) {
        if (!input) return true;
        let errEl = input.parentElement.querySelector('.field-error');
        if (!errEl && input.closest('.mb-3')) {
            errEl = input.closest('.mb-3').querySelector('.field-error');
        }
        if (errEl) errEl.textContent = msg;
        input.classList.toggle('is-invalid', Boolean(msg));
        return !msg;
    }

    // Password strength meter (optional on register/create/edit forms)
    document.querySelectorAll('[data-strength-bar]').forEach((pwdInput) => {
        const bar = document.getElementById(pwdInput.getAttribute('data-strength-bar'));
        const lbl = document.getElementById(pwdInput.getAttribute('data-strength-label'));
        if (!bar) return;

        pwdInput.addEventListener('input', () => {
            const v = pwdInput.value;
            let score = 0;
            if (v.length >= 8) score++;
            if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;

            const levels = [
                ['0%', '#27272a', ''],
                ['25%', '#E8405E', 'Weak'],
                ['50%', '#f59e0b', 'Fair'],
                ['75%', '#B4B4B4', 'Good'],
                ['100%', '#10b981', 'Strong']
            ];
            const [w, color, text] = levels[v ? Math.max(score, 1) : 0];
            bar.style.width = w;
            bar.style.backgroundColor = color;
            if (lbl) lbl.textContent = text ? `Password strength: ${text}` : '';
        });
    });

    // Generic form validation via data-validate="true"
    document.querySelectorAll('form[data-validate="true"]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            let valid = true;

            const fullName = form.querySelector('[name="full_name"]');
            if (fullName) {
                const v = fullName.value.trim();
                const ok = setFieldError(
                    fullName,
                    (v.length >= 3 && v.length <= 120 && nameRegex.test(v))
                        ? ''
                        : 'Full name must contain only letters and spaces (3–120 characters).'
                );
                if (!ok) valid = false;
            }

            const email = form.querySelector('[name="email"]');
            if (email) {
                const ok = setFieldError(
                    email,
                    emailRegex.test(email.value.trim()) ? '' : 'Please enter a valid email address.'
                );
                if (!ok) valid = false;
            }

            const phone = form.querySelector('[name="phone"]');
            if (phone) {
                const v = phone.value.trim();
                const ok = setFieldError(
                    phone,
                    (v === '' || phoneRegex.test(v)) ? '' : 'Phone must contain 8 to 20 digits.'
                );
                if (!ok) valid = false;
            }

            const password = form.querySelector('[name="password"], [name="new_password"]');
            if (password) {
                const isRequired = password.hasAttribute('required');
                const v = password.value;
                const isLogin = form.dataset.formType === 'login';
                let msg = '';
                if (isLogin) {
                    if (v === '') msg = 'Password is required.';
                } else if (isRequired || v !== '') {
                    if (v.length < 8 || !/[A-Za-z]/.test(v) || !/[0-9]/.test(v)) {
                        msg = 'Password must be at least 8 characters and include letters and digits.';
                    }
                }
                if (!setFieldError(password, msg)) valid = false;

                const confirm = form.querySelector('[name="password_confirm"], [name="new_password_confirm"]');
                if (confirm && (isRequired || v !== '')) {
                    const ok = setFieldError(
                        confirm,
                        confirm.value === v ? '' : 'Passwords do not match.'
                    );
                    if (!ok) valid = false;
                }
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    });
});
