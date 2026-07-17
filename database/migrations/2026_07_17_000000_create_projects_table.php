<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('robot_type', 32);
            $table->string('domain')->nullable();
            $table->string('status', 32)->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamp('archived_at')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
