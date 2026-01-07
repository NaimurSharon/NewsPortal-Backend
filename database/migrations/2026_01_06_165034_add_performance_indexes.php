<?php

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
        // Articles table - critical indexes for performance
        Schema::table('articles', function (Blueprint $table) {
            // Composite index for published articles queries
            $table->index(['status', 'published_at'], 'idx_status_published');
            
            // Section-based queries
            $table->index(['section_id', 'published_at'], 'idx_section_published');
            
            // Featured articles queries
            $table->index(['is_featured', 'published_at'], 'idx_featured_published');
            
            // Ensure foreign keys are indexed
            if (!Schema::hasIndex('articles', ['section_id'])) {
                $table->index('section_id');
            }
            if (!Schema::hasIndex('articles', ['author_id'])) {
                $table->index('author_id');
            }
            if (!Schema::hasIndex('articles', ['series_id'])) {
                $table->index('series_id');
            }
            if (!Schema::hasIndex('articles', ['poll_id'])) {
                $table->index('poll_id');
            }
        });

        // Article-Edition pivot table
        Schema::table('article_edition', function (Blueprint $table) {
            if (!Schema::hasIndex('article_edition', ['edition_id', 'article_id'])) {
                $table->index(['edition_id', 'article_id'], 'idx_edition_article');
            }
        });

        // Article-Author pivot table
        Schema::table('article_author', function (Blueprint $table) {
            if (!Schema::hasIndex('article_author', ['article_id', 'order'])) {
                $table->index(['article_id', 'order'], 'idx_article_author_order');
            }
        });

        // Sections table
        Schema::table('sections', function (Blueprint $table) {
            if (!Schema::hasIndex('sections', ['parent_id', 'is_active', 'order'])) {
                $table->index(['parent_id', 'is_active', 'order'], 'idx_parent_active_order');
            }
        });

        // Article tags pivot
        Schema::table('article_tag', function (Blueprint $table) {
            if (!Schema::hasIndex('article_tag', ['article_id'])) {
                $table->index('article_id');
            }
            if (!Schema::hasIndex('article_tag', ['tag_id'])) {
                $table->index('tag_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex('idx_status_published');
            $table->dropIndex('idx_section_published');
            $table->dropIndex('idx_featured_published');
        });

        Schema::table('article_edition', function (Blueprint $table) {
            $table->dropIndex('idx_edition_article');
        });

        Schema::table('article_author', function (Blueprint $table) {
            $table->dropIndex('idx_article_author_order');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropIndex('idx_parent_active_order');
        });
    }
};
