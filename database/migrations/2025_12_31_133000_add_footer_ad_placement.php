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
        AdPlacement::firstOrCreate(
            ['code' => 'footer'],
            [
                'name' => 'Footer Banner',
                'description' => 'Ad placement for bottom of the page',
                'width' => 970,
                'height' => 90,
                'is_active' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        AdPlacement::where('code', 'footer')->delete();
    }
};
