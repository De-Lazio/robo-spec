<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('category', 20);
            $table->string('kind', 20);
            $table->string('name', 180);
            $table->string('original_name', 180);
            $table->string('disk', 20);
            $table->string('path', 500);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size_bytes');
            $table->text('description')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['project_id', 'category']);
            $table->index(['project_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
