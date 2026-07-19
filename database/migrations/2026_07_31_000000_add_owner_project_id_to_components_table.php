<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('components', function (Blueprint $table): void {
            $table->foreignUlid('owner_project_id')->nullable()->after('id')->constrained('projects')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('components', function (Blueprint $table): void {
            $table->dropForeign(['owner_project_id']);
            $table->dropColumn('owner_project_id');
        });
    }
};
