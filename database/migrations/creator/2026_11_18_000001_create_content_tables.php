<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per piece of content: this id is the one platform ID.
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('master_text');
            $table->string('status')->default('imported');
            $table->boolean('source_changed')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        // The linked copy on each network. No foreign key to connected_accounts: disconnecting an account keeps history.
        Schema::create('content_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->unsignedBigInteger('connected_account_id')->nullable()->index();
            $table->string('platform');
            $table->string('external_id');
            $table->string('permalink')->nullable();
            $table->string('media_type')->nullable();
            $table->timestamp('remote_published_at')->nullable();
            $table->text('source_text');
            $table->string('source_hash', 40);
            $table->string('match_hash', 40)->index();
            $table->boolean('is_origin')->default(false); // only the origin copy can flag source_changed
            $table->string('link_mode')->default('origin'); // origin | auto
            $table->timestamps();
            $table->unique(['platform', 'external_id']);
        });

        Schema::table('sync_runs', function (Blueprint $table) {
            $table->unsignedInteger('created')->default(0);
            $table->unsignedInteger('changed')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('sync_runs', fn (Blueprint $table) => $table->dropColumn(['created', 'changed']));
        Schema::dropIfExists('content_copies');
        Schema::dropIfExists('contents');
    }
};
