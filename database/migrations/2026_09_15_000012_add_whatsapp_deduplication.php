<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_notifications', function (Blueprint $table) {
            $table->string('message_hash', 64)->nullable()->after('message');
            $table->index('message_hash');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_notifications', fn (Blueprint $table) => $table->dropColumn('message_hash'));
    }
};
