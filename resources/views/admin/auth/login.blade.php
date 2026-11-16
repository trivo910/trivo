<x-admin.layout title="Sign in" guest>
    <p class="helper">Use your administrator account to continue.</p>
    <form method="POST" action="{{ route('admin.authenticate') }}" class="form-stack">
        @csrf
        <x-admin.input name="email" label="Email address" type="email" :value="old('email')" required autocomplete="username" maxlength="254" autofocus />
        <x-admin.input name="password" label="Password" type="password" required autocomplete="current-password" maxlength="1024" />
        <x-admin.button type="submit" class="full-width">Sign in</x-admin.button>
    </form>
</x-admin.layout>
