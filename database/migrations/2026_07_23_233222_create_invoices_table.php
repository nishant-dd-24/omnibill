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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('customer_id')->index();
            $table->uuid('subscription_id')->nullable()->index();

            $table->string('number')->index();
            $table->string('status')->index();
            $table->string('currency', 3);

            $table->bigInteger('subtotal');
            $table->bigInteger('tax_total');
            $table->bigInteger('total');
            $table->bigInteger('amount_paid')->default(0);
            $table->bigInteger('amount_due');

            $table->timestamp('due_date')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('voided_at')->nullable();

            $table->string('pdf_url')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
