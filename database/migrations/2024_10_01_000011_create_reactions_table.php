// database/migrations/xxxx_create_reactions_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['like', 'love', 'haha', 'insightful', 'wow', 'sad', 'angry']);
            $table->timestamps();
            
            $table->unique(['user_id', 'article_id']);
        });
    }
};