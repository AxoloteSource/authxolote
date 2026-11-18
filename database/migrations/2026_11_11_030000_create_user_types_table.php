<?php

use App\Enums\UserTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('key')->unique();
            $table->timestamps();
        });

        DB::table('user_types')->insert([
            [
                'id' => UserTypeEnum::Human->value,
                'name' => 'Human',
                'key' => 'human',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => UserTypeEnum::App->value,
                'name' => 'App',
                'key' => 'app',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_types');
    }
};
