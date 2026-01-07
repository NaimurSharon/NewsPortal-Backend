<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('article_link')->nullable();
            $table->string('type'); // e.g., factual error, bias, etc.
            $table->text('description');
            $table->enum('status', ['new', 'under_review', 'resolved', 'rejected'])->default('new');
            $table->text('resolution_note')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
