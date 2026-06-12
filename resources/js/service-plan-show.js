function initServicePlanShow() {
    const list = document.querySelector('[data-plan-setlist]');
    if (!list) {
        return;
    }

    const reorderUrl = list.dataset.reorderUrl;
    const csrf = list.dataset.csrf;
    let dragItem = null;

    function updateOrderBadges() {
        list.querySelectorAll('[data-song-id]').forEach((item, index) => {
            const badge = item.querySelector('[data-order-badge]');
            if (badge) {
                badge.textContent = String(index + 1);
            }
        });
    }

    async function persistOrder() {
        const order = [...list.querySelectorAll('[data-song-id]')].map((item) => Number(item.dataset.songId));

        const response = await fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ order }),
        });

        if (!response.ok) {
            window.location.reload();
        }
    }

    list.querySelectorAll('[data-song-id]').forEach((item) => {
        item.setAttribute('draggable', 'true');

        item.addEventListener('dragstart', () => {
            dragItem = item;
            item.classList.add('opacity-50');
        });

        item.addEventListener('dragend', () => {
            item.classList.remove('opacity-50');
            dragItem = null;
        });

        item.addEventListener('dragover', (event) => {
            event.preventDefault();
            if (!dragItem || dragItem === item) {
                return;
            }

            const rect = item.getBoundingClientRect();
            const after = event.clientY > rect.top + rect.height / 2;
            list.insertBefore(dragItem, after ? item.nextSibling : item);
        });

        item.addEventListener('drop', (event) => {
            event.preventDefault();
            updateOrderBadges();
            persistOrder();
        });
    });

    list.querySelectorAll('[data-drag-handle]').forEach((handle) => {
        handle.addEventListener('mousedown', () => {
            handle.closest('[data-song-id]')?.setAttribute('draggable', 'true');
        });
    });
}

document.addEventListener('DOMContentLoaded', initServicePlanShow);
