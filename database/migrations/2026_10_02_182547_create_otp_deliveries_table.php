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
        Schema::create('otp_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('otp_challenge_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('channel');
            $table->string('destination');
            $table->string('status')
                ->default('pending');
            $table->string('provider')
                ->nullable();
            $table->timestamp('sent_at')
                ->nullable();
            $table->timestamp('failed_at')
                ->nullable();
            $table->text('error')
                ->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('otp_challenge_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_deliveries');
    }
};
