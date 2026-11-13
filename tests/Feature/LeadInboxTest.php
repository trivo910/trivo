<?php

use App\Models\Lead;
use App\Services\Leads\LeadInbox;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = adminAccount('Admin');
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
});

function syntheticInboxLead(array $values = []): Lead
{
    return Lead::query()->create(array_replace(['name' => 'Synthetic enquiry', 'email' => 'synthetic@example.test', 'phone' => '+919999999999',
        'company' => 'Synthetic company', 'service' => 'Synthetic service', 'message' => "Synthetic first line.\nSynthetic second line.",
        'source_url' => 'https://example.test/source', 'utm' => ['source' => 'synthetic-tracking'], 'ip_hash' => hash('sha256', 'synthetic-ip')], $values));
}

/** @return list<array<int, string|null>> */
function inboxCsvRows(string $csv): array
{
    $stream = fopen('php://temp', 'w+');
    fwrite($stream, substr($csv, 3));
    rewind($stream);
    $rows = [];
    while (($row = fgetcsv($stream, 0, ',', '"', '')) !== false) {
        $rows[] = $row;
    }
    fclose($stream);

    return $rows;
}

it('renders original enquiry data escaped without exposing tracking or IP hashes', function () {
    $lead = syntheticInboxLead(['name' => '<script>unsafe()</script>', 'message' => '<img src=x onerror=unsafe()>', 'source_url' => 'javascript:unsafe()']);
    $this->get(route('admin.leads.show', $lead))->assertOk()->assertSee('<script>unsafe()</script>')->assertDontSee('<script>unsafe()</script>', false)
        ->assertSee('<img src=x onerror=unsafe()>')->assertDontSee('<img src=x onerror=unsafe()>', false)->assertDontSee('synthetic-tracking')->assertDontSee($lead->ip_hash)
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    $this->get(route('admin.leads.index'))->assertOk()->assertSee('Export filtered CSV');
});

it('filters by search status service and inclusive UTC dates with stable ordering', function () {
    $first = syntheticInboxLead(['name' => 'First match', 'status' => 'qualified']);
    $first->forceFill(['created_at' => '2026-11-01 00:00:00'])->save();
    $last = syntheticInboxLead(['name' => 'Last match', 'status' => 'qualified']);
    $last->forceFill(['created_at' => '2026-11-02 23:59:59'])->save();
    syntheticInboxLead(['name' => 'Wrong status', 'status' => 'new']);
    $outside = syntheticInboxLead(['name' => 'Outside date', 'status' => 'qualified']);
    $outside->forceFill(['created_at' => '2026-11-03 00:00:00'])->save();
    $filters = ['q' => 'match', 'status' => 'qualified', 'service' => 'Synthetic service', 'from' => '2026-11-01', 'to' => '2026-11-02'];
    $this->get(route('admin.leads.index', $filters))->assertOk()->assertSeeInOrder(['Last match', 'First match'])->assertDontSee('Wrong status')->assertDontSee('Outside date');
    $rows = inboxCsvRows($this->get(route('admin.leads.export', $filters))->assertOk()->streamedContent());
    expect(array_column(array_slice($rows, 1), 1))->toBe(['Last match', 'First match']);
    syntheticInboxLead(['name' => 'Zero match', 'company' => '0', 'service' => '0']);
    $this->get(route('admin.leads.index', ['q' => '0', 'service' => '0']))->assertSee('Zero match')->assertDontSee('First match');
});

it('validates inbox and export filters', function (array $filters, string $field) {
    $this->get(route('admin.leads.index', $filters))->assertSessionHasErrors($field);
    $this->get(route('admin.leads.export', $filters))->assertSessionHasErrors($field);
})->with([
    [['status' => 'unsupported'], 'status'], [['q' => str_repeat('a', 121)], 'q'], [['service' => ['malformed']], 'service'],
    [['from' => 'tomorrow'], 'from'], [['to' => '2026-02-30'], 'to'], [['from' => '2026-11-03', 'to' => '2026-11-02'], 'to'],
]);

it('updates only follow-up fields with server-selected actor version checks and PII-free activity', function () {
    Mail::fake();
    $lead = syntheticInboxLead();
    $this->put(route('admin.leads.update', $lead), ['status' => 'contacted', 'notes' => "Synthetic internal note.\nSecond line.", 'content_version' => 1,
        'name' => 'Injected name', 'email' => 'injected@example.test', 'message' => 'Injected message', 'utm' => ['source' => 'injected'], 'handled_by' => 999])->assertSessionHasNoErrors();
    expect($lead->fresh()->status)->toBe('contacted')->and($lead->fresh()->content_version)->toBe(2)->and($lead->fresh()->handled_by)->toBe($this->admin->id)
        ->and($lead->fresh()->name)->toBe('Synthetic enquiry')->and($lead->fresh()->email)->toBe('synthetic@example.test')->and($lead->fresh()->utm)->toBe(['source' => 'synthetic-tracking']);
    $this->put(route('admin.leads.update', $lead), ['status' => 'closed', 'notes' => 'Stale note', 'content_version' => 1])->assertSessionHasErrors('content_version');
    expect($lead->fresh()->notes)->toBe("Synthetic internal note.\nSecond line.");
    $activity = Activity::query()->where('event', 'lead_updated')->sole();
    expect($activity->description)->toBe('lead_updated')->and($activity->properties->toJson())->not->toContain('Synthetic internal', 'synthetic@example.test')->and($activity->subject_id)->toBe($lead->id);
    Mail::assertNothingSent();
    Mail::assertNothingQueued();
});

