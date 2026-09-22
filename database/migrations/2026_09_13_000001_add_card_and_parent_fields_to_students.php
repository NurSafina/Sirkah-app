<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('parent_name')->nullable()->after('name');
            $table->string('parent_phone', 30)->nullable()->after('parent_name');
            $table->string('card_token', 64)->nullable()->unique()->after('parent_phone');
            $table->enum('card_status', ['active', 'inactive'])->default('active')->after('card_token');
        });

        DB::table('students')->select('id')->orderBy('id')->each(function ($student) {
            DB::table('students')->where('id', $student->id)->update([
                'card_token' => (string) Str::uuid(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['card_token']);
            $table->dropColumn(['parent_name', 'parent_phone', 'card_token', 'card_status']);
        });
    }
};
