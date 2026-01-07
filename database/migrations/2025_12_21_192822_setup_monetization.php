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
        // 1. Advertisers
        Schema::create('advertisers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('website')->nullable();
            $table->string('contact_person')->nullable();
            $table->enum('status', ['active','inactive','pending'])->default('pending');
            $table->timestamps();
        });

        // 2. Ad Campaigns
        Schema::create('ad_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('advertiser_id')->nullable()->constrained('advertisers')->nullOnDelete();
            $table->enum('type', ['display','video','native','sponsored_content'])->default('display');
            $table->string('format');
            $table->string('target_url');
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('daily_budget', 15, 2)->nullable();
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['draft','pending','active','paused','completed','cancelled'])->default('draft');
            $table->json('targeting')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // 3. Ad Placements
        Schema::create('ad_placements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->enum('type', ['header','sidebar','footer','inline','popup','sticky'])->default('sidebar');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('max_ads')->default(1);
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();
        });

        // 4. Ad Units
        Schema::create('ad_units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->foreignId('placement_id')->constrained('ad_placements')->cascadeOnDelete();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->longText('html_content')->nullable();
            $table->integer('impressions_limit')->nullable();
            $table->integer('clicks_limit')->nullable();
            $table->integer('weight')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
        });

        // 5. Ad Stats
        Schema::create('ad_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_unit_id')->constrained('ad_units')->cascadeOnDelete();
            $table->date('date');
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('conversions')->default(0);
            $table->decimal('revenue', 15, 4)->default(0);
            $table->timestamps();
            
            $table->unique(['ad_unit_id', 'date']);
            $table->index('date');
        });

        // 6. Subscription Plans
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['free','trial','monthly','yearly','lifetime'])->default('monthly');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->integer('trial_days')->default(0);
            $table->json('features')->nullable();
            $table->json('limitations')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 7. User Subscriptions
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->string('stripe_subscription_id')->unique()->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->enum('status', ['active','past_due','unpaid','cancelled','expired'])->default('active');
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });

        // 8. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('user_subscriptions')->nullOnDelete();
            $table->string('stripe_payment_id')->unique()->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending','succeeded','failed','refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('ad_stats');
        Schema::dropIfExists('ad_units');
        Schema::dropIfExists('ad_placements');
        Schema::dropIfExists('ad_campaigns');
        Schema::dropIfExists('advertisers');
    }
};
