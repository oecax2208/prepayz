<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersFaspayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_faspay', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->json('cart');
            $table->string('payment_method');
            $table->string('currency_sign');
            $table->decimal('tax', 8, 2);
            $table->string('transaction_number');
            $table->string('order_status');
            $table->json('shipping_info');
            $table->json('billing_info');
            $table->string('payment_status');
            $table->json('billing_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
