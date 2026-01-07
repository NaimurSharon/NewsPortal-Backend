// database/migrations/2024_10_01_000003_create_articles_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->foreignId('series_id')->nullable()->constrained('series')->onDelete('set null');
            $table->enum('format', ['standard', 'video', 'audio', 'gallery', 'live'])->default('standard');
            $table->enum('type', ['news', 'opinion', 'editorial', 'feature', 'special_report', 'sport', 'lifestyle'])->default('news');
            $table->enum('status', ['draft', 'review', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->integer('reading_time')->nullable(); // in minutes
            $table->string('featured_image_url')->nullable();
            $table->string('featured_image_caption')->nullable();
            $table->string('featured_image_credit')->nullable();
            $table->json('gallery_images')->nullable(); // For photo galleries
            $table->string('video_url')->nullable();
            $table->string('audio_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_exclusive')->default(false);
            $table->boolean('is_live')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->integer('view_count')->default(0);
            $table->integer('share_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'published_at']);
            $table->index(['section_id', 'published_at']);
            $table->index(['author_id', 'published_at']);
            $table->index(['series_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('articles');
    }
};
