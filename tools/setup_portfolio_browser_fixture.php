<?php

/** Create portfolio QA data only in the dedicated SQLite database, never the application database. */
declare(strict_types=1);

use App\Models\PortfolioItem;
use App\Models\User;
use Database\Seeders\LuhaifPortfolioSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! $app->environment('local')) {
    throw new RuntimeException('Portfolio browser fixtures are local-only.');
}
$directory = base_path('.audit/portfolio-reference');
if (! is_dir($directory.'/views')) {
    mkdir($directory.'/views', 0700, true);
}
$database = $directory.'/qa.sqlite';
touch($database);
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $database,
    'database.connections.sqlite.url' => null]);
DB::purge('sqlite');
Artisan::call('migrate', ['--force' => true]);
if (User::query()->where('email', '!=', 'portfolio-browser@example.test')->exists()) {
    throw new RuntimeException('Unexpected accounts in the isolated portfolio fixture.');
}
Artisan::call('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);
Artisan::call('db:seed', ['--class' => LuhaifPortfolioSeeder::class, '--force' => true]);
$password = bin2hex(random_bytes(24));
$user = User::query()->firstOrNew(['email' => 'portfolio-browser@example.test']);
$user->forceFill(['name' => 'Portfolio Browser', 'password' => $password, 'is_active' => true])->save();
$user->assignRole('Super Admin');
file_put_contents($directory.'/credentials.json', json_encode([
    'email' => $user->email, 'password' => $password, 'admin_path' => config('trivo.admin_path'),
    'portfolio_id' => PortfolioItem::query()->where('path', '/luhaif-perfumes')->sole()->id,
], JSON_THROW_ON_ERROR));
echo 'Isolated portfolio browser fixture ready. No application profiles were modified.'.PHP_EOL;
