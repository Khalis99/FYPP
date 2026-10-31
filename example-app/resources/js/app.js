/**
 * Pelita front-end behaviour.
 * Plain JavaScript only: theme, sidebar, dropdowns, command palette, delete confirmation,
 * toasts, loading buttons and a few small touches of delight (spotlight cards, count-up numbers).
 */

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Light / dark theme (the saved choice is applied by a tiny inline script in <head> to avoid a flash)
document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = next;

        try {
            localStorage.setItem('pelita-theme', next);
        } catch (e) {
            // Private mode: the choice simply lasts for this page view.
        }
    });
});

// Language switch: slide the thumb, then submit
document.querySelectorAll('[data-language-switch]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const toggle = form.querySelector('.lang-switch');

        if (form.dataset.sent || reducedMotion || !toggle) {
            return;
        }

        event.preventDefault();
        form.dataset.sent = '1';
        toggle.dataset.lang = toggle.dataset.lang === 'ms' ? 'en' : 'ms';
        setTimeout(() => form.submit(), 260);
    });
});

// Mobile sidebar
const sidebar = document.getElementById('sidebar');
const sidebarBackdrop = document.getElementById('sidebar-backdrop');

const setSidebar = (open) => {
    sidebar?.toggleAttribute('data-open', open);
    sidebarBackdrop?.classList.toggle('hidden', !open);
};

document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
    button.addEventListener('click', () => setSidebar(!sidebar?.hasAttribute('data-open')));
});

// Dropdown menus (profile, notifications)
document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
    const trigger = dropdown.querySelector('[data-dropdown-trigger]');
    const menu = dropdown.querySelector('[data-dropdown-menu]');

    trigger?.addEventListener('click', (event) => {
        event.stopPropagation();
        document.querySelectorAll('[data-dropdown-menu]').forEach((other) => other !== menu && other.classList.add('hidden'));
        menu.classList.toggle('hidden');
        trigger.setAttribute('aria-expanded', String(!menu.classList.contains('hidden')));
    });
});

document.addEventListener('click', (event) => {
    document.querySelectorAll('[data-dropdown-menu]').forEach((menu) => {
        if (!menu.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });
});

// Command palette: Ctrl/Cmd+K or "/" opens a quick "jump to" list
const palette = document.getElementById('palette');

if (palette) {
    const input = palette.querySelector('[data-palette-input]');
    const items = [...palette.querySelectorAll('[data-palette-item]')];
    const groups = [...palette.querySelectorAll('[data-palette-group]')];
    const empty = palette.querySelector('[data-palette-empty]');
    let active = 0;

    const visible = () => items.filter((item) => !item.hidden);

    const highlight = (index) => {
        const list = visible();

        if (!list.length) {
            return;
        }

        active = (index + list.length) % list.length;
        items.forEach((item) => item.removeAttribute('data-active'));
        list[active].setAttribute('data-active', '');
        list[active].scrollIntoView({ block: 'nearest' });
    };

    const filter = () => {
        const query = input.value.trim().toLowerCase();

        items.forEach((item) => {
            item.hidden = query !== '' && !item.dataset.text.includes(query);
        });
        groups.forEach((group) => {
            group.hidden = !group.querySelector('[data-palette-item]:not([hidden])');
        });
        empty.hidden = visible().length > 0;
        highlight(0);
    };

    const openPalette = () => {
        if (palette.open) {
            return;
        }

        palette.showModal();
        input.value = '';
        filter();
        input.focus();
    };

    document.querySelectorAll('[data-palette-open]').forEach((button) => button.addEventListener('click', openPalette));

    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            palette.open ? palette.close() : openPalette();
            return;
        }

        const typing = /^(input|textarea|select)$/i.test(event.target.tagName) || event.target.isContentEditable;

        if (event.key === '/' && !typing && !palette.open) {
            event.preventDefault();
            openPalette();
        }
    });

    input.addEventListener('input', filter);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            highlight(active + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            highlight(active - 1);
        } else if (event.key === 'Enter') {
            event.preventDefault();
            visible()[active]?.click();
        }
    });

    items.forEach((item) => item.addEventListener('pointermove', () => highlight(visible().indexOf(item))));
    palette.addEventListener('click', (event) => event.target === palette && palette.close());
}

