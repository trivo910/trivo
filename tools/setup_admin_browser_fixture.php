<?php

/** Create synthetic users only in the explicitly isolated browser-QA database. */
declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

require dirname(__DIR__).'/vendor/autoload.php';
$application = require dirname(__DIR__).'/bootstrap/app.php';
$application->make(Kernel::class)->bootstrap();
if (! $application->environment('local')) {
    throw new RuntimeException('Browser fixture is local-only.');
}
$directory = base_path('.audit/browser-qa');
if (! is_dir($directory)) {
    mkdir($directory, 0700, true);
}
$database = $directory.'/ui.sqlite';
touch($database);
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $database]);
DB::purge('sqlite');
Artisan::call('migrate', ['--force' => true]);
Artisan::call('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);
if (User::query()->where('email', '!=', 'browser-fixture@example.test')->exists()) {
    throw new RuntimeException('Unexpected data in browser fixture database.');
}
$password = bin2hex(random_bytes(24));
$secret = app(Google2FA::class)->generateSecretKey(32);
$codes = array_map(fn () => strtoupper(bin2hex(random_bytes(8))), range(1, 5));
$user = User::query()->firstOrNew(['email' => 'browser-fixture@example.test']);
$user->forceFill(['name' => 'Preview Administrator', 'password' => $password, 'is_active' => true,
    'theme' => 'system', 'two_factor_secret' => $secret, 'two_factor_confirmed_at' => now(),
    'two_factor_last_used_step' => null,
    'two_factor_recovery_codes' => array_map(fn (string $code) => Hash::make($code), $codes)])->save();
$user->assignRole('Super Admin');
file_put_contents($directory.'/credentials.json', json_encode(['email' => $user->email,
    'password' => $password, 'secret' => $secret, 'codes' => $codes, 'admin_path' => config('trivo.admin_path')], JSON_THROW_ON_ERROR));
echo "Prepared isolated browser fixture; application database unchanged.\n";
