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
        // 1. Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->json('permissions');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // 2. Update Users
        Schema::table('users', function (Blueprint $table) {
            $table->string('title')->nullable()->after('name');
            $table->string('phone', 20)->nullable();
            $table->string('location')->nullable();
            $table->string('website')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('instagram_handle')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->integer('login_count')->default(0);
            $table->json('notification_preferences')->nullable();
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->json('backup_codes')->nullable();
            
            $table->index(['role', 'is_active']);
            $table->index('is_staff');
        });

        // 3. User Roles
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->unique(['user_id', 'role_id']);
        });

        // 4. User Activities
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('activity_type');
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('user_id');
            $table->index(['activity_type', 'created_at']);
        });

        // 5. User Preferences
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('preference_type');
            $table->json('preference_value');
            $table->timestamps();
            
            $table->unique(['user_id', 'preference_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('user_roles');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'phone', 'location', 'website', 'linkedin_url', 
                'instagram_handle', 'last_login_at', 'login_count', 
                'notification_preferences', 'email_notifications', 
                'push_notifications', 'two_factor_enabled', 
                'two_factor_secret', 'backup_codes'
            ]);
            $table->dropIndex(['role', 'is_active']);
            $table->dropIndex(['is_staff']);
        });

        Schema::dropIfExists('roles');
    }
};
