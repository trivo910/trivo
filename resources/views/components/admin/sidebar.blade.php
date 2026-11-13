<aside id="admin-sidebar" class="glass admin-sidebar" :class="{ 'is-open': sidebarOpen }" :inert="compact && !sidebarOpen" aria-label="Administration navigation">
    <button x-cloak x-show="compact" type="button" class="admin-button button-secondary" @click="closeSidebar">Close navigation</button>
    <a class="brand" href="{{ route('admin.dashboard') }}">trivo<span>Administration</span></a>
    <nav aria-label="Main navigation">
        <p class="nav-group">Overview</p>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif><x-admin.icon name="home" />Dashboard</a>
        @can('leads.view')
            <a href="{{ route('admin.leads.index') }}" class="nav-item {{ request()->routeIs('admin.leads.*') ? 'active' : '' }}" @if(request()->routeIs('admin.leads.*')) aria-current="page" @endif><x-admin.icon name="grid" />Leads</a>
        @endcan
        @can('pages.view')
            <p class="nav-group">Content</p>
            <a href="{{ route('admin.pages.index') }}" class="nav-item {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}" @if(request()->routeIs('admin.pages.*')) aria-current="page" @endif><x-admin.icon name="grid" />Pages</a>
        @endcan
        @can('services.view')
            <a href="{{ route('admin.services.index') }}" class="nav-item {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" @if(request()->routeIs('admin.services.*')) aria-current="page" @endif><x-admin.icon name="grid" />Services</a>
        @endcan
        @can('portfolio.view')
            <a href="{{ route('admin.portfolio.index') }}" class="nav-item {{ request()->routeIs('admin.portfolio.*') ? 'active' : '' }}" @if(request()->routeIs('admin.portfolio.*')) aria-current="page" @endif><x-admin.icon name="grid" />Portfolio</a>
        @endcan
        @can('faqs.view')
            <a href="{{ route('admin.faqs.index') }}" class="nav-item {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}" @if(request()->routeIs('admin.faqs.*')) aria-current="page" @endif><x-admin.icon name="grid" />FAQs</a>
        @endcan
        @can('testimonials.view')
            <a href="{{ route('admin.testimonials.index') }}" class="nav-item {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}" @if(request()->routeIs('admin.testimonials.*')) aria-current="page" @endif><x-admin.icon name="grid" />Testimonials</a>
        @endcan
        @can('clients.view')
            <a href="{{ route('admin.clients.index') }}" class="nav-item {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}" @if(request()->routeIs('admin.clients.*')) aria-current="page" @endif><x-admin.icon name="grid" />Clients</a>
        @endcan
        @can('posts.view')
            <a href="{{ route('admin.posts.index') }}" class="nav-item {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}" @if(request()->routeIs('admin.posts.*')) aria-current="page" @endif><x-admin.icon name="grid" />Blog posts</a>
            <a href="{{ route('admin.authors.index') }}" class="nav-item {{ request()->routeIs('admin.authors.*') ? 'active' : '' }}" @if(request()->routeIs('admin.authors.*')) aria-current="page" @endif><x-admin.icon name="grid" />Blog authors</a>
            @foreach(['categories' => 'Blog categories', 'tags' => 'Blog tags'] as $termKind => $label)
                <a href="{{ route('admin.blog.terms.index', $termKind) }}" class="nav-item {{ request()->routeIs('admin.blog.terms.*') && request()->route('kind') === $termKind ? 'active' : '' }}" @if(request()->routeIs('admin.blog.terms.*') && request()->route('kind') === $termKind) aria-current="page" @endif><x-admin.icon name="grid" />{{ $label }}</a>
            @endforeach
        @endcan
        @can('menus.view')
            <a href="{{ route('admin.menus.index') }}" class="nav-item {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}" @if(request()->routeIs('admin.menus.*')) aria-current="page" @endif><x-admin.icon name="grid" />Menus</a>
        @endcan
        @can('media.manage')
            <p class="nav-group">Media</p>
            <a href="{{ route('admin.media.index') }}" class="nav-item {{ request()->routeIs('admin.media.*') ? 'active' : '' }}" @if(request()->routeIs('admin.media.*')) aria-current="page" @endif><x-admin.icon name="grid" />Image library</a>
        @endcan
        @can('settings.site')
            <p class="nav-group">Website</p>
            @foreach(['site' => 'Site settings', 'social' => 'Social profiles'] as $section => $label)
                <a href="{{ route('admin.website.edit', $section) }}" class="nav-item {{ request()->routeIs('admin.website.*') && request()->route('section') === $section ? 'active' : '' }}" @if(request()->routeIs('admin.website.*') && request()->route('section') === $section) aria-current="page" @endif><x-admin.icon name="grid" />{{ $label }}</a>
            @endforeach
        @endcan
        @can('settings.seo')
            <a href="{{ route('admin.website.edit', 'analytics') }}" class="nav-item {{ request()->routeIs('admin.website.*') && request()->route('section') === 'analytics' ? 'active' : '' }}" @if(request()->routeIs('admin.website.*') && request()->route('section') === 'analytics') aria-current="page" @endif><x-admin.icon name="activity" />Analytics</a>
        @endcan
        @can('settings.mail')
            <p class="nav-group">Settings</p>
            <a href="{{ route('admin.mail.index') }}" class="nav-item {{ request()->routeIs('admin.mail.*') ? 'active' : '' }}" @if(request()->routeIs('admin.mail.*')) aria-current="page" @endif><x-admin.icon name="activity" />Mail / SMTP</a>
        @endcan
        @can('redirects.manage')
            <p class="nav-group">SEO</p>
            <a href="{{ route('admin.redirects.index') }}" class="nav-item {{ request()->routeIs('admin.redirects.*') ? 'active' : '' }}"><x-admin.icon name="activity" />Redirects</a>
            <a href="{{ route('admin.not-found') }}" class="nav-item {{ request()->routeIs('admin.not-found') ? 'active' : '' }}"><x-admin.icon name="activity" />404 log</a>
        @endcan
        @canany(['activity.view', 'view-styleguide'])<p class="nav-group">System</p>@endcanany
        @can('activity.view')
            <a href="{{ route('admin.activity') }}" class="nav-item {{ request()->routeIs('admin.activity') ? 'active' : '' }}" @if(request()->routeIs('admin.activity')) aria-current="page" @endif><x-admin.icon name="activity" />Activity log</a>
        @endcan
        @can('view-styleguide')
            <a href="{{ route('admin.styleguide') }}" class="nav-item {{ request()->routeIs('admin.styleguide') ? 'active' : '' }}" @if(request()->routeIs('admin.styleguide')) aria-current="page" @endif><x-admin.icon name="grid" />Styleguide</a>
        @endcan
    </nav>
    <div class="sidebar-account">
        <p>{{ auth()->user()->name }}</p>
        <form method="POST" action="{{ route('admin.logout') }}">@csrf<x-admin.button variant="secondary" type="submit"><x-admin.icon name="logout" />Sign out</x-admin.button></form>
    </div>
</aside>
