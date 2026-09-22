<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_reports', function (Blueprint $table) {
            $table->string('source')->nullable()->after('stock_in');
            $table->string('invoice_number')->nullable()->after('source');
            $table->decimal('cost_price', 12, 2)->nullable()->after('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::table('stock_reports', function (Blueprint $table) {
            $table->dropColumn(['source', 'invoice_number', 'cost_price']);
        });
    }
};
