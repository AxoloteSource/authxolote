<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('action_role_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('action_id')->constrained();
            $table->foreignUuid('role_id')->constrained();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['action_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_role_overrides');
    }
};
