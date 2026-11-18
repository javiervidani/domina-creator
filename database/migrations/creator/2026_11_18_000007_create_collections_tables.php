<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('cover_url')->nullable(); // a link, never a stored file
            $table->string('visibility')->default('private'); // private | link | public
            $table->timestamps();
        });

        Schema::create('collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->unique(['collection_id', 'content_id']);
        });

        Schema::create('embed_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->unique()->constrained('collections')->cascadeOnDelete();
            $table->string('layout')->default('list'); // list | grid
            $table->string('accent', 7)->default('#0b57d0');
            $table->boolean('show_media')->default(true);
            $table->text('allowed_domains')->nullable(); // one host per line; empty means nobody may frame it
            $table->timestamps();
        });

        // The token is stored encrypted so the embed code can be shown again, and hashed so a request can find it.
        Schema::create('feed_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->text('token');
            $table->string('token_hash', 64)->unique();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_tokens');
        Schema::dropIfExists('embed_settings');
        Schema::dropIfExists('collection_items');
        Schema::dropIfExists('collections');
    }
};
