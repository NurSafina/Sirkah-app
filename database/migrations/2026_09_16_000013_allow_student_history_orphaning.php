<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('balance_mutations', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreignId('student_id')->nullable()->change();
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
        });
        Schema::table('whatsapp_notifications', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreignId('student_id')->nullable()->change();
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('whatsapp_notifications', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreignId('student_id')->nullable(false)->change();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
        });
        Schema::table('balance_mutations', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreignId('student_id')->nullable(false)->change();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
        });
    }
};
