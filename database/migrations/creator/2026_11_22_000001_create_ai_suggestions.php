<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // What the AI offered for one tab of one post. Never applied by itself. No text of ours leaves the schema.
        Schema::create('ai_suggestions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_id')->index();
            $table->string('target', 20);   // master, telegram, youtube
            $table->string('action', 10);   // rewrite | shorten
            $table->string('provider', 20);
            $table->string('model', 80);
            $table->string('status', 10)->default('pending'); // pending | offered | applied | dismissed | failed
            $table->text('offered_text')->nullable();
            $table->string('error', 300)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_suggestions');
    }
};
