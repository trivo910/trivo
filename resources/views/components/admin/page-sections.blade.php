@props(['blocks', 'images', 'title' => 'Page sections'])
<div x-data="pageSections" data-blocks="{{ json_encode(array_values($blocks)) }}" class="form-stack">
    <h2>{{ $title }}</h2>
    <p class="helper">Add up to 50 sections. Section headings will render below the page title. Use the arrow buttons to change their order.</p>
    <input type="hidden" name="blocks_json" value="{{ json_encode(array_values($blocks)) }}" :value="serialize()">
    <noscript><p class="helper">Enable JavaScript to edit sections. Saving without JavaScript retains the existing sections.</p></noscript>
    <template x-for="(block, index) in blocks" :key="block.key">
        <fieldset class="page-section form-stack">
            <legend x-text="'Section ' + (index + 1)"></legend>
            <div class="field"><label :for="'section-type-' + block.key">Section type</label>
                <select class="admin-input" :id="'section-type-' + block.key" x-model="block.type"><option value="heading">Heading</option><option value="paragraph">Paragraph</option><option value="image">Image</option><option value="link">Link / button</option></select>
            </div>
            <div class="field" x-show="['heading', 'paragraph'].includes(block.type)"><label :for="'section-text-' + block.key">Section text</label><textarea class="admin-input" :id="'section-text-' + block.key" x-model="block.text" :maxlength="block.type === 'heading' ? 250 : 8000" rows="4"></textarea></div>
            <div class="field" x-show="block.type === 'image'"><label :for="'section-image-' + block.key">Section image</label><select class="admin-input" :id="'section-image-' + block.key" x-model="block.media_id"><option value="">Choose a ready image</option>@foreach($images as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach</select></div>
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
