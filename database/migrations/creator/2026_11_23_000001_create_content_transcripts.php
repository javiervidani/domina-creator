<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The transcript of one video, kept apart from the master text so nothing the creator wrote is touched.
        Schema::create('content_transcripts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_id')->index();
            $table->unsignedBigInteger('content_copy_id')->unique();
            $table->string('language', 12)->nullable();
            $table->string('kind', 10); // standard (uploaded captions) | asr (automatic) | stt (speech to text, later)
            $table->text('text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_transcripts');
    }
};
