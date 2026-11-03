<x-admin.layout :title="ucfirst($section).' settings'">
    <div class="page-intro"><h2>{{ match($section) { 'site' => 'Site identity and contact details', 'social' => 'Social profiles', default => 'Analytics and verification' } }}</h2>
        <p class="helper">{{ match($section) { 'site' => 'Use the existing website details. Image selections come from the image library.', 'social' => 'Enter complete HTTPS profile URLs. Leave unused profiles blank.', default => 'Save the existing tracking IDs and verification tokens. Tracking remains inactive until the public site is integrated.' } }}</p>
    </div>
    <x-admin.glass-card strong>
        <form method="POST" action="{{ route('admin.website.update', $section) }}" class="form-stack">@csrf @method('PUT')
            <div class="form-grid">
                @foreach($fields as $name => $field)
                    @if($field['type'] === 'textarea')
                        <x-admin.textarea :name="$name" :label="$field['label']" :maxlength="$field['max']" :value="old($name, $settings[$name] ?? '')" />
                    @elseif($field['type'] === 'media')
                        <x-admin.select :name="$name" :label="$field['label']" :value="old($name, $settings[$name] ?? '')" :options="['' => 'No image selected'] + $images" :help="$name === 'favicon_media_id' ? 'Choose a square image, at least 48 × 48 pixels.' : 'Choose a ready image with descriptive alt text.'" />
                    @elseif($field['type'] === 'boolean')
                        <x-admin.select :name="$name" :label="$field['label']" :value="old($name, (int) ($settings[$name] ?? false))" :options="[0 => 'Disabled', 1 => 'Enabled when public site is integrated']" />
                    @else
                        <x-admin.input :name="$name" :label="$field['label']" :type="$field['type']" :value="old($name, $settings[$name] ?? '')" :maxlength="$field['max']" :required="$name === 'site_name'" />
                    @endif
                @endforeach
            </div>
            @if($section === 'analytics')<p class="helper">Use IDs and tokens only. If GA4 is configured through Tag Manager, avoid installing it a second time. Script snippets are not accepted here.</p>@endif
            @if($section === 'site')<p class="helper">WhatsApp numbers include the country code, for example + followed by digits. Footer and address fields accept plain text.</p>@endif
            <div><x-admin.button type="submit">Save {{ $section }} settings</x-admin.button></div>
        </form>
    </x-admin.glass-card>
</x-admin.layout>
