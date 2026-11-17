<?php

namespace Database\Seeders;

use App\Services\Content\ImportPortfolioReference;
use Illuminate\Database\Seeder;

class PortfolioProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $manifest = json_decode(file_get_contents(database_path('seeders/data/portfolio-profiles.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($manifest as $entry) {
            $data = json_decode(file_get_contents(database_path('seeders/data/'.$entry['file'])), true, 512, JSON_THROW_ON_ERROR);
            $portfolio = app(ImportPortfolioReference::class)->handle($data);
            $this->command->info($portfolio->title.' ready; existing admin edits retained.');
        }
    }
}
