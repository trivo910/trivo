<x-admin.layout title="Set up two-factor authentication" guest>
    <p>Add a new account in your authenticator app using this setup key.</p>
    <p class="secret-key" aria-label="Authenticator setup key">{{ $secret }}</p>
    <p><a class="text-link" href="{{ $uri }}">Open in an authenticator app</a></p>
    <p class="helper">Choose a time-based account, then enter its six-digit code below.</p>
    <form method="POST" action="{{ route('admin.enroll') }}" class="form-stack">
        @csrf
        <x-admin.input name="code" label="Authenticator code" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="one-time-code" required autofocus />
        <x-admin.button type="submit" class="full-width">Enable two-factor authentication</x-admin.button>
    </form>
</x-admin.layout>
