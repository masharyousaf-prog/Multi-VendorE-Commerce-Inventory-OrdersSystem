<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 1. Try to find an existing Vendor
        $vendor = \App\Models\User::where('role', 'vendor')->inRandomOrder()->first();

        // 2. If NO vendor exists, CREATE one automatically
        if (!$vendor) {
            $vendor = \App\Models\User::factory()->create([
                'name' => 'Demo Vendor',
                'email' => 'vendor_' . uniqid() . '@example.com',
                'role' => 'vendor',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            ]);
        }

        return [
            'user_id'     => $vendor->id, // Now guaranteed to exist
            'name'        => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(3),
            'price'       => $this->faker->randomFloat(2, 10, 500),
            'stock'       => $this->faker->numberBetween(0, 100),
            'image'       => "https://picsum.photos/seed/" . $this->faker->uuid . "/400/300",
            'discount'    => $this->faker->numberBetween(0, 30),
        ];
    }
}
