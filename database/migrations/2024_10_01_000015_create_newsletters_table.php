// database/migrations/xxxx_create_newsletters_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('newsletters', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Morning Briefing, Weekly Digest, etc.
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'real_time']);
            $table->time('send_time')->nullable();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('subscriber_count')->default(0);
            $table->timestamps();
        });
    }
};