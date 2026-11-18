<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });

        // One row per provider. The key is encrypted by the model cast and is never exported (see DATABASE.md).
        Schema::create('ai_keys', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique();
            $table->text('api_key');
            $table->string('default_model')->nullable();
            $table->boolean('enabled')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamp('last_tested_at')->nullable();
            $table->boolean('last_test_ok')->nullable();
            $table->string('last_test_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_keys');
        Schema::dropIfExists('settings');
    }
};
