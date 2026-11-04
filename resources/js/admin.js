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

Alpine.data('mediaUpload', () => ({
    busy: false, progress: 0, message: '', dragging: false,
    drop(event) {
        this.dragging = false;
        if (event.dataTransfer.files.length !== 1) { this.message = 'Choose one image at a time.'; return; }
        this.$refs.file.files = event.dataTransfer.files;
    },
    submit() {
        if (this.busy || !this.$el.reportValidity()) return;
        this.busy = true; this.message = ''; this.progress = 0;
        const request = new XMLHttpRequest();
        request.open('POST', this.$el.action);
        request.setRequestHeader('Accept', 'application/json');
        request.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
        request.timeout = 120000;
        request.upload.onprogress = (event) => { if (event.lengthComputable) this.progress = Math.round(event.loaded / event.total * 100); };
        request.onerror = request.ontimeout = () => { this.busy = false; this.message = 'Upload did not finish. Check your connection and try again.'; };
        request.onload = () => {
            this.busy = false;
            let result;
            try { result = JSON.parse(request.responseText); } catch { this.message = 'Your session may have expired. Reload this page and try again.'; return; }
            if (request.status >= 200 && request.status < 300 && result.redirect) {
                const target = new URL(result.redirect, location.origin);
                if (target.origin === location.origin) location.assign(target.href);
            } else this.message = result.errors ? Object.values(result.errors).flat().join(' ') : 'The upload was rejected. Check the file and try again.';
        };
        request.send(new FormData(this.$el));
    },
}));

Alpine.data('mediaProcessing', () => ({
    timer: null, attempts: 0,
    init() {
        if (!['queued', 'processing'].includes(this.$el.dataset.initialStatus)) return;
        this.timer = setInterval(() => this.check(), 4000);
    },
    async check() {
        if (++this.attempts > 30) { clearInterval(this.timer); return; }
        try {
            const response = await fetch(this.$el.dataset.statusUrl, { headers: { Accept: 'application/json' } });
            if (!response.ok) { clearInterval(this.timer); return; }
            const data = await response.json();
            if (['ready', 'failed'].includes(data.status)) { clearInterval(this.timer); location.reload(); }
        } catch { clearInterval(this.timer); }
    },
    destroy() { clearInterval(this.timer); },
}));

Alpine.data('pageSections', () => ({
    blocks: [], sequence: 0, message: '',
    init() {
        try { this.blocks = JSON.parse(this.$el.dataset.blocks).map(block => ({ text: '', media_id: '', label: '', url: '', ...block, key: ++this.sequence })); }
        catch { this.blocks = []; this.message = 'Sections could not be loaded. Reload before saving.'; }
    },
    serialize() { return JSON.stringify(this.blocks.map(({ key, ...block }) => block)); },
    add() {
        if (this.blocks.length >= 50) return;
        this.blocks.push({ type: 'paragraph', text: '', media_id: '', label: '', url: '', key: ++this.sequence });
        this.message = 'Section added.';
        this.$nextTick(() => document.getElementById('section-type-' + this.sequence)?.focus());
    },
    move(index, direction) {
        const next = index + direction;
        if (next < 0 || next >= this.blocks.length) return;
        [this.blocks[index], this.blocks[next]] = [this.blocks[next], this.blocks[index]];
        this.message = 'Section moved to position ' + (next + 1) + '.';
    },
    remove(index) { this.blocks.splice(index, 1); this.message = 'Section removed. Save the draft to keep this change.'; },
}));

Alpine.data('pageSeoPreview', () => ({
    defaults: { title: '', description: '', url: '' }, title: '', description: '', url: '', device: 'desktop',
    init() {
        this.defaults = JSON.parse(this.$el.dataset.preview);
        this.title = this.$el.elements.seo_title.value;
        this.description = this.$el.elements.seo_description.value;
        this.url = this.$el.elements.canonical_url.value;
    },
}));

Alpine.start();
