<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_line_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();

            $table->string('description');
            $table->integer('quantity');
            $table->bigInteger('unit_amount');
            $table->bigInteger('subtotal');
            $table->bigInteger('tax_amount');
            $table->bigInteger('total');
            $table->string('currency', 3);

            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_line_items');
    }
};
