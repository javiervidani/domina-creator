<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Collections go to the Recycle bin like posts do: deleted_at, then a purge after 60 days.
        Schema::table('collections', fn (Blueprint $table) => $table->softDeletes());
    }

    public function down(): void
    {
        Schema::table('collections', fn (Blueprint $table) => $table->dropSoftDeletes());
    }
};
