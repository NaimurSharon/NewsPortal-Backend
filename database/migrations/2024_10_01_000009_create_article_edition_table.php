<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('article_edition', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->foreignId('edition_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['article_id', 'edition_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('article_edition');
    }
};
