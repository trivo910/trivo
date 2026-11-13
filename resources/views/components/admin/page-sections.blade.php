@props(['blocks', 'images', 'title' => 'Page sections'])
@php
    $initialBlocks = old('blocks');
    if (!is_array($initialBlocks)) {
        $previousBlocks = json_decode((string) old('blocks_json', ''), true);
        $initialBlocks = is_array($previousBlocks) && array_is_list($previousBlocks) ? $previousBlocks : $blocks;
    }
    $initialBlocks = array_values($initialBlocks);
@endphp
<div x-data="pageSections" data-blocks="{{ json_encode($initialBlocks) }}" data-images="{{ json_encode($images) }}" class="form-stack">
    <h2>{{ $title }}</h2>
    <p class="helper">Add up to 50 sections. Section headings will render below the page title. Use the arrow buttons to change their order.</p>
    <input type="hidden" name="blocks_json" value="{{ old('blocks_json', json_encode($initialBlocks)) }}" :value="serialize()">
    <noscript><p class="helper">Enable JavaScript to edit sections. Saving without JavaScript retains the existing sections.</p></noscript>
    <template x-for="(block, index) in blocks" :key="block.key">
        <fieldset class="page-section form-stack">
            <legend x-text="'Section ' + (index + 1)"></legend>
            <div class="field"><label :for="'section-type-' + block.key">Section type</label>
                <select class="admin-input" :id="'section-type-' + block.key" x-model="block.type"><option value="heading">Heading</option><option value="paragraph">Paragraph</option><option value="image">Image</option><option value="link">Link / button</option><option value="legacy_html" disabled>Imported HTML (remove to replace)</option></select>
            </div>
            <p class="helper" x-show="block.type === 'legacy_html'">This imported section is preserved as sanitized HTML. Remove it to replace it with editable sections.</p>
            <div class="field" x-show="['heading', 'paragraph'].includes(block.type)"><label :for="'section-text-' + block.key">Section text</label><textarea class="admin-input" :id="'section-text-' + block.key" x-model="block.text" :maxlength="block.type === 'heading' ? 250 : 8000" rows="4"></textarea></div>
            <div class="field" x-show="block.type === 'image'">
                <label :for="'section-image-' + block.key">Section image</label>
                <select class="admin-input" :id="'section-image-' + block.key" x-model="block.media_id">
                    <option value="">Choose a ready image</option>
                    <template x-for="(label, id) in images" :key="id"><option :value="id" x-text="label"></option></template>
                </select>
                @can('create', App\Models\Media::class)
                    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="section-image-upload" @submit.prevent="uploadImage($event, block)">
                        @csrf
                        <label :for="'section-upload-file-' + block.key">Or upload an image</label>
                        <input class="admin-input" :id="'section-upload-file-' + block.key" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif" required>
                        <label :for="'section-upload-alt-' + block.key">Image alt text</label>
                        <input class="admin-input" :id="'section-upload-alt-' + block.key" type="text" name="alt" maxlength="1000" required>
                        <label :for="'section-upload-title-' + block.key">Image title (optional)</label>
                        <input class="admin-input" :id="'section-upload-title-' + block.key" type="text" name="title" maxlength="255">
                        <input type="hidden" name="folder" value="Content sections">
                        <button class="admin-button button-secondary" type="submit" x-bind:disabled="uploadingKey !== null" x-text="uploadingKey === block.key ? 'Uploading image...' : 'Upload and select image'"></button>
                        <p class="helper" role="status" aria-live="polite" x-show="uploadMessage" x-text="uploadMessage"></p>
                    </form>
                @endcan
            </div>
            <div class="form-grid" x-show="block.type === 'link'">
                <div class="field"><label :for="'section-label-' + block.key">Link label</label><input class="admin-input" :id="'section-label-' + block.key" x-model="block.label" maxlength="150"></div>
                <div class="field"><label :for="'section-url-' + block.key">Link URL</label><input class="admin-input" :id="'section-url-' + block.key" x-model="block.url" maxlength="1000" placeholder="/existing-path or https://..."></div>
            </div>
            <div class="section-actions">
                <x-admin.button type="button" variant="secondary" @click="move(index, -1)" x-bind:disabled="index === 0" x-bind:aria-label="'Move section ' + (index + 1) + ' up'">Move up</x-admin.button>
                <x-admin.button type="button" variant="secondary" @click="move(index, 1)" x-bind:disabled="index === blocks.length - 1" x-bind:aria-label="'Move section ' + (index + 1) + ' down'">Move down</x-admin.button>
                <x-admin.button type="button" variant="secondary" @click="remove(index)" x-bind:aria-label="'Remove section ' + (index + 1)">Remove</x-admin.button>
            </div>
        </fieldset>
    </template>
    <div x-cloak><x-admin.button type="button" variant="secondary" @click="add" x-bind:disabled="blocks.length >= 50">Add section</x-admin.button></div>
    <p class="helper" role="status" aria-live="polite" x-text="message"></p>
</div>
