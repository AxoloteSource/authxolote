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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('user_type_id')
                ->nullable()
                ->after('role_id')
                ->constrained('user_types')
                ->restrictOnDelete();
        });

        DB::table('users')
            ->whereNull('user_type_id')
            ->update(['user_type_id' => UserTypeEnum::Human->value]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['user_type_id']);
            $table->dropColumn('user_type_id');
        });
    }
};
