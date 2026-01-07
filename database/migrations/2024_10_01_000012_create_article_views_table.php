// database/migrations/xxxx_create_article_views_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('article_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('country')->nullable();
            $table->string('device_type')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            
            $table->index(['article_id', 'viewed_at']);
            $table->index(['session_id', 'viewed_at']);
        });
    }
};