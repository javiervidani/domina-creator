<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('creator_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('creators')->cascadeOnDelete();
            $table->string('role')->default('owner');
            $table->timestamps();
            $table->unique(['user_id', 'creator_id']);
        });

        // One row per creator: where their data lives and whether it is being deleted (see DATABASE.md).
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->unique()->constrained('creators')->cascadeOnDelete();
            $table->string('schema_name')->unique();
            $table->string('status')->default('active');
            $table->timestamp('purge_at')->nullable();
            $table->timestamps();
        });

        Schema::create('deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creator_id');
            $table->unsignedBigInteger('requested_by');
            $table->timestamp('purge_at');
            $table->timestamp('restored_at')->nullable();
            $table->timestamp('purged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deletion_requests');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('creator_members');
        Schema::dropIfExists('creators');
    }
};
