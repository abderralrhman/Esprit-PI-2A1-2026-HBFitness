'use strict';

document.querySelectorAll('form[data-module4-validate]').forEach((form) => {
    const inputs = [...form.querySelectorAll('input:not([type="hidden"]), textarea')];

    function validationMessage(input) {
        const value = input.value.trim();
        if (input.required && value === '') {
            return 'Fill in this field.';
        }
        const maxCharacters = Number(input.dataset.maxCharacters);
        if (maxCharacters && [...value].length > maxCharacters) {
            return `Use at most ${maxCharacters} characters.`;
        }
        if (input.name === 'description' && new TextEncoder().encode(value).length > 65535) {
            return 'Use at most 65,535 bytes.';
        }
        if (input.name === 'capacity' && (!/^\d{1,10}$/.test(value) || Number(value) > 4294967295)) {
            return 'Enter a whole number from 0 to 4294967295.';
        }
        if (input.name === 'website' && value !== '' && !/^https?:\/\//i.test(value)) {
            return 'Enter an HTTP or HTTPS URL.';
        }
        if (input.name === 'logo' && value !== ''
            && !/^Views\/assets\/images\/[A-Za-z0-9][A-Za-z0-9._-]*\.(png|jpe?g|webp|gif|svg)$/i.test(value)) {
            return 'Enter a path to an image under Views/assets/images/.';
        }
        return '';
    }

    inputs.forEach((input) => {
        input.addEventListener('input', () => {
            input.setCustomValidity(validationMessage(input));
            input.classList.remove('is-invalid');
            input.removeAttribute('aria-invalid');
        });
    });

    form.addEventListener('submit', (event) => {
        inputs.forEach((input) => input.setCustomValidity(validationMessage(input)));
        if (!form.reportValidity()) {
            event.preventDefault();
        }
    });
});
