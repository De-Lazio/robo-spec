<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table): void {
            $table->string('folder', 120)->nullable()->after('category');
            $table->index(['project_id', 'category', 'folder']);
        });
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table): void {
            $table->dropIndex(['project_id', 'category', 'folder']);
            $table->dropColumn('folder');
        });
    }
};
