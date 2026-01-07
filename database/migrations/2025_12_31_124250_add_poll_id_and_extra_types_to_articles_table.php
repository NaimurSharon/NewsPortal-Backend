<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('poll_id')->nullable()->constrained()->nullOnDelete();
            
            // Adding scheduled status and new types if possible or via raw
            // Since enum modification is tricky, we add it here if supported or just add the col for poll
        });
        
        // Raw SQL to update enums for MySQL
        try {
            DB::statement("ALTER TABLE articles MODIFY COLUMN type ENUM('news', 'opinion', 'editorial', 'feature', 'special_report', 'sport', 'lifestyle', 'video', 'live') DEFAULT 'news'");
            DB::statement("ALTER TABLE articles MODIFY COLUMN status ENUM('draft', 'review', 'published', 'archived', 'scheduled') DEFAULT 'draft'");
        } catch (\Exception $e) {
            // Fallback or ignore if not MySQL
        }
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('poll_id');
        });
    }
};
