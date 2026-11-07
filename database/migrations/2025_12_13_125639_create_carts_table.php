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
        // 1. Create the primary 'carts' table
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            // Link the cart to a user (if logged in). Nullable if guests can have carts.
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained() // Assumes a 'users' table exists
                  ->onDelete('cascade');

            // Unique identifier for guest carts (e.g., a session ID or cookie token)
            // You might use this instead of a session for persistence/flexibility
            $table->string('session_token')->unique()->nullable();

            // Status of the cart (e.g., 'active', 'checkout', 'purchased')
            $table->enum('status', ['active', 'checkout', 'purchased'])->default('active');

            // Optional: Store the subtotal or total on the cart for quick retrieval
            $table->decimal('subtotal', 10, 2)->default(0.00);

            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Then drop the parent table
        Schema::dropIfExists('carts');
    }
};
