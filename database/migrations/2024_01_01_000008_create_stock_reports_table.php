<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('report_date');
            $table->unsignedInteger('stock_in')->default(0);
            $table->unsignedInteger('stock_out')->default(0);
            $table->unsignedInteger('sold')->default(0);
            $table->unsignedInteger('damaged')->default(0);
            $table->unsignedInteger('remaining')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reports');
    }
};
