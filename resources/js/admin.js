import Alpine from 'alpinejs';
import registerBlogEditor from './admin-blog-editor';

registerBlogEditor(Alpine);

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
    blocks: [], images: {}, sequence: 0, message: '', uploadingKey: null, uploadMessage: '',
    init() {
        try {
            this.blocks = JSON.parse(this.$el.dataset.blocks).map(block => ({ text: '', media_id: '', label: '', url: '', ...block, key: ++this.sequence }));
            this.images = JSON.parse(this.$el.dataset.images);
        }
        catch { this.blocks = []; this.message = 'Sections could not be loaded. Reload before saving.'; }
    },
    serialize() { return JSON.stringify(this.blocks.map(({ key, ...block }) => block)); },
    async uploadImage(event, block) {
        const form = event.currentTarget;
        if (this.uploadingKey !== null || !form.reportValidity()) return;
        this.uploadingKey = block.key;
        this.uploadMessage = 'Uploading image and preparing responsive versions...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': form.elements._token.value,
                },
                credentials: 'same-origin',
            });
            const result = await response.json();

            if (!response.ok) {
                const errors = result.errors ? Object.values(result.errors).flat().join(' ') : '';
                throw new Error(errors || 'The image upload was rejected. Check the file and try again.');
            }

            const statusUrl = new URL(result.status_url, window.location.origin);
            if (statusUrl.origin !== window.location.origin || !result.media?.id || !result.media?.label) {
                throw new Error('The upload completed, but its processing status could not be checked. Reload the page to select it once ready.');
            }

            let status = result.status;
            for (let attempt = 0; status !== 'ready' && status !== 'failed' && attempt < 30; attempt++) {
                await new Promise(resolve => window.setTimeout(resolve, 4000));
                const statusResponse = await fetch(statusUrl, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                if (!statusResponse.ok) throw new Error('Image processing status could not be checked. Reload the page to check the library.');
                status = (await statusResponse.json()).status;
            }

            if (status !== 'ready') {
                throw new Error(status === 'failed'
                    ? 'Image processing failed. Try another image or upload it again from the media library.'
                    : 'Image processing is taking longer than expected. Once ready, reload this draft to select it.');
            }

            this.images[result.media.id] = result.media.label;
            block.media_id = String(result.media.id);
            form.reset();
            this.uploadMessage = 'Image is ready and selected for this section.';
        } catch (error) {
            console.error('Could not upload and select the section image.', error);
            this.uploadMessage = error instanceof Error ? error.message : 'The image could not be uploaded. Try again.';
        } finally {
            this.uploadingKey = null;
        }
    },
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

Alpine.data('portfolioLayout', () => ({
    layout: { sections: [] }, counter: 0, newType: 'report',
    colors: [{key:'background',label:'Page background'},{key:'gradient_start',label:'Banner gradient start'},{key:'gradient_end',label:'Banner gradient end'},{key:'heading_color',label:'Heading colour'},{key:'body_color',label:'Body text colour'}],
    init() { this.layout = JSON.parse(this.$el.dataset.layout); this.layout.design ??= 'modern'; this.layout.sections.forEach(s => { s._key = ++this.counter; }); },
    serialize() { return JSON.stringify({...this.layout, sections: this.layout.sections.map(({_key, ...section}) => section)}); },
    move(index, direction) { const next = index + direction; if (next < 0 || next >= this.layout.sections.length) return; const [s] = this.layout.sections.splice(index,1); this.layout.sections.splice(next,0,s); },
    moveCard(section,index,direction) { const next=index+direction; if(next<0||next>=section.cards.length)return; const [card]=section.cards.splice(index,1);section.cards.splice(next,0,card); },
    add() { this.layout.sections.push({_key: ++this.counter,type:this.newType,title:'New section',eyebrow:'',body:'',image_id:'',image_position:'right',button_label:'',button_url:'',badge_value:'',badge_label:'',visible:true,padding_top:80,padding_bottom:80,cards:[]}); }
}));

Alpine.start();
