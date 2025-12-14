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
        // 2. Create the 'cart_items' table
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            // Foreign key linking the item to its cart
            $table->foreignId('cart_id')
                  ->constrained() // Links to the 'carts' table
                  ->onDelete('cascade'); // If the cart is deleted, delete its items

            // Foreign key linking the item to a product (or product variant)
            // Assumes a 'products' table exists
            $table->foreignId('product_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Quantity of the product in the cart
            $table->integer('quantity')->default(1);


            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
