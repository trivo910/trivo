<?php

use App\Jobs\SendSmtpTest;
use App\Mail\SmtpTestMail;
use App\Models\MailLog;
use App\Models\Setting;
use App\Providers\MailConfigServiceProvider;
use App\Services\Mail\SmtpConfigurator;
use App\Services\Mail\SmtpErrorSanitizer;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->seed(RolePermissionSeeder::class);
    $this->admin = adminAccount();
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
});

/** @return array<string, mixed> */
function smtpFixture(): array
{
    return ['host' => 'smtp.example.test', 'port' => 587, 'encryption' => 'tls',
        'username' => 'smtp-user@example.test', 'password' => 'fixture-secret-123!',
        'from_email' => 'sender@example.test', 'from_name' => 'Trivo', 'reply_to' => 'reply@example.test',
        'queue_enabled' => true];
}

it('encrypts SMTP settings, masks the password, and applies TLS configuration', function () {
    $data = smtpFixture();
    $this->put(route('admin.mail.update'), $data)->assertRedirect(route('admin.mail.index'));
    expect(DB::table('settings')->where('key', 'mail.smtp')->value('value'))->not->toContain($data['password'])
        ->and(config('mail.default'))->toBe('trivo')->and(config('mail.mailers.trivo.require_tls'))->toBeTrue()
        ->and(config('mail.mailers.trivo.password'))->toBe($data['password']);
    $this->get(route('admin.mail.index'))->assertOk()->assertDontSee($data['password'])->assertSee('••••••••');
    expect(Activity::query()->pluck('properties')->toJson())->not->toContain($data['password']);
});

it('retains a saved password on blank submission and only clears it explicitly', function () {
    $this->put(route('admin.mail.update'), smtpFixture());
    $this->put(route('admin.mail.update'), array_replace(smtpFixture(), ['password' => '']))->assertSessionHasNoErrors();
    expect(Setting::query()->where('key', 'mail.smtp')->first()->value['password'])->toBe('fixture-secret-123!');
    $this->put(route('admin.mail.update'), array_replace(smtpFixture(), ['password' => '', 'clear_password' => true]));
    expect(Setting::query()->where('key', 'mail.smtp')->first()->value['password'])->toBeNull();
});

it('queues a test containing only a log ID and an encrypted recipient', function () {
    Bus::fake();
    $this->put(route('admin.mail.update'), smtpFixture());
    $this->post(route('admin.mail.test'), ['recipient' => 'recipient@example.test'])->assertRedirect(route('admin.mail.index'));
    $log = MailLog::query()->sole();
    expect($log->status)->toBe('queued')->and($log->recipient)->toBe('recipient@example.test')
        ->and(DB::table('mail_logs')->value('recipient'))->not->toContain('recipient@example.test');
    Bus::assertDispatched(SendSmtpTest::class, fn (SendSmtpTest $job) => $job->mailLogId === $log->id
        && ! str_contains(serialize($job), 'fixture-secret-123!') && ! str_contains(serialize($job), 'recipient@example.test'));
});

it('sends a test with the saved mailer and does not send it twice', function () {
    Mail::fake();
    $this->put(route('admin.mail.update'), array_replace(smtpFixture(), ['queue_enabled' => false]));
    $this->post(route('admin.mail.test'), ['recipient' => 'recipient@example.test'])->assertRedirect(route('admin.mail.index'));
    $log = MailLog::query()->sole();
    expect($log->status)->toBe('sent')->and($log->sent_at)->not->toBeNull();
    app()->call([new SendSmtpTest($log->id), 'handle']);
    Mail::assertSent(SmtpTestMail::class, 1);
});

it('records a sanitized SMTP failure instead of leaking raw exceptions', function () {
    $this->put(route('admin.mail.update'), smtpFixture());
    $log = MailLog::query()->create(['purpose' => 'smtp_test', 'status' => 'queued', 'recipient' => 'recipient@example.test']);
    Mail::shouldReceive('purge')->with('trivo')->once()->andReturnNull();
    Mail::shouldReceive('mailer')->with('trivo')->andThrow(new RuntimeException('535 Authentication failed for smtp-user@example.test with fixture-secret-123!'));
    app()->call([new SendSmtpTest($log->id), 'handle']);
    $log->refresh();
    expect($log->status)->toBe('failed')->and($log->error_message)->toContain('535')
        ->not->toContain('smtp-user@example.test')->not->toContain('fixture-secret-123!');
});

it('redacts SMTP credentials in plain, encoded and URL forms', function () {
    $data = smtpFixture();
    $message = '535 '.base64_encode("\0".$data['username']."\0".$data['password']).' smtp://'.$data['username'].':'.$data['password'].'@smtp.example.test';
    $safe = app(SmtpErrorSanitizer::class)->message(new RuntimeException($message), $data);
    expect($safe)->toContain('535')->not->toContain($data['password'])->not->toContain(base64_encode("\0".$data['username']."\0".$data['password']));
    $encoded = base64_encode($data['username']."\0".$data['username']."\0".$data['password']);
    expect(app(SmtpErrorSanitizer::class)->message(new RuntimeException('535 '.$encoded), $data))->toBe('535 [redacted]');
});

it('requires saved settings and rate limits test requests', function () {
    Bus::fake();
    $this->post(route('admin.mail.test'), ['recipient' => 'recipient@example.test'])->assertSessionHasErrors('recipient');
    $this->put(route('admin.mail.update'), smtpFixture());
    $this->post(route('admin.mail.test'), ['recipient' => 'recipient@example.test'])->assertRedirect();
    $this->post(route('admin.mail.test'), ['recipient' => 'recipient@example.test'])->assertStatus(429);
});

it('denies mail access to editors and SEO managers', function (string $role) {
    $user = adminAccount($role);
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->get(route('admin.mail.index'))->assertForbidden();
    $this->put(route('admin.mail.update'), smtpFixture())->assertForbidden();
    $this->post(route('admin.mail.test'), ['recipient' => 'recipient@example.test'])->assertForbidden();
})->with(['Editor', 'SEO Manager']);

it('rejects header injection and plaintext SMTP in production', function () {
    $this->put(route('admin.mail.update'), array_replace(smtpFixture(), ['from_name' => "Trivo\r\nBcc: x@example.test"]))->assertSessionHasErrors('from_name');
    app()->instance('env', 'production');
    $this->withSession(['_token' => 'csrf-fixture'])->put(route('admin.mail.update'), array_replace(smtpFixture(), ['encryption' => 'none', '_token' => 'csrf-fixture']))->assertSessionHasErrors('encryption');
});

it('does not materialize stored SMTP passwords during console configuration boot', function () {
    $this->put(route('admin.mail.update'), smtpFixture());
    config(['mail.mailers.trivo' => null]);
    (new MailConfigServiceProvider(app()))->boot();
    expect(config('mail.mailers.trivo'))->toBeNull();
    app(SmtpConfigurator::class)->apply();
    expect(config('mail.mailers.trivo.password'))->toBe('fixture-secret-123!');
});
