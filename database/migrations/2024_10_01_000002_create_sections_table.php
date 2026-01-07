// database/migrations/xxxx_create_sections_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // News, Sport, Culture, etc.
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->integer('parent_id')->nullable()->index();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('icon')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }
};