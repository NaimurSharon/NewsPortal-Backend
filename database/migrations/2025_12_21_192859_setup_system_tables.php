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
        // 1. Site Settings
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->enum('type', ['string','integer','boolean','json','array'])->default('string');
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        // 2. Cache (Standard Laravel)
        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
        }

        // 3. Notifications (Standard Laravel)
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Notification Templates
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subject');
            $table->text('content');
            $table->enum('type', ['email','push','sms','in_app'])->default('email');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Analytics Events
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type');
            $table->string('event_name');
            $table->string('page_url', 500);
            $table->string('referrer_url', 500)->nullable();
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('duration')->nullable(); // seconds
            $table->integer('scroll_depth')->nullable(); // percentage
            $table->json('device_info')->nullable();
            $table->json('location_info')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('session_id');
            $table->index(['event_type', 'created_at']);
        });

        // 6. Daily Metrics
        Schema::create('daily_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('metric_type');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->decimal('value', 15, 4);
            $table->timestamp('created_at')->useCurrent();
            
            $table->unique(['date', 'metric_type', 'entity_type', 'entity_id']);
            $table->index('date');
        });

        // 7. Content Recommendations
        Schema::create('content_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 4);
            $table->string('algorithm');
            $table->json('context')->nullable();
            $table->timestamp('shown_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();
        });

        // 8. Social Shares
        Schema::create('social_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['facebook','twitter','linkedin','whatsapp','telegram','email','other']);
            $table->foreignId('shared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 9. Search Logs
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('results_count')->default(0);
            $table->json('filters')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 10. Search Index
        Schema::create('search_index', function (Blueprint $table) {
            $table->id();
            $table->string('searchable_type');
            $table->unsignedBigInteger('searchable_id');
            $table->longText('content');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['searchable_type', 'searchable_id']);
            // Fulltext index usually requires raw SQL for universality, but Laravel supports it in newer versions
            // We'll add it raw to be safe/explicit if using MySQL
            $table->fullText('content'); 
        });

        // 11. API Keys
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key', 64)->unique();
            $table->string('secret', 64)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('permissions')->nullable();
            $table->integer('rate_limit')->default(60);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 12. Webhooks
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url', 500);
            $table->json('events');
            $table->string('secret', 64)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('last_response_status')->nullable();
            $table->text('last_response_body')->nullable();
            $table->timestamps();
        });

        // 13. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('tags')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('search_index');
        Schema::dropIfExists('search_logs');
        Schema::dropIfExists('social_shares');
        Schema::dropIfExists('content_recommendations');
        Schema::dropIfExists('daily_metrics');
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('notification_templates');
        // Notifications and Cache are conditional, but we can try dropping them or leaving them
        Schema::dropIfExists('site_settings');
    }
};
