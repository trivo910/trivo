<x-admin.layout title="Admin styleguide">
    <div class="page-intro"><p class="eyebrow">Design system</p><h2>Shared admin components</h2><p class="helper">Use the theme selector to review light and dark appearances.</p></div>
    <div class="styleguide-grid">
        <x-admin.glass-card strong><h2>Buttons</h2><div class="component-row"><x-admin.button>Primary</x-admin.button><x-admin.button variant="secondary">Secondary</x-admin.button><x-admin.button variant="danger">Danger</x-admin.button></div></x-admin.glass-card>
        <x-admin.glass-card strong><h2>Form controls</h2><x-admin.input name="sample-title" label="Example label" help="Helper text uses the accessible muted colour." placeholder="Example value" /></x-admin.glass-card>
        <x-admin.glass-card strong><h2>SEO scores</h2><div class="component-row"><x-admin.score-ring :score="35" /><x-admin.score-ring :score="65" /><x-admin.score-ring :score="92" /></div></x-admin.glass-card>
        <x-admin.glass-card strong><h2>Dialog</h2><p class="helper">Native dialog provides keyboard focus containment and Escape dismissal.</p><x-admin.modal title="Example dialog" label="Open example"><p>This dialog uses the shared opaque glass surface.</p></x-admin.modal></x-admin.glass-card>
    </div>
    <x-admin.table caption="Example status table"><x-slot:head><tr><th scope="col">Example item</th><th scope="col">Status</th></tr></x-slot:head><tr><td>Draft content</td><td><span class="badge">Draft</span></td></tr><tr><td>Published content</td><td><span class="badge badge-success">Published</span></td></tr></x-admin.table>
</x-admin.layout>
