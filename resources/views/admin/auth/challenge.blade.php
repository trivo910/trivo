<x-admin.layout title="Verify your sign-in" guest>
    <p class="helper">Enter the current code from your authenticator, or an unused recovery code.</p>
    <form method="POST" action="{{ route('admin.challenge') }}" class="form-stack">
        @csrf
        <x-admin.input name="code" label="Authenticator or recovery code" autocomplete="one-time-code" minlength="6" maxlength="64" required autofocus />
        <x-admin.button type="submit" class="full-width">Verify and sign in</x-admin.button>
    </form>
</x-admin.layout>
