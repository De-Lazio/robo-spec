<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table): void {
            $table->foreignUlid('algorithm_diagram_id')->nullable()->after('project_id')->constrained('algorithm_diagrams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table): void {
            $table->dropForeign(['algorithm_diagram_id']);
            $table->dropColumn('algorithm_diagram_id');
        });
    }
};
