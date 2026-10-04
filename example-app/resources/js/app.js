/**
 * SchoolHub front-end behaviour.
 * Plain JavaScript only: sidebar, dropdowns, delete confirmation, toasts and loading buttons.
 */

// Mobile sidebar
document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('sidebar')?.classList.toggle('-translate-x-full');
        document.getElementById('sidebar-backdrop')?.classList.toggle('hidden');
    });
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
