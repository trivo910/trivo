<?php

/** Copy public article content into a dedicated local QA database. Never writes to the application database. */
declare(strict_types=1);

use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! $app->environment('local')) {
    throw new RuntimeException('Blog browser fixtures are local-only.');
}
$posts = Post::query()->where('status', 'published')->where('published_at', '<=', now())->get();
$media = Media::query()->where('processing_status', 'ready')->get();
$terms = [];
foreach (['authors', 'categories', 'tags', 'post_tag'] as $table) {
    $terms[$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
}
$directory = base_path('.audit/blog-qa');
if (! is_dir($directory.'/views')) {
    mkdir($directory.'/views', 0700, true);
}
$database = $directory.'/qa.sqlite';
touch($database);
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $database,
    'database.connections.sqlite.url' => null]);
DB::purge('sqlite');
Artisan::call('migrate', ['--force' => true]);
if (User::query()->where('email', '!=', 'blog-browser@example.test')->exists()) {
    throw new RuntimeException('Unexpected accounts in the isolated blog fixture.');
}
Artisan::call('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);
$password = bin2hex(random_bytes(24));
$user = User::query()->firstOrNew(['email' => 'blog-browser@example.test']);
$user->forceFill(['name' => 'Blog Browser', 'password' => $password, 'is_active' => true])->save();
$user->assignRole('Super Admin');
foreach (['authors', 'categories', 'tags'] as $table) {
    foreach ($terms[$table] as $row) {
        DB::table($table)->updateOrInsert(['id' => $row['id']], $row);
    }
}
foreach ($media as $image) {
    $attributes = $image->getAttributes();
    $attributes['uploaded_by'] = null;
    DB::table('media')->updateOrInsert(['id' => $image->id], $attributes);
}
foreach ($posts as $post) {
    DB::table('posts')->updateOrInsert(['id' => $post->id], $post->getAttributes());
}
foreach ($terms['post_tag'] as $row) {
    if ($posts->contains('id', $row['post_id'])) {
        DB::table('post_tag')->updateOrInsert(['post_id' => $row['post_id'], 'tag_id' => $row['tag_id']], $row);
    }
}
file_put_contents($directory.'/credentials.json', json_encode([
    'email' => $user->email, 'password' => $password, 'admin_path' => config('trivo.admin_path'),
], JSON_THROW_ON_ERROR));
$articles = $posts->where('content_kind', 'article');
file_put_contents($directory.'/articles.json', $articles->values()->map(fn ($post) => [
    'id' => $post->id, 'title' => $post->title, 'path' => $post->path,
])->toJson(JSON_PRETTY_PRINT));
echo 'Isolated blog fixture ready: '.$articles->count().' published articles copied; application content unchanged.'.PHP_EOL;
