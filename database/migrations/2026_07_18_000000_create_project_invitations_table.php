<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignUlid('project_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('role', 32);
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('invited_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_invitations');
    }
};
