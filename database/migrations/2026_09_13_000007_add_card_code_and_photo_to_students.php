<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('card_code', 30)->nullable()->unique()->after('card_token');
            $table->string('photo_path')->nullable()->after('card_code');
        });

        DB::table('students')->select('id')->orderBy('id')->each(function ($student) {
            DB::table('students')->where('id', $student->id)->update([
                'card_code' => 'SIRKAH-' . str_pad((string) $student->id, 6, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['card_code']);
            $table->dropColumn(['card_code', 'photo_path']);
        });
    }
};
