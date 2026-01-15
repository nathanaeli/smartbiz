<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // RELATIONSHIPS
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->onDelete('cascade');

            $table->foreignId('subscription_id')
                ->constrained('duka_subscriptions')
                ->onDelete('cascade');

            // PAYMENT DETAILS
            $table->string('payment_method')      // mpesa, airtel, mastercard, visa, stripe, paypal
                  ->nullable();

            $table->string('transaction_id')       // returned from provider
                  ->nullable();

            $table->string('provider_reference')   // provider ref (Mpesa: CheckoutRequestID, Stripe: charge_id)
                  ->nullable();

            $table->integer('amount');             // TZS

            $table->enum('currency', ['TZS', 'USD'])->default('TZS');

            // STATUS
            $table->enum('status', [
                'pending',     // created but not paid
                'processing',  // request sent to payment provider
                'paid',        // success
                'failed',      // transaction failed
                'cancelled',   // user cancelled
            ])->default('pending');

            // LOGGING
            $table->text('request_payload')->nullable();
            $table->text('response_payload')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
