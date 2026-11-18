<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A row exists only when a target has its own text; no row means "same as master text".
        Schema::create('content_target_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->string('target'); // youtube | telegram
            $table->text('text');
            $table->timestamps();
            $table->unique(['content_id', 'target']);
        });

        // History. Edits made close together are merged into one entry (see VersionRecorder).
        Schema::create('content_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->string('target')->nullable(); // null = master text
            $table->string('note');
            $table->string('title')->nullable();
            $table->text('text');
            $table->timestamps();
            $table->index(['content_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_versions');
        Schema::dropIfExists('content_target_texts');
    }
};
