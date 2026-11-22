<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Posts the creator marked as "my voice": the source of the style guide and of the example posts.
        Schema::table('contents', fn (Blueprint $table) => $table->boolean('my_voice')->default(false));

        // Saved instructions ("My usual rewrite"). The style guide itself lives in `settings` (style_guide, style_guide_draft).
        Schema::create('ai_instructions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->text('text');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::table('ai_suggestions', function (Blueprint $table) {
            $table->text('instruction')->nullable();    // the creator's instruction used for this suggestion
            $table->boolean('use_style')->default(false);
            $table->string('style_note', 80)->nullable(); // for example "guide + 3 examples", shown in the panel
        });
    }

    public function down(): void
    {
        Schema::table('ai_suggestions', fn (Blueprint $table) => $table->dropColumn(['instruction', 'use_style', 'style_note']));
        Schema::dropIfExists('ai_instructions');
        Schema::table('contents', fn (Blueprint $table) => $table->dropColumn('my_voice'));
    }
};
