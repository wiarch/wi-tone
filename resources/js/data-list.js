import { DataTable } from 'simple-datatables';
import 'simple-datatables/dist/style.css';

const TABLE_OPTIONS = {
    searchable: true,
    sortable: true,
    paging: false,
    perPageSelect: false,
    labels: {
        placeholder: 'Buscar…',
        perPage: '{select} filas por página',
        noRows: 'Sin filas',
        noResults: 'Sin coincidencias',
        info: '{start}–{end} de {rows}',
    },
};

const instances = new WeakMap();

function initTable(table) {
    if (instances.has(table)) {
        return;
    }

    instances.set(
        table,
        new DataTable(table, TABLE_OPTIONS),
    );
}

function destroyTable(table) {
    const instance = instances.get(table);
    if (!instance) {
        return;
    }

    instance.destroy();
    instances.delete(table);
}

function bindCardSearch(root) {
    const input = root.querySelector('[data-card-search]');
    const cards = root.querySelectorAll('[data-list-card]');

    if (!input || !cards.length) {
        return;
    }

    input.addEventListener('input', () => {
        const query = input.value.trim().toLowerCase();

        cards.forEach((card) => {
            const text = (card.dataset.searchText ?? card.textContent ?? '').toLowerCase();
            card.classList.toggle('hidden', query.length > 0 && !text.includes(query));
        });
    });
}

function syncTables() {
    const isDesktop = window.matchMedia('(min-width: 1024px)').matches;

    document.querySelectorAll('[data-responsive-data-list]').forEach((root) => {
        const table = root.querySelector('[data-datatable]');
        if (!table) {
            return;
        }

        if (isDesktop) {
            initTable(table);
        } else {
            destroyTable(table);
        }
    });
}

function initDataLists() {
    document.querySelectorAll('[data-responsive-data-list]').forEach((root) => {
        bindCardSearch(root);
    });

    syncTables();
    window.addEventListener('resize', syncTables);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDataLists);
} else {
    initDataLists();
}
