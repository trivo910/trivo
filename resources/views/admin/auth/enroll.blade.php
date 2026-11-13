<x-admin.layout title="Set up two-factor authentication" guest>
    <p>Scan this QR code with your authenticator app to add your Trivo Admin account.</p>
    <div class="auth-qr" data-enrollment-qr="{{ $uri }}">
        <img alt="Authenticator setup QR code" width="240" height="240" hidden>
        <p class="helper" data-qr-status role="status" aria-live="polite">Preparing QR code...</p>
    </div>
    <p class="helper">After scanning, enter the six-digit code from your authenticator app below.</p>
    <details class="setup-key-fallback" data-setup-key-fallback>
        <summary>Can't scan? Enter the setup key manually</summary>
        <p class="secret-key" aria-label="Authenticator setup key">{{ $secret }}</p>
        <p><a class="text-link" href="{{ $uri }}">Open in an authenticator app</a></p>
    </details>
    <form method="POST" action="{{ route('admin.enroll') }}" class="form-stack">
        @csrf
        <x-admin.input name="code" label="Authenticator code" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="one-time-code" required autofocus />
        <x-admin.button type="submit" class="full-width">Enable two-factor authentication</x-admin.button>
    </form>
</x-admin.layout>
