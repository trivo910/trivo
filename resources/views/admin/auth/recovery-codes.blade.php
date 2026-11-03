<x-admin.layout title="Save your recovery codes">
    <x-admin.glass-card strong>
        <h2>Keep these codes somewhere safe</h2>
        <p>Each code works once if you lose access to your authenticator. These codes are shown only once.</p>
        <ul class="recovery-codes">@foreach($codes as $code)<li><code>{{ $code }}</code></li>@endforeach</ul>
        <x-admin.button :href="route('admin.dashboard')">I have saved my codes</x-admin.button>
    </x-admin.glass-card>
</x-admin.layout>
