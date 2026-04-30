<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('post_count')->default(0);
            $table->timestamps();
        });

        Schema::create('post_hashtag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hashtag_id')->constrained()->cascadeOnDelete();
            $table->primary(['post_id', 'hashtag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_hashtag');
        Schema::dropIfExists('hashtags');
    }
};
