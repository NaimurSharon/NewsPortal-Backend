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
        // 1. Workflow Steps
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('order')->default(0);
            $table->string('required_role')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Article Workflow
        Schema::create('article_workflow', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('step_id')->constrained('workflow_steps')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending','in_progress','completed','rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 3. Scheduled Content
        Schema::create('scheduled_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->timestamp('scheduled_for');
            $table->foreignId('scheduled_by')->constrained('users')->cascadeOnDelete();
            $table->enum('action', ['publish','unpublish','archive','feature']);
            $table->enum('status', ['pending','completed','failed','cancelled'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });

        // 4. Media Collections
        Schema::create('media_collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['gallery','carousel','featured','archive'])->default('gallery');
            $table->unsignedBigInteger('cover_media_id')->nullable(); // FK manually added later if needed or just loosely linked
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 5. Media Collection Items
        Schema::create('media_collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('media_collections')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->integer('order')->default(0);
            $table->text('caption')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['collection_id', 'media_id']);
        });

        // 6. Bookmarks
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->string('folder')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'article_id']);
        });

        // 7. Update Articles
        Schema::table('articles', function (Blueprint $table) {
            $table->string('language', 10)->default('en');
            $table->integer('word_count')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_edited_at')->nullable();
            $table->json('related_articles')->nullable();
            $table->json('content_versions')->nullable();
            $table->json('syndication')->nullable();
            $table->text('editorial_notes')->nullable();
            $table->enum('fact_check_status', ['pending','verified','disputed','corrected'])->default('pending');
            $table->text('fact_check_notes')->nullable();
            $table->boolean('content_lock')->default(false);
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            
            $table->index(['language', 'status', 'published_at']);
            $table->index(['is_featured', 'published_at']);
            $table->index(['type', 'section_id']);
        });

        // 8. Update Media
        Schema::table('media', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('source')->nullable();
            $table->string('copyright')->nullable();
            $table->string('license_type')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->integer('usage_count')->default(0);
            $table->json('tags')->nullable();
            
            $table->index(['type', 'uploaded_by']);
            $table->index(['is_approved', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('media_collection_items');
        Schema::dropIfExists('media_collections');
        Schema::dropIfExists('scheduled_content');
        Schema::dropIfExists('article_workflow');
        Schema::dropIfExists('workflow_steps');

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn([
                'language', 'word_count', 'seo_title', 'meta_description', 
                'canonical_url', 'last_edited_by', 'last_edited_at', 
                'related_articles', 'content_versions', 'syndication', 
                'editorial_notes', 'fact_check_status', 'fact_check_notes', 
                'content_lock', 'locked_by', 'locked_at'
            ]);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'alt_text', 'source', 'copyright', 
                'license_type', 'expires_at', 'is_approved', 
                'approved_by', 'approved_at', 'usage_count', 'tags'
            ]);
        });
    }
};
