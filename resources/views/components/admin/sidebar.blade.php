<aside id="admin-sidebar" class="glass admin-sidebar" :class="{ 'is-open': sidebarOpen }" :inert="compact && !sidebarOpen" aria-label="Administration navigation">
    <button x-cloak x-show="compact" type="button" class="admin-button button-secondary" @click="closeSidebar">Close navigation</button>
    <a class="brand" href="{{ route('admin.dashboard') }}">trivo<span>Administration</span></a>
    <nav aria-label="Main navigation">
        <p class="nav-group">Overview</p>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif><x-admin.icon name="home" />Dashboard</a>
        @can('media.manage')
            <p class="nav-group">Media</p>
            <a href="{{ route('admin.media.index') }}" class="nav-item {{ request()->routeIs('admin.media.*') ? 'active' : '' }}" @if(request()->routeIs('admin.media.*')) aria-current="page" @endif><x-admin.icon name="grid" />Image library</a>
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
