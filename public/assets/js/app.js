'use strict';

/** Close flash messages. */
document.querySelectorAll('[data-dismiss]').forEach((button) => {
    button.addEventListener('click', () => button.closest('.alert')?.remove());
});

/** Copy the e-mail address in the footer. */
document.querySelectorAll('[data-copy]').forEach((button) => {
    const original = button.textContent;

    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(button.dataset.copy);
            button.textContent = button.dataset.copied;
            setTimeout(() => { button.textContent = original; }, 1500);
        } catch {
            window.location.href = `mailto:${button.dataset.copy}`;
        }
    });
});

/** Ask before submitting forms marked with data-confirm (e.g. delete buttons). */
const dialog = document.getElementById('confirm-dialog');

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (form.dataset.confirmed) {
            return;
        }

        event.preventDefault();

        if (!dialog?.showModal) {
            if (window.confirm(form.dataset.confirm)) {
                form.dataset.confirmed = '1';
                form.submit();
            }
            return;
        }

        dialog.querySelector('[data-message]').textContent = form.dataset.confirm;
        dialog.returnValue = '';
        dialog.showModal();
        dialog.addEventListener('close', () => {
            if (dialog.returnValue === 'confirm') {
                form.dataset.confirmed = '1';
                form.submit();
            }
        }, { once: true });
    });
});

/** Mobile sidebar toggle in the admin dashboard. */
const sidebar = document.getElementById('sidebar');

document.querySelector('[data-toggle="sidebar"]')?.addEventListener('click', (event) => {
    event.stopPropagation();
    sidebar?.classList.toggle('is-open');
});

document.addEventListener('click', (event) => {
    if (sidebar?.classList.contains('is-open') && !sidebar.contains(event.target)) {
        sidebar.classList.remove('is-open');
    }
});

/** Stock entry form: show the unit of the chosen product and the total (quantity × price) while typing. */
document.querySelectorAll('[data-entry-form]').forEach((form) => {
    const product = form.elements.product_id;
    const quantity = form.elements.quantity;
    const price = form.elements.unit_price;
    const unitLabel = form.querySelector('[data-unit-label]');
    const total = form.querySelector('[data-total]');
    const money = new Intl.NumberFormat(document.documentElement.lang, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const update = () => {
        const unit = product.selectedOptions[0]?.dataset.unit ?? '';
        unitLabel.textContent = unit ? `(${unit})` : '';

        const q = parseFloat(quantity.value);
        const p = parseFloat(price.value);
        total.textContent = Number.isFinite(q) && Number.isFinite(p)
            ? `${money.format(Math.round(q * p * 100) / 100)} ${form.dataset.currency}`
            : '—';
    };

    [product, quantity, price].forEach((field) => field.addEventListener('input', update));
    update();
});

/** Print buttons (stock report). */
document.querySelectorAll('[data-print]').forEach((button) => {
    button.addEventListener('click', () => window.print());
});
