<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connected_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('platform');
            $table->string('external_id');
            $table->string('handle');
            $table->string('status')->default('ok'); // ok | reconnect
            $table->boolean('is_source_of_truth')->default(false);
            $table->boolean('import_enabled')->default(true);
            $table->string('import_frequency')->default('hourly');
            $table->timestamp('last_sync_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->unique(['platform', 'external_id']);
        });

        // Values are encrypted by the model cast and are never exported.
        Schema::create('account_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connected_account_id')->unique()->constrained('connected_accounts')->cascadeOnDelete();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('refreshed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('account_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_account_id')->constrained('connected_accounts')->cascadeOnDelete();
            $table->foreignId('target_account_id')->constrained('connected_accounts')->cascadeOnDelete();
            $table->string('mode')->default('review'); // review first is the default
            $table->string('what_to_send')->default('text');
            $table->timestamps();
            $table->unique(['source_account_id', 'target_account_id']);
        });

        Schema::create('sync_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connected_account_id')->constrained('connected_accounts')->cascadeOnDelete();
            $table->string('kind'); // sync | token_refresh
            $table->string('status'); // running | ok | failed
            $table->unsignedInteger('fetched')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_runs');
        Schema::dropIfExists('account_targets');
        Schema::dropIfExists('account_tokens');
        Schema::dropIfExists('connected_accounts');
    }
};
