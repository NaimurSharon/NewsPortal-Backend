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
        // 1. Redirects (Crucial for SEO)
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('old_url', 500);
            $table->string('new_url', 500);
            $table->integer('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('old_url');
        });

        // 2. Navigation Menus
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // e.g., 'primary', 'footer', 'sidebar-quick-links'
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->integer('parent_id')->nullable()->default(0); // For nested menus
            $table->string('title');
            $table->string('url')->nullable();
            $table->string('target')->default('_self'); // _blank, _self
            $table->string('type')->default('custom'); // custom, page, category, post
            $table->unsignedBigInteger('related_id')->nullable(); // ID of the related model
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->json('display_conditions')->nullable(); // e.g., only for logged_in users
            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'order']);
        });

        // 3. User Following (Topics/Authors)
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('followable_type');
            $table->unsignedBigInteger('followable_id');
            $table->timestamp('created_at')->useCurrent();
            
            $table->unique(['user_id', 'followable_type', 'followable_id']);
            $table->index(['followable_type', 'followable_id']);
        });

        // 4. Polls & Surveys
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('description')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_multiple_choice')->default(false);
            $table->boolean('requires_login')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->integer('votes_count')->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('poll_options')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Prevent duplicate votes: either by user_id OR ip_address+poll_id
            $table->index(['poll_id', 'user_id']); 
            $table->index(['poll_id', 'ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
        Schema::dropIfExists('follows');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('redirects');
    }
};
