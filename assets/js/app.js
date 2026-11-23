(() => {
    const root = document.documentElement;
    document.querySelectorAll('.theme-toggle').forEach((button) => {
        const updateIcon = () => {
            const dark = root.dataset.theme === 'dark';
            button.textContent = dark ? '☀' : '☾';
            button.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
        };
        updateIcon();
        button.addEventListener('click', () => {
            const dark = root.dataset.theme !== 'dark';
            if (dark) root.dataset.theme = 'dark';
            else delete root.dataset.theme;
            localStorage.setItem('stockflow-theme', dark ? 'dark' : 'light');
            updateIcon();
        });
    });

    document.querySelectorAll('[data-table-search]').forEach((input) => {
        const table = document.querySelector(input.dataset.tableSearch);
        if (!table) return;
        input.addEventListener('input', () => {
            const query = input.value.toLocaleLowerCase();
            table.querySelectorAll('tbody tr').forEach((row) => {
                row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
            });
        });
    });

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
})();