// Escape also closes the mobile sidebar
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setSidebar(false);
    }
});

// Spotlight: cards glow where the cursor is
document.addEventListener('pointermove', (event) => {
    const card = event.target.closest?.('.card');

    if (card) {
        const box = card.getBoundingClientRect();
        card.style.setProperty('--mx', `${event.clientX - box.left}px`);
        card.style.setProperty('--my', `${event.clientY - box.top}px`);
    }

    const aurora = document.querySelector('[data-aurora]');

    if (aurora) {
        aurora.style.setProperty('--ax', `${(event.clientX / window.innerWidth) * 100}%`);
        aurora.style.setProperty('--ay', `${(event.clientY / window.innerHeight) * 100}%`);
    }
});

// Numbers count up when a page loads
if (!reducedMotion) {
    document.querySelectorAll('[data-countup]').forEach((element) => {
        const end = parseInt(element.textContent.replace(/[^0-9]/g, ''), 10);

        if (!Number.isFinite(end) || end <= 0) {
            return;
        }

        const start = performance.now();
        const duration = 900;
        const tick = (now) => {
            const progress = Math.min(1, (now - start) / duration);
            element.textContent = Math.round(end * (1 - Math.pow(1 - progress, 3)));

            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };

        element.textContent = '0';
        requestAnimationFrame(tick);
    });
}

// Show / hide button on every password field
const text = { showPassword: 'Show password', hidePassword: 'Hide password', ...window.pelitaText };
const eyeOpen = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />';
const eyeShut = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />';

document.querySelectorAll('input[type="password"]').forEach((field) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'relative';
    field.parentNode.insertBefore(wrapper, field);
    wrapper.appendChild(field);
    field.classList.add('pr-12');

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'absolute top-1/2 right-2 -translate-y-1/2 rounded-full p-1.5 text-slate-500 transition hover:bg-brand-50 hover:text-brand-700';
    toggle.setAttribute('aria-label', text.showPassword);
    toggle.innerHTML = `<svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">${eyeOpen}</svg>`;
    wrapper.appendChild(toggle);

    toggle.addEventListener('click', () => {
        const show = field.type === 'password';
        field.type = show ? 'text' : 'password';
        toggle.setAttribute('aria-label', show ? text.hidePassword : text.showPassword);
        toggle.querySelector('svg').innerHTML = show ? eyeShut : eyeOpen;
    });
});

// Confirmation dialog for forms marked with data-confirm (used by every delete button)
const confirmDialog = document.getElementById('confirm-dialog');
let pendingForm = null;

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!form.matches('form[data-confirm]') || form.dataset.confirmed === 'true' || !confirmDialog) {
        return;
    }

    event.preventDefault();
    pendingForm = form;
    confirmDialog.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
    confirmDialog.querySelector('[data-confirm-accept]').textContent = form.dataset.confirmButton || 'Yes, delete';
    confirmDialog.showModal();
});

confirmDialog?.querySelector('[data-confirm-accept]')?.addEventListener('click', () => {
    confirmDialog.close();
    if (pendingForm) {
        pendingForm.dataset.confirmed = 'true';
        pendingForm.requestSubmit();
    }
});

confirmDialog?.querySelector('[data-confirm-cancel]')?.addEventListener('click', () => {
    pendingForm = null;
    confirmDialog.close();
});

// Toast notifications auto-hide after 6 seconds
document.querySelectorAll('[data-toast]').forEach((toast) => {
    const hide = () => toast.remove();
    toast.querySelector('[data-toast-close]')?.addEventListener('click', hide);
    setTimeout(hide, 6000);
});

// Buttons with data-loading-text show progress and prevent double submission
document.addEventListener('submit', (event) => {
    if (event.defaultPrevented) {
        return;
    }

    const button = event.submitter;

    if (button?.dataset.loadingText) {
        button.disabled = true;
        button.textContent = button.dataset.loadingText;

        // A disabled submitter is not sent, so keep its name/value for the server.
        if (button.name) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = button.name;
            hidden.value = button.value;
            event.target.appendChild(hidden);
        }
    }
});

// Print button on the RPH print page
document.querySelectorAll('[data-print]').forEach((button) => {
    button.addEventListener('click', () => window.print());
});
