<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The model names this key can use, as the provider listed them when the key was last tested.
        Schema::table('ai_keys', function (Blueprint $table) {
            $table->text('models')->nullable();
            $table->timestamp('models_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ai_keys', fn (Blueprint $table) => $table->dropColumn(['models', 'models_at']));
    }
};
