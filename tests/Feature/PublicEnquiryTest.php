<?php

use App\Jobs\SendLeadMail;
use App\Mail\LeadMail;
use App\Models\Lead;
use App\Models\MailLog;
use App\Models\Setting;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    Bus::fake([SendLeadMail::class]);
    Mail::fake();
});

function enquiryPayload(string $form = 'contact'): array
{
    return ['form' => $form, 'name' => 'Test Visitor', 'email' => 'visitor@example.test',
        'phone' => '+91 99999 99999', 'company' => 'Test company', 'message' => 'Please contact me.',
        'website' => '', 'status' => 'closed', 'handled_by' => 99];
}

it('saves each public form, queues both emails and appears in the admin inbox', function (string $form) {
    $this->post(route('enquiries.store'), enquiryPayload($form))->assertRedirect(route('thank-you'));
    $lead = Lead::query()->sole();
    expect($lead->status)->toBe('new')->and($lead->handled_by)->toBeNull()
        ->and($lead->email)->toBe('visitor@example.test')->and($lead->ip_hash)->toHaveLength(64);
    expect(MailLog::query()->count())->toBe(2);
    Bus::assertDispatched(SendLeadMail::class, 2);
    Bus::assertDispatched(SendLeadMail::class, fn ($job) => $job->connection === 'database'
        && ! str_contains(serialize($job), 'visitor@example.test'));
    Mail::assertNothingSent();
    $this->seed(RolePermissionSeeder::class);
    $admin = adminAccount('Admin');
    $this->actingAs($admin)->withSession(verifiedAdminSession($admin));
    $this->get(route('admin.leads.index'))->assertOk()->assertSee('Test Visitor');
    $this->get(route('admin.leads.show', $lead))->assertOk()->assertSee('Visitor thank-you')->assertSee('Admin notification');
})->with(['contact', 'audit', 'proposal']);

it('rejects invalid submissions without creating leads or email jobs', function (array $changes) {
    $this->from(route('contact'))->post(route('enquiries.store'), array_replace(enquiryPayload(), $changes))
        ->assertRedirect(route('contact'))->assertSessionHasErrors();
    expect(Lead::query()->count())->toBe(0)->and(MailLog::query()->count())->toBe(0);
    Bus::assertNotDispatched(SendLeadMail::class);
})->with([
    [['email' => 'invalid']], [['name' => '']], [['phone' => 'x']],
    [['message' => '']], [['website' => 'spam']], [['form' => 'unknown']],
    [['message' => str_repeat('a', 10001)]],
]);

it('rate limits public submissions', function () {
    foreach (range(1, 3) as $i) {
        $this->post(route('enquiries.store'), enquiryPayload('audit'))->assertRedirect(route('thank-you'));
    }
    $this->post(route('enquiries.store'), enquiryPayload())->assertStatus(429);
    expect(Lead::query()->count())->toBe(3);
});

it('requires CSRF for public submissions', function () {
    app()->instance('env', 'local');
    $this->post(route('enquiries.store'), enquiryPayload())->assertStatus(419);
    expect(Lead::query()->count())->toBe(0);
});

it('renders working forms on the homepage and contact page', function () {
    $this->get('/')->assertOk()->assertSee('action="'.route('enquiries.store').'"', false)
        ->assertSee('name="phone"', false)->assertSee('name="_token"', false);
    $this->get('/contact')->assertOk()->assertSee('action="'.route('enquiries.store').'"', false)
        ->assertSee('name="form" value="contact"', false)->assertSee('name="_token"', false)
        ->assertDontSee('data-home-enquiry="contact"', false);
});

it('delivers both messages through saved SMTP settings and avoids repeat delivery', function (bool $customRecipient) {
    Setting::query()->create(['key' => 'mail.smtp', 'group' => 'mail', 'value' => [
        'host' => 'smtp.example.test', 'port' => 587, 'encryption' => 'tls',
        'from_email' => 'sender@example.test', 'from_name' => 'Trivo',
        'notification_email' => $customRecipient ? 'inbox@example.test' : null,
    ]]);
    $this->post(route('enquiries.store'), enquiryPayload());
    foreach (MailLog::query()->get() as $log) {
        app()->call([new SendLeadMail($log->id), 'handle']);
        app()->call([new SendLeadMail($log->id), 'handle']);
        expect($log->fresh()->status)->toBe('sent');
    }
    Mail::assertSent(LeadMail::class, 2);
    Mail::assertSent(LeadMail::class, fn ($mail) => $mail->thankYou && $mail->hasTo('visitor@example.test'));
    Mail::assertSent(LeadMail::class, fn ($mail) => ! $mail->thankYou && $mail->hasTo($customRecipient ? 'inbox@example.test' : 'sender@example.test')
        && $mail->envelope()->replyTo[0]->address === 'visitor@example.test');
})->with([true, false]);

it('retains leads and independently records failures when SMTP is missing', function () {
    $this->post(route('enquiries.store'), enquiryPayload())->assertRedirect(route('thank-you'));
    foreach (MailLog::query()->get() as $log) {
        app()->call([new SendLeadMail($log->id), 'handle']);
        expect($log->fresh()->status)->toBe('failed');
    }
    expect(Lead::query()->count())->toBe(1);
    Mail::assertNothingSent();
});

it('escapes visitor input in notification emails and keeps it out of thank-you content', function () {
    $lead = Lead::query()->create(['name' => '<script>alert(1)</script>', 'email' => 'visitor@example.test',
        'message' => '<img src=x onerror=alert(1)>']);
    expect((new LeadMail($lead, false))->render())->toContain('&lt;img')->not->toContain('<img src=x');
    expect((new LeadMail($lead, true))->render())->not->toContain('onerror');
});
