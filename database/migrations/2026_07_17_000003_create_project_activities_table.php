<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignUlid('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->string('subject_type')->nullable();
            $table->string('subject_id', 26)->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['project_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_activities');
    }
};
