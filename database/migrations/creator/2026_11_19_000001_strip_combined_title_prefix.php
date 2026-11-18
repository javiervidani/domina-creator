<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// One-off tidy: combined drafts made earlier carry an English "Combined: " prefix that breaks right-to-left rows.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE contents SET title = substr(title, 11) WHERE status = 'draft' AND title LIKE 'Combined: %'");
    }

    public function down(): void
    {
    }
};
