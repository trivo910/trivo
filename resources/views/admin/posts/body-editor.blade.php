@php($editorBody = app(\App\Services\Content\BlogBody::class)->clean((string) old('body', $post->body ?? '')))
<div class="form-stack blog-editor" x-data="blogEditor" data-images="{{ json_encode($inlineImages) }}"
     data-library-url="{{ route('admin.posts.media-options') }}" data-upload-url="{{ route('admin.media.store') }}">
    <h2>Article content</h2>
    <p class="helper">Format your article and insert images from the media library. Select an image in the editor to replace, move or remove it. Saving a published article updates the website immediately.</p>
    <div class="blog-editor__toolbar" role="group" aria-label="Article formatting">
        <button type="button" class="admin-button button-secondary" @mousedown.prevent @click="format('bold')" :disabled="sourceMode">Bold</button>
        <button type="button" class="admin-button button-secondary" @mousedown.prevent @click="format('italic')" :disabled="sourceMode">Italic</button>
        <button type="button" class="admin-button button-secondary" @mousedown.prevent @click="format('formatBlock', 'p')" :disabled="sourceMode">Paragraph</button>
        <button type="button" class="admin-button button-secondary" @mousedown.prevent @click="format('formatBlock', 'h2')" :disabled="sourceMode">Heading 2</button>
        <button type="button" class="admin-button button-secondary" @mousedown.prevent @click="format('formatBlock', 'h3')" :disabled="sourceMode">Heading 3</button>
        <button type="button" class="admin-button button-secondary" @mousedown.prevent @click="format('insertUnorderedList')" :disabled="sourceMode">Bullet list</button>
        <button type="button" class="admin-button button-secondary" @mousedown.prevent @click="format('insertOrderedList')" :disabled="sourceMode">Numbered list</button>
        <button type="button" class="admin-button button-secondary" @mousedown.prevent @click="format('unlink')" :disabled="sourceMode">Remove link</button>
        <button type="button" class="admin-button button-secondary" @click="toggleSource()" :aria-pressed="sourceMode" x-text="sourceMode ? 'Visual editor' : 'Edit HTML'"></button>
    </div>
    <div class="form-grid">
        <div class="field"><label for="blog-link">Link URL</label><input class="admin-input" id="blog-link" x-model="link" maxlength="1000" placeholder="https://example.com or /contact"></div>
        <div><button type="button" class="admin-button button-secondary" @click="insertLink()" :disabled="sourceMode || !link">Apply link to selected text</button></div>
    </div>
    <div class="blog-editor__visual" x-ref="visual" x-show="!sourceMode" contenteditable="true" role="textbox" aria-label="Post body" aria-multiline="true"
         @input="sync()" @mouseup="remember()" @keyup="remember()" @click="selectImage($event)" @paste.prevent="paste($event)" @drop.prevent></div>
    <div class="field" x-show="sourceMode" x-cloak><label for="blog-body-source">Article HTML source</label><textarea class="admin-input" id="blog-body-source" name="body" x-ref="source" x-model="body" rows="18" maxlength="100000">{{ $editorBody }}</textarea></div>
    @error('body')<p class="field-error">{{ $message }}</p>@enderror
    <p class="helper" role="status" x-text="message"></p>
    <fieldset class="form-stack">
        <legend>Content images</legend>
        <div class="form-grid">
            <div class="field"><label for="blog-inline-image">Library image</label><select class="admin-input" id="blog-inline-image" x-model="imageId" @change="chooseImage()"><option value="">Choose an image</option><template x-for="image in images" :key="image.id"><option :value="image.id" x-text="image.label"></option></template></select></div>
            <div class="field"><label for="blog-image-alt">Image alt text</label><input class="admin-input" id="blog-image-alt" x-model="imageAlt" maxlength="1000"></div>
            <div class="field"><label for="blog-image-caption">Image caption</label><input class="admin-input" id="blog-image-caption" x-model="imageCaption" maxlength="2000"></div>
        </div>
        <div class="blog-editor__toolbar">
            <button type="button" class="admin-button button-secondary" @click="insertImage()" :disabled="(!imageId && !selectedImage) || sourceMode" x-text="selectedImage ? 'Update selected image' : 'Insert image'"></button>
            <button type="button" class="admin-button button-secondary" @click="moveImage(-1)" :disabled="!selectedImage || sourceMode">Move image earlier</button>
            <button type="button" class="admin-button button-secondary" @click="moveImage(1)" :disabled="!selectedImage || sourceMode">Move image later</button>
            <button type="button" class="admin-button button-secondary" @click="removeImage()" :disabled="!selectedImage || sourceMode">Remove selected image</button>
            <button type="button" class="admin-button button-secondary" @click="refreshImages()" :disabled="busy">Refresh images</button>
            <a href="{{ route('admin.media.index') }}" class="admin-button button-secondary" target="_blank" rel="noopener">Open media library</a>
        </div>
        @can('create', \App\Models\Media::class)
            <div class="form-grid">
                <div class="field"><label for="blog-upload">Upload a new image</label><input class="admin-input" id="blog-upload" type="file" accept="image/jpeg,image/png,image/webp,image/avif" x-ref="upload"></div>
                <div class="field"><label for="blog-upload-alt">Uploaded image alt text</label><input class="admin-input" id="blog-upload-alt" x-model="uploadAlt" maxlength="1000"></div>
            </div>
            <div><button type="button" class="admin-button button-secondary" @click="uploadImage()" :disabled="busy">Upload image</button></div>
            <p class="helper">Images may need background processing. Uploading does not save the article; use Save when finished.</p>
        @endcan
    </fieldset>
    <noscript><p>JavaScript is needed for the visual editor. You can edit the HTML below; images can be managed in the media library.</p><style>.blog-editor [name=body]{display:block!important}.blog-editor .field:has([name=body]){display:block!important}</style></noscript>
</div>
