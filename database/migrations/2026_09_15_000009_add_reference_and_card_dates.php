<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->timestamp('card_issued_at')->nullable()->after('card_code');
            $table->timestamp('card_revoked_at')->nullable()->after('card_issued_at');
        });

        Schema::table('balance_mutations', function (Blueprint $table) {
            $table->string('reference_number')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('balance_mutations', fn (Blueprint $table) => $table->dropColumn('reference_number'));
        Schema::table('students', fn (Blueprint $table) => $table->dropColumn(['card_issued_at', 'card_revoked_at']));
    }
};
