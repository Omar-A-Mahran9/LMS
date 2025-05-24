<?php

use App\Enums\OrderStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->date('date');
            $table->string('time');
            $table->unsignedBigInteger('city_id');
            $table->string('address');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->decimal('total_price')->nullable();
            $table->string('payment_id')->nullable();
            $table->integer('status')->comment('App\Enums\OrderStatus')->default(OrderStatus::pending->value);
            $table->string('otp')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->boolean('is_paid')->default(false);

            // $table->foreign('customer_id')->references('id')->on('customers');
            // $table->foreign('city_id')->references('id')->on('cities');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
