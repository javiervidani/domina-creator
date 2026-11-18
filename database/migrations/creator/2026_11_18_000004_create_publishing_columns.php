<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A target copy is a content_copies row too. status: source | draft | queued | published | failed.
        Schema::table('content_copies', function (Blueprint $table) {
            $table->string('status')->default('source');
            $table->string('published_hash', 40)->nullable(); // hash of the text last sent, to show when the copy differs
            $table->timestamp('published_at')->nullable();
            $table->text('last_error')->nullable();
        });

        Schema::create('publish_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_copy_id')->constrained('content_copies')->cascadeOnDelete();
            $table->string('kind'); // publish | republish
            $table->string('status'); // running | sent | failed
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publish_jobs');
        Schema::table('content_copies', fn (Blueprint $table) => $table->dropColumn(['status', 'published_hash', 'published_at', 'last_error']));
    }
};
