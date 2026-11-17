<?php

namespace Database\Seeders;

use App\Services\Content\ImportPortfolioReference;
use Illuminate\Database\Seeder;

class LuhaifPortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('seeders/data/luhaif-perfumes.json')), true, 512, JSON_THROW_ON_ERROR);
        app(ImportPortfolioReference::class)->handle($data);
        $this->command->info('Luhaif portfolio ready; existing admin edits retained.');
    }
}
