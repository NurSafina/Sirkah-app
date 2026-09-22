<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->decimal('daily_limit', 12, 2)->default(0)->after('balance');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode')->nullable()->unique()->after('sku');
            $table->string('unit')->default('pcs')->after('category');
            $table->unsignedInteger('minimum_stock')->default(5)->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('daily_limit');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['barcode']);
            $table->dropColumn(['barcode', 'unit', 'minimum_stock']);
        });
    }
};