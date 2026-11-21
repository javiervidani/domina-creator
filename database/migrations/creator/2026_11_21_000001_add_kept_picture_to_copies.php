<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A small kept copy of the post's picture (step 14). Null while the picture is only linked.
        Schema::table('content_copies', function (Blueprint $table) {
            $table->string('kept_path')->nullable();
            $table->unsignedInteger('kept_bytes')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('content_copies', fn (Blueprint $table) => $table->dropColumn(['kept_path', 'kept_bytes']));
    }
};
