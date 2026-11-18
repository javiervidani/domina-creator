<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Links only. We never download or store the file; the address is the network's and can expire.
        Schema::table('content_copies', function (Blueprint $table) {
            $table->text('media_url')->nullable();
            $table->text('thumbnail_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('content_copies', fn (Blueprint $table) => $table->dropColumn(['media_url', 'thumbnail_url']));
    }
};
