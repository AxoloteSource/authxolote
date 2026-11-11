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
        Schema::create('otp_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('purpose');
            $table->string('channel');
            $table->string('destination');
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')
                ->nullable();
            $table->unsignedInteger('attempts')
                ->default(0);
            $table->unsignedInteger('max_attempts');
            $table->timestamps();

            $table->index(['destination', 'purpose', 'expires_at', 'verified_at'], 'otp_challenges_active_lookup');
            $table->index('purpose');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_challenges');
    }
};
