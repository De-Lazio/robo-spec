<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('components', function (Blueprint $table): void {
            $table->string('image_url', 500)->nullable()->after('supplier_url');
            $table->string('image_disk', 20)->nullable()->after('image_url');
            $table->string('image_path', 500)->nullable()->after('image_disk');
            $table->string('image_original_name', 180)->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('components', function (Blueprint $table): void {
            $table->dropColumn(['image_url', 'image_disk', 'image_path', 'image_original_name']);
        });
    }
};
