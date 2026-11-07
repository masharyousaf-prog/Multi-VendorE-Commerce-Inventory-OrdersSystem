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
    // 1. The Order Receipt (Date, User, Total)
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->decimal('total_amount', 10, 2);
        $table->timestamps(); // Created_at = Date of Purchase
    });

    // 2. The Line Items (Product Name, Price, Quantity)
    Schema::create('order_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
        $table->string('product_name'); // Save name (in case product is deleted later)
        $table->decimal('price', 10, 2); // The price PAID (after discount)
        $table->integer('quantity');
        $table->integer('discount_applied'); // Store the discount % used
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_tables');
    }
};
