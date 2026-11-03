<?php

use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Deterministic fixture: production uses Laravel's real breach verifier.
    app()->instance(UncompromisedVerifier::class, new class implements UncompromisedVerifier
    {
        public function verify($data): bool
        {
            return true;
        }
    });
});

it('provisions an explicit admin through hidden password prompts without enabling access before enrollment', function () {
    $password = 'A-strong-fixture-Password12!';
    $this->artisan('trivo:admin-create')
        ->expectsQuestion('Name', 'Test Editor')
        ->expectsQuestion('Email address', 'editor@example.test')
        ->expectsQuestion('Password (12+ characters, mixed case, number and symbol)', $password)
        ->expectsChoice('Role', 'Editor', AdminAccess::ROLES)
        ->expectsQuestion('Confirm password', $password)
        ->assertSuccessful();
    $user = User::query()->sole();
    expect($user->hasRole('Editor'))->toBeTrue()
        ->and(Hash::check($password, $user->password))->toBeTrue()
        ->and($user->two_factor_confirmed_at)->toBeNull();
});

it('does not create accounts for mismatched passwords', function () {
    $this->artisan('trivo:admin-create')
        ->expectsQuestion('Name', 'Test Editor')
        ->expectsQuestion('Email address', 'editor@example.test')
        ->expectsQuestion('Password (12+ characters, mixed case, number and symbol)', 'Strong-Password12!')
        ->expectsChoice('Role', 'Editor', AdminAccess::ROLES)
        ->expectsQuestion('Confirm password', 'Different-Password12!')
        ->assertFailed();
    expect(User::query()->count())->toBe(0);
});
