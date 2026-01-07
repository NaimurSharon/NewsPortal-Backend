// database/migrations/xxxx_create_media_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('path');
            $table->string('url');
            $table->string('mime_type');
            $table->integer('size'); // in bytes
            $table->enum('type', ['image', 'video', 'audio', 'document'])->default('image');
            $table->string('caption')->nullable();
            $table->string('credit')->nullable();
            $table->json('dimensions')->nullable(); // For images: {"width": 1200, "height": 800}
            $table->json('variants')->nullable(); // Different sizes/thumbnails
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }
};