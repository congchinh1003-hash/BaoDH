/* Small shared behavior layer; existing application actions keep their handlers. */
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('tf-nav-toggle');
    const navigation = document.getElementById('tf-primary-navigation');
    const backdrop = document.getElementById('tf-nav-backdrop');
    if (toggle && navigation && backdrop) {
        const mobile = matchMedia('(max-width: 991px)');
        const focusables = () => [...navigation.querySelectorAll('a[href],button,[tabindex="0"]')].filter(el => el.getClientRects().length && !el.disabled);
        function setOpen(open, restoreFocus = true) {
            document.body.classList.toggle('tf-nav-open', open);
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Đóng menu điều hướng' : 'Mở menu điều hướng');
            backdrop.hidden = !open;
            navigation.inert = mobile.matches && !open;
            if (open) focusables()[0]?.focus();
            else if (restoreFocus && mobile.matches) toggle.focus();
        }
        navigation.inert = mobile.matches;
        toggle.addEventListener('click', () => setOpen(!document.body.classList.contains('tf-nav-open')));
        backdrop.addEventListener('click', () => setOpen(false));
        navigation.querySelectorAll('[data-close-navigation]').forEach(button => button.addEventListener('click', () => setOpen(false)));
        navigation.addEventListener('click', event => { if (mobile.matches && event.target.closest('a[href]')) setOpen(false, false); });
        mobile.addEventListener('change', () => setOpen(false, false));
        document.addEventListener('keydown', event => {
            if (!document.body.classList.contains('tf-nav-open')) return;
            if (event.key === 'Escape') { setOpen(false); return; }
            if (event.key === 'Tab') {
                const items = focusables(), first = items[0], last = items.at(-1);
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
                else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
            }
        });
    }
    document.querySelectorAll('.quick-role-card,.quick-user-chip,.account-card,.qr-banner-card,.soft-ticket-card').forEach(makeKeyboardAction);
    function makeKeyboardAction(element) {
        if (!element.hasAttribute('onclick') || element.tagName === 'BUTTON') return;
        element.tabIndex = 0;
        element.setAttribute('role', 'button');
        element.addEventListener('keydown', event => {
            if (event.target === element && (event.key === 'Enter' || event.key === ' ')) { event.preventDefault(); element.click(); }
        });
    }
    const tickets = document.getElementById('ticketsContainer');
    if (tickets) new MutationObserver(() => tickets.querySelectorAll('.soft-ticket-card').forEach(el => { if (!el.hasAttribute('tabindex')) makeKeyboardAction(el); })).observe(tickets, {childList: true});
    if (document.body.classList.contains('tf-mobile-app') && window.visualViewport) {
        const updateViewport = () => {
            document.documentElement.style.setProperty('--tf-visual-height', `${visualViewport.height}px`);
            document.body.classList.toggle('tf-keyboard-open', innerHeight - visualViewport.height > 150);
        };
        visualViewport.addEventListener('resize', updateViewport);
        updateViewport();
    }
});
