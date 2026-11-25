<style>
    .password-reveal { position: relative; width: 100%; }
    .password-reveal > input { width: 100%; padding-right: 3rem; }
    .input-group > .password-reveal { flex: 1 1 auto; width: 1%; min-width: 0; }
    .input-group > .password-reveal > input { border-top-left-radius: 0; border-bottom-left-radius: 0; }
    .password-reveal-button {
        position: absolute; top: 1px; right: 1px; bottom: 1px; width: 2.75rem;
        display: flex; align-items: center; justify-content: center;
        border: 0; border-radius: 0 .375rem .375rem 0;
        background: transparent; color: #6c757d; cursor: pointer;
        touch-action: none; user-select: none; -webkit-user-select: none;
        -webkit-touch-callout: none;
    }
    .password-reveal-button:hover { color: #b33c12; background: #fff3ed; }
    .password-reveal-button[aria-pressed="true"] { color: #fff; background: #d94b18; }
    .password-reveal-button:focus-visible { outline: 2px solid #d94b18; outline-offset: 2px; z-index: 2; }
    .password-reveal.is-revealed > input { border-color: #ff6b35; box-shadow: 0 0 0 .2rem rgba(255,107,53,.2); }
    input[data-hold-password]::-ms-reveal,
    input[data-hold-password]::-ms-clear { display: none; }
</style>
<script>
(() => {
    const hideHandlers = [];
    const hideAll = () => hideHandlers.forEach(hide => hide());
    document.querySelectorAll('input[type="password"]').forEach((input, index) => {
        if (input.hasAttribute('data-hold-password')) return;
        input.setAttribute('data-hold-password', '');
        if (!input.id) input.id = 'holdPassword' + index;
        const wrapper = document.createElement('div');
        wrapper.className = 'password-reveal';
        input.before(wrapper);
        wrapper.append(input);
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'password-reveal-button';
        const label = input.name === 'confirm_password' ? 'Nhấn giữ để xem mật khẩu xác nhận' : 'Nhấn giữ để xem mật khẩu';
        button.setAttribute('aria-label', label);
        button.setAttribute('aria-controls', input.id);
        button.setAttribute('aria-pressed', 'false');
        button.title = label;
        button.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
        wrapper.append(button);
        let pointerId = null;
        let heldKey = null;
        const hide = () => {
            input.type = 'password';
            wrapper.classList.remove('is-revealed');
            button.setAttribute('aria-pressed', 'false');
            heldKey = null;
            const captured = pointerId;
            pointerId = null;
            if (captured !== null && button.hasPointerCapture(captured)) button.releasePointerCapture(captured);
        };
        const show = () => {
            hideAll();
            input.type = 'text';
            wrapper.classList.add('is-revealed');
            button.setAttribute('aria-pressed', 'true');
        };
        hideHandlers.push(hide);
        button.addEventListener('pointerdown', event => {
            if (event.button !== 0 || !event.isPrimary) return;
            event.preventDefault();
            show();
            pointerId = event.pointerId;
            button.setPointerCapture(event.pointerId);
        });
        button.addEventListener('pointermove', event => {
            if (pointerId !== event.pointerId) return;
            const rect = button.getBoundingClientRect();
            if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) hide();
        });
        button.addEventListener('pointerleave', hide);
        button.addEventListener('lostpointercapture', hide);
        button.addEventListener('blur', hide);
        button.addEventListener('contextmenu', event => event.preventDefault());
        button.addEventListener('click', event => event.preventDefault());
        button.addEventListener('keydown', event => {
            if (event.key !== ' ' && event.key !== 'Enter') return;
            event.preventDefault();
            if (!event.repeat) { show(); heldKey = event.key; }
        });
        button.addEventListener('keyup', event => {
            if (event.key === heldKey) { event.preventDefault(); hide(); }
        });
        input.form?.addEventListener('submit', hideAll);
        input.form?.addEventListener('reset', hideAll);
    });
    document.addEventListener('pointerup', hideAll, true);
    document.addEventListener('pointercancel', hideAll, true);
    document.addEventListener('keydown', event => { if (event.key === 'Escape') hideAll(); });
    window.addEventListener('blur', hideAll);
    window.addEventListener('pagehide', hideAll);
    document.addEventListener('visibilitychange', () => { if (document.hidden) hideAll(); });
})();
</script>
