// database/migrations/xxxx_create_tags_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->enum('type', ['topic', 'location', 'person', 'organization', 'event'])->default('topic');
            $table->integer('usage_count')->default(0);
            $table->boolean('is_trending')->default(false);
            $table->timestamps();
        });
    }
};