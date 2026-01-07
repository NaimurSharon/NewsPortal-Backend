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
        Schema::table('newsletter_subscriptions', function (Blueprint $table) {
            $table->string('email')->unique()->after('id');
            $table->foreignId('user_id')->nullable()->change();
            $table->foreignId('newsletter_id')->nullable()->change();
            $table->boolean('is_active')->default(true)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletter_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['email', 'is_active']);
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreignId('newsletter_id')->nullable(false)->change();
        });
    }
};
