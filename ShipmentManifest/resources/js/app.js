// Sidebar responsif (layar < md)
document.addEventListener('click', (e) => {
    const toggle = e.target.closest('[data-sidebar-toggle]');
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;

    if (toggle) {
        sidebar.classList.toggle('-translate-x-full');
        document.getElementById('sidebar-backdrop')?.classList.toggle('hidden');
    } else if (e.target.id === 'sidebar-backdrop') {
        sidebar.classList.add('-translate-x-full');
        e.target.classList.add('hidden');
    }
});
