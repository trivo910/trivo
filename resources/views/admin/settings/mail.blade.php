<x-admin.layout title="Mail settings">
    <div class="page-intro"><h2>SMTP delivery</h2><p class="helper">Configure the mail server used for website notifications.</p></div>
    <x-admin.glass-card strong>
        <form method="POST" action="{{ route('admin.mail.update') }}" class="form-stack">@csrf @method('PUT')
            <div class="form-grid">
                <x-admin.input name="host" label="SMTP host" :value="old('host', $settings['host'] ?? '')" required maxlength="253" />
                <x-admin.input name="port" label="Port" type="number" :value="old('port', $settings['port'] ?? 587)" min="1" max="65535" required />
                <x-admin.select name="encryption" label="Encryption" :value="old('encryption', $settings['encryption'] ?? 'tls')" :options="app()->isProduction() ? ['tls' => 'STARTTLS', 'ssl' => 'TLS / SSL'] : ['tls' => 'STARTTLS', 'ssl' => 'TLS / SSL', 'none' => 'None (local development)']" />
                <x-admin.input name="username" label="SMTP username" :value="old('username', $settings['username'] ?? '')" autocomplete="off" maxlength="254" />
                <x-admin.input name="password" label="SMTP password" type="password" autocomplete="new-password" :placeholder="($settings['password_configured'] ?? false) ? '••••••••' : ''" help="Leave blank to keep the saved password." maxlength="2048" />
                <div class="field"><label class="checkbox-label"><input type="checkbox" name="clear_password" value="1"> Remove the saved password</label><p class="helper">Use this only when the mail server does not require a password.</p></div>
                <x-admin.input name="from_email" label="From email" type="email" :value="old('from_email', $settings['from_email'] ?? '')" required maxlength="254" />
                <x-admin.input name="from_name" label="From name" :value="old('from_name', $settings['from_name'] ?? '')" required maxlength="100" />
                <x-admin.input name="notification_email" label="Enquiry notification email" type="email" :value="old('notification_email', $settings['notification_email'] ?? '')" maxlength="254" help="Receives website enquiries. Leave blank to use the From email." />
                <x-admin.input name="reply_to" label="Reply-to email (optional)" type="email" :value="old('reply_to', $settings['reply_to'] ?? '')" maxlength="254" />
                <x-admin.select name="queue_enabled" label="Test email delivery" :value="old('queue_enabled', (int) ($settings['queue_enabled'] ?? true))" :options="[1 => 'Queue in the background', 0 => 'Send immediately']" help="Form notifications always use the background queue." />
            </div>
            <div><x-admin.button type="submit">Save SMTP settings</x-admin.button></div>
        </form>
    </x-admin.glass-card>
    <x-admin.glass-card strong>
        <h2>Send a test email</h2><p class="helper">Save your settings first. Queued tests are processed by the scheduled worker.</p>
        <form method="POST" action="{{ route('admin.mail.test') }}" class="form-stack">@csrf
            <x-admin.input name="recipient" label="Test recipient" type="email" :value="old('recipient', auth()->user()->email)" required maxlength="254" />
            <div><x-admin.button type="submit" variant="secondary">Send test email</x-admin.button></div>
        </form>
    </x-admin.glass-card>
    <x-admin.table caption="Recent SMTP tests"><x-slot:head><tr><th scope="col">Test</th><th scope="col">Status</th><th scope="col">Result</th></tr></x-slot:head>
        @forelse($logs as $log)<tr><td>#{{ $log->id }}</td><td>{{ ucfirst($log->status) }}</td><td>{{ $log->error_message ?: ($log->sent_at ? 'SMTP accepted at '.$log->sent_at->format('Y-m-d H:i:s').' UTC' : 'Awaiting processing') }}</td></tr>
        @empty<tr><td colspan="3">No test emails requested yet.</td></tr>@endforelse
    </x-admin.table>
</x-admin.layout>
