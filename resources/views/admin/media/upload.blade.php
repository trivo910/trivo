<x-admin.layout title="Upload image">
    <x-admin.glass-card strong>
        <h2>Add a website image</h2><p class="helper">JPEG, PNG, WebP or AVIF. Maximum 8 MiB, 6,000 pixels per side and 8 megapixels. Responsive versions are prepared in the background.</p>
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.media.store') }}" class="form-stack" x-data="mediaUpload" @submit.prevent="submit">
            @csrf
            <div class="media-dropzone" :class="{ 'is-dragging': dragging }" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="drop">
                <x-admin.input name="image" label="Image file" type="file" accept=".jpg,.jpeg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif" required x-ref="file" />
                <p class="helper">Choose a file or drop one here.</p>
            </div>
            <x-admin.input name="alt" label="Alt text" :value="old('alt')" required maxlength="1000" help="Describe the image for someone who cannot see it." />
            <x-admin.input name="title" label="Image title (optional)" :value="old('title')" maxlength="255" />
            <x-admin.textarea name="caption" label="Caption (optional)" :value="old('caption')" rows="3" maxlength="2000" />
            <x-admin.input name="folder" label="Folder (optional)" :value="old('folder')" maxlength="80" help="A library label, such as Team or Blog. It does not change the file URL." />
            <div x-cloak x-show="busy"><progress max="100" :value="progress" aria-label="Image upload progress"></progress><p class="helper" x-text="progress + '% uploaded'"></p></div>
            <p x-cloak x-show="message" class="field-error" role="alert" x-text="message"></p>
            <div class="component-row"><x-admin.button type="submit" x-bind:disabled="busy">Upload image</x-admin.button><x-admin.button variant="secondary" :href="route('admin.media.index')">Cancel</x-admin.button></div>
        </form>
    </x-admin.glass-card>
</x-admin.layout>
