@props(['title', 'guest' => false])
<!doctype html>
<html lang="en" data-theme="light" data-theme-choice="{{ auth()->user()?->theme ?? 'system' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} | Trivo Admin</title>
    <link rel="preload" href="{{ asset('fonts/montserrat-latin-400.woff2') }}" as="font" type="font/woff2" crossorigin>
    <script nonce="{{ Illuminate\Support\Facades\Vite::cspNonce() }}">
        (() => { const root = document.documentElement; const choice = root.dataset.themeChoice;
            root.dataset.theme = choice === 'system' ? (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : choice;
        })();
    </script>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin-body" x-data="adminShell" @keydown.escape.window="closeSidebar" @keydown.tab.window="trapNavigation">
    <a class="skip-link" href="#main-content">Skip to content</a>
    @if ($guest)
        <main id="main-content" class="auth-shell">
            <x-admin.glass-card class="auth-card" strong>
                <a class="brand" href="{{ route('admin.login') }}">trivo<span>Administration</span></a>
                <h1>{{ $title }}</h1>
                <x-admin.errors />
                {{ $slot }}
            </x-admin.glass-card>
        </main>
    @else
        <button x-cloak x-show="sidebarOpen" @click="closeSidebar" class="sidebar-backdrop" aria-label="Close navigation"></button>
        <x-admin.sidebar />
        <div class="admin-workspace">
            <header class="glass admin-topbar">
                <button type="button" class="icon-button mobile-menu" @click="toggleSidebar" :aria-expanded="sidebarOpen" aria-controls="admin-sidebar" aria-label="Toggle navigation"><x-admin.icon name="menu" /></button>
                <div><p class="eyebrow">Trivo administration</p><h1>{{ $title }}</h1></div>
                <form method="POST" action="{{ route('admin.preferences') }}" class="theme-form">
                    @csrf @method('PATCH')
                    <input type="hidden" name="return_to" value="{{ in_array(request()->route()?->getName(), \App\Support\AdminAccess::THEME_ROUTES, true) ? request()->route()->getName() : 'admin.dashboard' }}">
                    @if(request()->routeIs('admin.website.edit'))<input type="hidden" name="return_section" value="{{ request()->route('section') }}">@endif
                    <label for="theme">Theme</label>
                    <select name="theme" id="theme">
                        @foreach (['system' => 'System', 'light' => 'Light', 'dark' => 'Dark'] as $value => $label)
                            <option value="{{ $value }}" @selected(auth()->user()?->theme === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-admin.button variant="secondary" type="submit">Apply</x-admin.button>
                </form>
            </header>
            <main id="main-content" class="admin-main" tabindex="-1">
                <x-admin.errors />
                @if (session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
                {{ $slot }}
            </main>
        </div>
    @endif
</body>
</html>
