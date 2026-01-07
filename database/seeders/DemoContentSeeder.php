<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Section;
use App\Models\User;
use App\Models\Edition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first() ?? User::factory()->create(['name' => 'Admin']);
        $edition = Edition::first() ?? Edition::create(['name' => 'International', 'code' => 'international', 'is_default' => true]);

        $sections = ['News', 'Opinion', 'Sport', 'Culture', 'Lifestyle'];
        foreach ($sections as $name) {
            $section = Section::firstOrCreate(['name' => $name], ['slug' => Str::slug($name)]);
            
            Article::factory()->count(5)->create([
                'section_id' => $section->id,
                'status' => 'published',
                'published_at' => now(),
            ])->each(function($a) use ($edition, $admin) {
                $a->editions()->syncWithoutDetaching([$edition->id]);
                $a->authors()->syncWithoutDetaching([$admin->id]);
            });
        }
    }
}
