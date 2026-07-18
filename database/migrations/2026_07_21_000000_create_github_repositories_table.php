<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('github_repositories', function (Blueprint $table): void {
            $table->id();
            $table->foreignUlid('project_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 20)->default('github');
            $table->string('owner', 120);
            $table->string('repository', 120);
            $table->string('url', 255);
            $table->string('default_branch', 120)->nullable();
            $table->string('visibility', 20)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_status', 20);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_repositories');
    }
};
