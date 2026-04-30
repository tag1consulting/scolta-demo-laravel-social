<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('display_name');
            $table->string('username')->unique();
            $table->text('bio')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamp('joined_at');
            $table->string('archetype');
            $table->string('posting_frequency')->default('moderate'); // power, moderate, casual
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
