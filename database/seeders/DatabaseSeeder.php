<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Edition;
use App\Models\Section;
use App\Models\Series;
use App\Models\Tag;
use App\Models\User;
use App\Models\LiveUpdate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed Legacy System Data (Roles, Settings, etc.)
        $this->call(LegacyDataSeeder::class);

        // 0.1 Create Test Users (Idempotent)
        $password = \Illuminate\Support\Facades\Hash::make('password');
        
        // Admin
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => $password,
                'role' => 'admin',
                'is_staff' => true,
                'email_verified_at' => now(),
            ]
        );

        // Editor
        User::firstOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Chief Editor',
                'password' => $password,
                'role' => 'editor',
                'is_staff' => true,
                'email_verified_at' => now(),
            ]
        );

        // Journalist
        User::firstOrCreate(
            ['email' => 'journalist@example.com'],
            [
                'name' => 'Jane Journalist',
                'password' => $password,
                'role' => 'journalist',
                'is_staff' => true,
                'email_verified_at' => now(),
            ]
        );

        // Subscriber
        User::firstOrCreate(
            ['email' => 'subscriber@example.com'],
            [
                'name' => 'Sam Subscriber',
                'password' => $password,
                'role' => 'subscriber',
                'is_staff' => false,
                'email_verified_at' => now(),
            ]
        );

        // Regular User
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular Joe',
                'password' => $password,
                'role' => 'user',
                'is_staff' => false,
                'email_verified_at' => now(),
            ]
        );
        
        // 1. Access or Create Journalists
        if (User::where('role', 'journalist')->count() === 0) {
             $journalists = User::factory(5)->create([
                'role' => 'journalist',
                'is_staff' => true,
            ]);
        } else {
             $journalists = User::where('role', 'journalist')->get();
        }

        // 2. Run Monetization Seeder (Prioritized)
        $this->call(MonetizationSeeder::class);

        // 3. Create Editions
        $editions = [
            ['name' => 'International', 'code' => 'international', 'is_default' => true],
            ['name' => 'UK', 'code' => 'uk', 'is_default' => false],
            ['name' => 'US', 'code' => 'us', 'is_default' => false],
            ['name' => 'Australia', 'code' => 'au', 'is_default' => false],
            ['name' => 'Europe', 'code' => 'eu', 'is_default' => false],
        ];

        foreach ($editions as $editionData) {
            Edition::firstOrCreate(
                ['code' => $editionData['code']],
                $editionData
            );
        }
        $allEditions = Edition::all();

        // 4. Create Sections
        $sections = [
            'News' => ['World', 'UK', 'US', 'Climate', 'Science'],
            'Sport' => ['Football', 'Cricket', 'Rugby', 'Tennis'],
            'Opinion' => ['Editorial', 'Columnists'],
            'Culture' => ['Books', 'Music', 'TV & Radio', 'Art & Design'],
            'Lifestyle' => ['Food', 'Health & Fitness', 'Travel', 'Money'],
        ];

        $allSections = collect();
        foreach ($sections as $parentName => $subsections) {
            $parent = Section::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'name' => $parentName,
                    'is_active' => true,
                ]
            );
            $allSections->push($parent);

            foreach ($subsections as $subName) {
                $child = Section::firstOrCreate(
                    ['slug' => Str::slug($subName)],
                    [
                        'name' => $subName,
                        'parent_id' => $parent->id,
                        'is_active' => true,
                    ]
                );
                $allSections->push($child);
            }
        }

        // 5. Create Tags
        try {
            $tags = Tag::factory(20)->create();
        } catch (\Exception $e) {
            $tags = Tag::all();
        }

        // 6. Create Series
        $series = [
            'The Long Read',
            'Guardian Opinion',
            'Football Weekly',
            'The Guide',
        ];

        foreach ($series as $seriesTitle) {
            if ($allSections->isNotEmpty() && $journalists->isNotEmpty()) {
                Series::firstOrCreate(
                    ['slug' => Str::slug($seriesTitle)],
                    [
                        'title' => $seriesTitle,
                        'section_id' => $allSections->random()->id,
                        'author_id' => $journalists->random()->id,
                    ]
                );
            }
        }
        $allSeries = Series::all();

        // 7. Create Articles
        try {
            if ($journalists->isNotEmpty() && $allSections->isNotEmpty()) {
                Article::factory(50)->create([
                    'status' => 'published',
                    'published_at' => now()->subDays(rand(0, 30)),
                ])->each(function ($article) use ($journalists, $tags, $allEditions, $allSections, $allSeries) {
                    $article->update([
                        'section_id' => $allSections->random()->id,
                        'author_id' => $journalists->random()->id,
                        'series_id' => rand(0, 10) > 8 ? ($allSeries->count() > 0 ? $allSeries->random()->id : null) : null,
                    ]);

                    if ($tags->count() > 0) {
                        // attach uses pivot table, duplicate relation check might fail if not checked, but factory usually handles it.
                        // Actually attach doesn't check unique by default, using syncWithoutDetaching is better, but attach is standard in seeders.
                        // We'll wrap in try catch loop inside if truly needed, but strict mode might be off.
                        try {
                            $article->tags()->attach($tags->random(rand(2, 5)), ['relevance_score' => rand(1, 10)]);
                        } catch (\Exception $e) {}
                    }

                    if ($allEditions->count() > 0) {
                        try {
                            $article->editions()->attach($allEditions->random(rand(1, 3)));
                        } catch (\Exception $e) {}
                    }

                    if ($article->format === 'live') {
                        LiveUpdate::factory(rand(5, 12))->create([
                            'article_id' => $article->id,
                            'author_id' => $journalists->random()->id,
                        ]);
                    }
                });
            }
        } catch (\Exception $e) {
            // content seeding error
        }

        // 8. Create Drafts
        try {
            Article::factory(10)->create(['status' => 'draft']);
        } catch (\Exception $e) {}
    }
}
