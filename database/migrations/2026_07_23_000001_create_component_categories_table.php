<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('component_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 30);
            $table->string('name', 80);
            $table->string('slug', 80);
            $table->timestamps();

            $table->unique(['type', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_categories');
    }
};
