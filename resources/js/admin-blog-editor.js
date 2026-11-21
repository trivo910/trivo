export default function registerBlogEditor(Alpine) {
    Alpine.data('blogEditor', () => ({
        body: '', images: [], originalImages: [], sourceMode: false, busy: false, message: '',
        link: '', imageId: '', imageAlt: '', imageCaption: '', uploadAlt: '', selectedImage: null,
        range: null, form: null, submitHandler: null, libraryUrl: '', uploadUrl: '',
        init() {
            this.images = JSON.parse(this.$el.dataset.images);
            this.libraryUrl = this.$el.dataset.libraryUrl;
            this.uploadUrl = this.$el.dataset.uploadUrl;
            this.body = this.$refs.source.value;
            this.$refs.visual.innerHTML = this.body;
            this.originalImages = [...this.$refs.visual.querySelectorAll('img')].map(image => image.getAttribute('src'));
            this.form = this.$el.closest('form');
            this.submitHandler = () => {
                if (!this.sourceMode) this.sync();
                this.$refs.source.value = this.body;
            };
            this.form.addEventListener('submit', this.submitHandler);
        },
        destroy() { this.form?.removeEventListener('submit', this.submitHandler); },
        remember() {
            const selection = window.getSelection();
            if (selection?.rangeCount && this.$refs.visual.contains(selection.anchorNode)) {
                this.range = selection.getRangeAt(0).cloneRange();
            }
        },
        restoreRange() {
            this.$refs.visual.focus();
            const selection = window.getSelection();
            const range = this.range && this.$refs.visual.contains(this.range.commonAncestorContainer)
                ? this.range : document.createRange();
            if (range !== this.range) { range.selectNodeContents(this.$refs.visual); range.collapse(false); }
            selection.removeAllRanges(); selection.addRange(range);
            return range;
        },
        sync() {
            const copy = this.$refs.visual.cloneNode(true);
            copy.querySelectorAll('[data-blog-selected]').forEach(image => image.removeAttribute('data-blog-selected'));
            this.body = copy.innerHTML;
            this.$refs.source.value = this.body;
        },
        safeUrl(value) {
            if (!value || /[\x00-\x20\x7f\\<>"']/.test(value) || value.startsWith('//')) return false;
            try {
                const url = new URL(value, location.origin);
                return ['http:', 'https:', 'mailto:', 'tel:'].includes(url.protocol) && !url.username && !url.password;
            } catch { return false; }
        },
        cleanSource(html) {
            const template = document.createElement('template'); template.innerHTML = html;
            const allowed = new Set('a article b blockquote br caption code dd del div dl dt em figcaption figure h1 h2 h3 h4 h5 h6 hr i img li ol p pre section small span strong sub summary sup table tbody td th thead tr u ul'.split(' '));
            const drop = new Set(['script', 'style', 'iframe', 'object', 'svg', 'form', 'template', 'video', 'audio']);
            const attributes = new Set(['alt', 'title', 'href', 'src', 'width', 'height', 'colspan', 'rowspan', 'target']);
            for (const node of [...template.content.querySelectorAll('*')]) {
                const tag = node.tagName.toLowerCase();
                if (drop.has(tag)) { node.remove(); continue; }
                if (!allowed.has(tag)) { node.replaceWith(...node.childNodes); continue; }
                for (const attribute of [...node.attributes]) {
                    if (!attributes.has(attribute.name)
                        || (['href', 'src'].includes(attribute.name) && !this.safeUrl(attribute.value))
                        || (['width', 'height', 'colspan', 'rowspan'].includes(attribute.name) && !/^\d+$/.test(attribute.value))) {
                        node.removeAttribute(attribute.name);
                    }
                }
                if (tag === 'img') {
                    const src = node.getAttribute('src');
                    const known = this.originalImages.includes(src) || this.images.some(image => {
                        try { return new URL(image.url, location.origin).href === new URL(src, location.origin).href; }
                        catch { return false; }
                    });
                    if (!known) node.remove();
                }
                if (node.getAttribute('target') === '_blank') node.setAttribute('rel', 'noopener noreferrer');
            }
            return template.innerHTML;
        },
        toggleSource() {
            if (!this.sourceMode) this.sync();
            else {
                this.body = this.cleanSource(this.body);
                this.$refs.visual.innerHTML = this.body;
                this.range = null; this.selectedImage = null;
                this.$refs.source.value = this.body;
            }
            this.sourceMode = !this.sourceMode;
        },
        format(command, value = null) {
            if (this.sourceMode) return;
            this.restoreRange(); document.execCommand(command, false, value); this.remember(); this.sync();
        },
        paste(event) {
            this.restoreRange();
            document.execCommand('insertText', false, event.clipboardData.getData('text/plain'));
            this.remember(); this.sync();
        },
        insertLink() {
            if (!this.safeUrl(this.link)) { this.message = 'Enter a valid website, internal, email or telephone link.'; return; }
            this.restoreRange();
            if (window.getSelection().isCollapsed) {
                const link = document.createElement('a'); link.href = this.link; link.textContent = this.link;
                this.insertNode(link);
            } else this.format('createLink', this.link);
            this.sync(); this.message = '';
        },
        insertNode(node) {
            const range = this.restoreRange(); range.deleteContents(); range.insertNode(node);
            range.setStartAfter(node); range.collapse(true);
            const selection = window.getSelection(); selection.removeAllRanges(); selection.addRange(range);
            this.remember();
        },
        chooseImage() {
            const image = this.images.find(image => String(image.id) === String(this.imageId));
            if (image) { this.imageAlt = image.alt; this.imageCaption = image.caption || ''; }
        },
        selectImage(event) {
            this.$refs.visual.querySelectorAll('[data-blog-selected]').forEach(image => image.removeAttribute('data-blog-selected'));
            this.selectedImage = event.target instanceof HTMLImageElement ? event.target : null;
            if (this.selectedImage) {
                this.selectedImage.setAttribute('data-blog-selected', 'true');
                this.imageAlt = this.selectedImage.alt;
                this.imageCaption = this.selectedImage.closest('figure')?.querySelector('figcaption')?.textContent || '';
                const found = this.images.find(image => new URL(image.url, location.origin).href === this.selectedImage.src);
                this.imageId = found ? String(found.id) : '';
            } else this.remember();
        },
        insertImage() {
            const item = this.images.find(image => String(image.id) === String(this.imageId));
            if ((!item && !this.selectedImage) || this.sourceMode) return;
            const image = this.selectedImage || document.createElement('img');
            if (item) {
                image.src = item.url;
                if (item.width > 0) image.width = item.width;
                if (item.height > 0) image.height = item.height;
            }
            image.alt = this.imageAlt;
            let figure = image.closest('figure');
            if (!figure) {
                figure = document.createElement('figure');
                if (this.selectedImage) image.replaceWith(figure);
                figure.append(image);
                if (!this.selectedImage) this.insertNode(figure);
            }
            figure.querySelector('figcaption')?.remove();
            if (this.imageCaption.trim()) {
                const caption = document.createElement('figcaption'); caption.textContent = this.imageCaption; figure.append(caption);
            }
            this.selectedImage = image; image.setAttribute('data-blog-selected', 'true'); this.sync(); this.message = '';
        },
        imageBlock() {
            return this.selectedImage?.closest('figure') || this.selectedImage?.closest('p') || this.selectedImage;
        },
        moveImage(direction) {
            const block = this.imageBlock(); if (!block) return;
            const sibling = direction < 0 ? block.previousElementSibling : block.nextElementSibling;
            if (!sibling) return;
            if (direction < 0) sibling.before(block); else sibling.after(block);
            this.range = null; this.sync();
        },
        removeImage() {
            if (!this.selectedImage) return;
            const figure = this.selectedImage.closest('figure');
            if (figure) figure.remove(); else this.selectedImage.remove();
            this.selectedImage = null; this.range = null; this.sync();
        },
        async refreshImages() {
            try {
                const response = await fetch(this.libraryUrl, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Image list unavailable');
                this.images = (await response.json()).images;
                const featured = this.form.elements.featured_media_id;
                if (featured) {
                    const selected = featured.value;
                    featured.replaceChildren(new Option('No featured image', ''), ...this.images.map(image => new Option(image.label, image.id)));
                    featured.value = selected;
                }
                return true;
            } catch { this.message = 'Could not refresh images. Your article edits remain in this page.'; return false; }
        },
        async uploadImage() {
            if (this.busy) return;
            const file = this.$refs.upload.files[0];
            if (!file || !this.uploadAlt.trim()) { this.message = 'Choose an image and provide its alt text.'; return; }
            this.busy = true; this.message = 'Uploading image…';
            try {
                const data = new FormData(); data.append('image', file); data.append('alt', this.uploadAlt);
                const response = await fetch(this.uploadUrl, { method: 'POST', body: data,
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
                const result = await response.json();
                if (!response.ok) { this.message = result.errors ? Object.values(result.errors).flat().join(' ') : 'Image upload was rejected.'; return; }
                let status = result.status;
                for (let attempt = 0; status !== 'ready' && status !== 'failed' && attempt < 15; attempt++) {
                    this.message = 'Preparing image. Your article edits are still here.';
                    await new Promise(resolve => setTimeout(resolve, 2000));
                    const progress = await fetch(result.status_url, { headers: { Accept: 'application/json' } });
                    if (!progress.ok) break;
                    status = (await progress.json()).status;
                }
                if (!await this.refreshImages()) return;
                if (status === 'ready') {
                    this.imageId = String(result.media.id); this.chooseImage();
                    this.message = 'Image ready. Use Insert image, or select it as the featured image.';
                    this.$refs.upload.value = '';
                } else this.message = status === 'failed' ? 'Image processing failed. Open the media library to review or retry it.'
                    : 'Image uploaded and awaiting background processing. Refresh images when it is ready.';
            } catch { this.message = 'Upload did not finish. Your article edits are retained in this page.'; }
            finally { this.busy = false; }
        },
    }));
}
