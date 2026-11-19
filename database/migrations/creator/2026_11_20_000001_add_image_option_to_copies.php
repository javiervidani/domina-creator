<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_copies', function (Blueprint $table) {
            $table->boolean('send_image')->default(true); // the creator's choice, per target copy
            $table->string('image_mode', 10)->nullable(); // how the first send went: caption | separate | null (no picture)
        });
    }

    public function down(): void
    {
        Schema::table('content_copies', fn (Blueprint $table) => $table->dropColumn(['send_image', 'image_mode']));
    }
};
