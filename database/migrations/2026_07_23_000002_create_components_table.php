<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('components', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignId('component_category_id')->constrained()->restrictOnDelete();
            $table->string('name', 160);
            $table->string('manufacturer', 120)->nullable();
            $table->string('reference', 120)->nullable();
            $table->text('description')->nullable();
            $table->json('specs')->nullable();
            $table->string('datasheet_disk', 20)->nullable();
            $table->string('datasheet_path', 500)->nullable();
            $table->string('datasheet_original_name', 180)->nullable();
            $table->unsignedInteger('price_cents')->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('supplier_url', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['component_category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
