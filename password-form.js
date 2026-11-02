(() => {
    document.querySelectorAll('[data-password-form]').forEach(form => {
        const password = form.querySelector('input[name="password"]');
        const confirmation = form.querySelector('input[name="password_confirm"]');
        const meter = form.querySelector('[data-password-strength]');
        const strengthText = form.querySelector('[data-password-strength-text]');
        const matchText = form.querySelector('[data-password-match]');
        if (!password || !confirmation || !meter || !strengthText || !matchText) return;

        const labels = ['弱い', '普通', '良好', '強い'];
        const updateStrength = () => {
            const value = password.value;
            const characterLength = Array.from(value).length;
            const categories = [/[a-z]/.test(value), /[A-Z]/.test(value), /\d/.test(value), /[^A-Za-z0-9]/u.test(value)].filter(Boolean).length;
            const score = characterLength === 0 ? 0 : Math.min(4, (characterLength >= 8 ? 1 : 0) + (characterLength >= 12 ? 1 : 0) + (categories >= 3 ? 1 : 0) + (categories === 4 ? 1 : 0));
            meter.value = score;
            meter.setAttribute('aria-valuetext', score ? labels[score - 1] : '未入力');
            strengthText.textContent = score ? `強度の目安：${labels[score - 1]}` : 'パスワードを入力してください。';

            const byteLength = new TextEncoder().encode(value).length;
            const validationMessage = characterLength > 0 && characterLength < 8
                ? 'パスワードは8文字以上で入力してください。'
                : byteLength > 72 ? 'UTF-8で72バイト以内で入力してください。' : '';
            password.setCustomValidity(validationMessage);
            updateMatch();
        };
        const updateMatch = () => {
            const matches = !confirmation.value || password.value === confirmation.value;
            confirmation.setCustomValidity(matches ? '' : '新しいパスワードが一致しません。');
            matchText.textContent = confirmation.value ? (matches ? 'パスワードが一致しています。' : 'パスワードが一致しません。') : '';
        };

        form.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.getAttribute('aria-controls'));
                if (!input || !form.contains(input)) return;
                const visible = input.type === 'password';
                input.type = visible ? 'text' : 'password';
                button.setAttribute('aria-pressed', String(visible));
                button.textContent = visible ? '隠す' : '表示';
            });
        });

        password.addEventListener('input', updateStrength);
        confirmation.addEventListener('input', updateMatch);
        updateStrength();
    });
})();
