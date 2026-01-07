<?php

use App\Models\AdPlacement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $placements = [
            [
                'code' => 'article_sidebar_top',
                'name' => 'Article Sidebar Top',
                'description' => 'Top placement in article sidebar',
                'width' => 300,
                'height' => 250,
            ],
            [
                'code' => 'article_sidebar_bottom',
                'name' => 'Article Sidebar Bottom',
                'description' => 'Bottom placement in article sidebar',
                'width' => 300,
                'height' => 600,
            ],
        ];

        foreach ($placements as $p) {
            AdPlacement::firstOrCreate(
                ['code' => $p['code']],
                [
                    'name' => $p['name'],
                    'description' => $p['description'],
                    'width' => $p['width'],
                    'height' => $p['height'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        AdPlacement::whereIn('code', ['article_sidebar_top', 'article_sidebar_bottom'])->delete();
    }
};
