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
            $table->unsignedBigInteger('addon_service_id');
            $table->date('date');
            $table->string('time'); // if you need to store time separately
            $table->unsignedBigInteger('city_id');
            $table->string('address');
            $table->integer('count');
            $table->decimal('total_price')->nullable();

            $table->string('payment_type')->nullable();
            $table->integer('status')->comment('App\Enums\OrderStatus')->default(OrderStatus::pending->value);
            $table->string('otp')->nullable();
            $table->timestamp('validated_at')->nullable(); // <-- added this line
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('addon_service_id')->references('id')->on('addon_services')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities');
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
