import Alpine from 'alpinejs';

Alpine.data('adminShell', () => ({
    sidebarOpen: false,
    compact: false,
    opener: null,
    init() {
        const media = matchMedia('(max-width: 1023px)');
        this.compact = media.matches;
        media.addEventListener('change', (event) => {
            this.compact = event.matches;
            if (!this.compact) this.sidebarOpen = false;
        });
    },
    toggleSidebar(event) {
        if (this.sidebarOpen) return this.closeSidebar();
        this.opener = event?.currentTarget;
        this.sidebarOpen = true;
        this.$nextTick(() => document.querySelector('#admin-sidebar a')?.focus());
    },
    closeSidebar() {
        if (!this.sidebarOpen) return;
        this.sidebarOpen = false;
        this.opener?.focus();
    },
    trapNavigation(event) {
        if (!this.sidebarOpen) return;
        const items = [...document.querySelectorAll('#admin-sidebar a, #admin-sidebar button')];
        if (!items.length) return;
        const first = items[0], last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    },
}));

Alpine.data('adminModal', () => ({
    open() { this.$refs.dialog.showModal(); },
    close() { this.$refs.dialog.close(); },
}));

Alpine.start();