it('rejects unsafe or invalid follow-up changes without mutations', function (array $values, string $field) {
    $lead = syntheticInboxLead();
    $this->put(route('admin.leads.update', $lead), array_replace(['status' => 'contacted', 'notes' => 'Safe note', 'content_version' => 1], $values))->assertSessionHasErrors($field);
    expect($lead->fresh()->status)->toBe('new')->and($lead->fresh()->content_version)->toBe(1)->and(Activity::query()->where('event', 'lead_updated')->count())->toBe(0);
})->with([
    [['status' => 'invented'], 'status'], [['notes' => '<script>unsafe()</script>'], 'notes'], [['notes' => "bad\x00note"], 'notes'],
    [['notes' => str_repeat('a', 10001)], 'notes'], [['content_version' => 0], 'content_version'],
]);

it('exports quoted UTF8 CSV as text and excludes private processing fields', function () {
    syntheticInboxLead(['name' => '=SUM(1,2)', 'email' => 'synthetic@example.test', 'company' => '  @SUM(1,2)', 'notes' => 'PRIVATE-NOTE', 'message' => "First, line\nSecond \"quoted\" line"]);
    $response = $this->get(route('admin.leads.export'))->assertOk()->assertHeader('X-Export-Limit', '1000')->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $csv = $response->streamedContent();
    expect(str_starts_with($csv, "\xEF\xBB\xBF"))->toBeTrue()->and($csv)->not->toContain('PRIVATE-NOTE', 'synthetic-tracking', hash('sha256', 'synthetic-ip'));
    $rows = inboxCsvRows($csv);
    expect($rows[1][1])->toBe("'=SUM(1,2)")->and($rows[1][3])->toBe("'+919999999999")->and($rows[1][4])->toBe("'  @SUM(1,2)")
        ->and($rows[1][6])->toBe("First, line\nSecond \"quoted\" line")->and($rows[0])->not->toContain('notes', 'ip_hash', 'utm', 'handled_by');
    expect(Activity::query()->where('event', 'leads_exported')->count())->toBe(1);
});

it('neutralizes spreadsheet formula prefixes including whitespace controls and Unicode spacing', function (string $value) {
    expect(app(LeadInbox::class)->csvCell($value))->toBe("'".$value);
})->with(['=1+1', '+1', '-1', '@SUM(1,2)', "\t=1", "\r=1", "\ntext", '   =1', "\u{00A0}=1", "\u{FEFF}=1"]);

it('caps exports at the newest 1000 matching records', function () {
    $rows = [];
    for ($index = 0; $index < 1002; $index++) {
        $rows[] = ['name' => 'Synthetic '.$index, 'email' => 'synthetic@example.test', 'status' => 'new', 'created_at' => now(), 'updated_at' => now()];
    }
    foreach (array_chunk($rows, 100) as $chunk) {
        Lead::query()->insert($chunk);
    }
    $csv = inboxCsvRows($this->get(route('admin.leads.export'))->assertOk()->streamedContent());
    expect($csv)->toHaveCount(1001)->and($csv[1][1])->toBe('Synthetic 1001')->and($csv[1000][1])->toBe('Synthetic 2');
});

it('requires view and manage independently for updates and exports and denies editor SEO access', function () {
    $lead = syntheticInboxLead();
    $this->admin->roles()->firstOrFail()->revokePermissionTo('leads.manage');
    $this->actingAs($this->admin->refresh())->withSession(verifiedAdminSession($this->admin));
    $this->get(route('admin.leads.show', $lead))->assertOk()->assertDontSee('Save lead');
    $this->get(route('admin.leads.index'))->assertOk()->assertDontSee('Export filtered CSV');
    $this->get(route('admin.leads.export'))->assertForbidden();
    $this->put(route('admin.leads.update', $lead), ['status' => 'closed', 'content_version' => 1])->assertForbidden();
    $role = $this->admin->roles()->firstOrFail();
    $role->givePermissionTo('leads.manage');
    $role->revokePermissionTo('leads.view');
    $this->actingAs($this->admin->refresh())->withSession(verifiedAdminSession($this->admin));
    $this->get(route('admin.leads.show', $lead))->assertForbidden();
    $this->get(route('admin.leads.export'))->assertForbidden();
    foreach (['Editor', 'SEO Manager'] as $role) {
        $user = adminAccount($role);
        $this->actingAs($user)->withSession(verifiedAdminSession($user));
        $this->get(route('admin.leads.index'))->assertForbidden();
        $this->get(route('admin.leads.show', $lead))->assertForbidden();
        $this->get(route('admin.leads.export'))->assertForbidden();
    }
});

it('requires CSRF and second factor sessions and provides no manual creation or deletion routes', function () {
    $lead = syntheticInboxLead();
    $this->app['env'] = 'local';
    $this->put(route('admin.leads.update', $lead), ['status' => 'closed', 'content_version' => 1])->assertStatus(419);
    $this->post(route('admin.leads.index'), [])->assertStatus(405);
    $this->delete(route('admin.leads.show', $lead))->assertStatus(405);
    $this->flushSession();
    $this->get(route('admin.leads.index'))->assertRedirect(route('admin.login'));
    $this->get(route('admin.leads.export'))->assertRedirect(route('admin.login'));
});
