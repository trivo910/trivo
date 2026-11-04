@props(['model', 'seo', 'preview', 'revisions', 'images', 'label' => 'page', 'routePrefix', 'schemaOptions' => ['WebPage' => 'Web page', 'AboutPage' => 'About page', 'ContactPage' => 'Contact page', 'FAQPage' => 'FAQ page'], 'schemaHelp' => 'Use FAQ only when matching questions and answers are visible on the page. Schema generation is pending.'])
<x-admin.layout :title="ucfirst($label).' SEO'">
    <div class="page-intro"><h2>{{ $model->title }}</h2><p class="helper">Edit search and social metadata while retaining the content and original path. Public SEO rendering is still in development.</p></div>
    <div><x-admin.button :href="route($routePrefix.'.edit', $model)" variant="secondary">Back to {{ $label }}</x-admin.button></div>
    <x-admin.glass-card strong>
        <form method="POST" action="{{ route($routePrefix.'.seo.update', $model) }}" class="form-stack" x-data="pageSeoPreview" data-preview="{{ json_encode($preview) }}">@csrf @method('PUT')
            <input type="hidden" name="content_version" value="{{ old('content_version', $model->content_version) }}">
            <div class="form-grid">
                <x-admin.input name="seo_title" label="SEO title" :value="old('seo_title', $seo['seo_title'] ?? '')" maxlength="255" help="Aim for 30–60 characters. Existing source titles may be longer." x-on:input="title = $event.target.value" />
                <x-admin.input name="canonical_url" label="Canonical URL" :value="old('canonical_url', $seo['canonical_url'] ?? '')" maxlength="1000" type="url" help="Optional HTTPS URL on the configured website host. Blank uses the page path when public rendering is integrated." x-on:input="url = $event.target.value" />
                <x-admin.input name="seo_description" label="Meta description" :value="old('seo_description', $seo['seo_description'] ?? '')" maxlength="500" help="Aim for 120–160 characters. Preserve verified source copy." x-on:input="description = $event.target.value" />
                <x-admin.input name="robots" label="Robots directives" :value="old('robots', $seo['robots'] ?? '')" maxlength="500" help="Comma-separated directives, such as index,follow,max-image-preview:large. Blank leaves this override unset." />
                <x-admin.select name="sitemap_include" label="Include in sitemap" :value="old('sitemap_include', (int) ($seo['sitemap_include'] ?? true))" :options="[1 => 'Include when published and indexable', 0 => 'Exclude']" />
                <x-admin.input name="sitemap_priority" label="Sitemap priority" :value="old('sitemap_priority', $seo['sitemap_priority'] ?? '')" type="number" min="0" max="1" step="0.1" help="Optional value between 0 and 1." />
                <x-admin.select name="schema_type" :label="ucfirst($label).' schema type'" :value="old('schema_type', $seo['schema_type'] ?? '')" :options="['' => 'No override'] + $schemaOptions" :help="$schemaHelp" />
            </div>
            <h2>Search preview</h2>
            <p class="helper">An approximate preview; search engines may choose different text. Fallback text is displayed only and is never silently saved.</p>
            <div class="field"><label for="preview-device">Preview width</label><select id="preview-device" class="admin-input" x-model="device"><option value="desktop">Desktop</option><option value="mobile">Mobile</option></select></div>
            <div class="seo-preview" :class="{ 'seo-preview-mobile': device === 'mobile' }" aria-label="Search result preview">
                <p class="seo-preview-url" x-text="url || defaults.fallback_url">{{ $preview['url'] }}</p>
                <p class="seo-preview-title" x-text="title || defaults.fallback_title">{{ $preview['title'] }}</p>
                <p x-text="description || ''">{{ $preview['description'] }}</p>
            </div>
            <p class="helper" x-cloak x-text="'SEO title: ' + Array.from(title).length + ' characters. Meta description: ' + Array.from(description).length + ' characters.'"></p>
            <h2>Social metadata</h2>
            <div class="form-grid">
                <x-admin.input name="og_title" label="Open Graph title" :value="old('og_title', $seo['og_title'] ?? '')" maxlength="255" />
                <x-admin.input name="og_description" label="Open Graph description" :value="old('og_description', $seo['og_description'] ?? '')" maxlength="500" />
                <x-admin.select name="og_image_id" label="Open Graph image" :value="old('og_image_id', $seo['og_image_id'] ?? '')" :options="['' => 'No image override'] + $images" help="Choose a ready image with alt text; a 1200 × 630 image is recommended." />
                <x-admin.input name="twitter_title" label="Twitter / X title" :value="old('twitter_title', $seo['twitter_title'] ?? '')" maxlength="255" />
                <x-admin.input name="twitter_description" label="Twitter / X description" :value="old('twitter_description', $seo['twitter_description'] ?? '')" maxlength="500" />
                <x-admin.select name="twitter_image_id" label="Twitter / X image" :value="old('twitter_image_id', $seo['twitter_image_id'] ?? '')" :options="['' => 'No image override'] + $images" />
            </div>
            <div><x-admin.button type="submit">Save {{ $label }} SEO</x-admin.button></div>
        </form>
    </x-admin.glass-card>
    <x-admin.table caption="SEO revision history"><x-slot:head><tr><th scope="col">Version</th><th scope="col">Saved (UTC)</th><th scope="col">Action</th></tr></x-slot:head>
        @forelse($revisions as $revision)<tr><td>{{ $revision->snapshot['content_version'] }}</td><td>{{ $revision->created_at->format('Y-m-d H:i:s') }}</td><td>
            <form method="POST" action="{{ route($routePrefix.'.seo.restore', [$model, $revision]) }}">@csrf<input type="hidden" name="content_version" value="{{ $model->content_version }}"><x-admin.button type="submit" variant="secondary">Restore SEO revision #{{ $revision->id }}</x-admin.button></form>
        </td></tr>@empty<tr><td colspan="3">No SEO revisions yet. Saving creates a revision.</td></tr>@endforelse
    </x-admin.table>
    <p class="helper">Restoring SEO creates a new metadata revision. Content, path, publication state and custom schema JSON remain unchanged.</p>
    <div class="pagination">{{ $revisions->links() }}</div>
</x-admin.layout>
