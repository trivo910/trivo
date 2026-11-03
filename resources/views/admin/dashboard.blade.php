<x-admin.layout title="Dashboard">
    <div class="page-intro"><p class="eyebrow">Overview</p><h2>Welcome back, {{ auth()->user()->name }}</h2><p class="helper">Content and enquiry totals from your website.</p></div>
    <div class="stats-grid">
        @if($stats['posts'] !== null)<x-admin.stat-card label="Blog posts" :value="$stats['posts']" />@endif
        @if($stats['pages'] !== null)<x-admin.stat-card label="Pages" :value="$stats['pages']" />@endif
        @if($stats['leads'] !== null)<x-admin.stat-card label="Enquiries" :value="$stats['leads']" />@endif
        @if($stats['not_found'] !== null)<x-admin.stat-card label="Recorded 404 hits" :value="$stats['not_found']" />@endif
    </div>
    @can('posts.view')
        <x-admin.glass-card strong class="seo-overview"><div><p class="eyebrow">Content health</p><h2>Average SEO score</h2><p class="helper">Based on published posts. A score appears after content has been scored and published.</p></div><x-admin.score-ring :score="$stats['seo']" /></x-admin.glass-card>
    @endcan
</x-admin.layout>
