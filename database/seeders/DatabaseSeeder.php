<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users (All Approved by Default)
        $admin = User::updateOrCreate(['email' => 'admin@nexamart.com'], [
            'name' => 'Executive Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $vendor1 = User::updateOrCreate(['email' => 'vendor@nexamart.com'], [
            'name' => 'Apex Tech Hub',
            'store_name' => 'Apex Global Electronics',
            'store_slug' => 'apex-global-electronics',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $customer = User::updateOrCreate(['email' => 'customer@nexamart.com'], [
            'name' => 'Verified Buyer',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        // 2. Categories
        $catData = [
            ['name' => 'Consumer Electronics', 'slug' => 'electronics', 'icon' => 'fa-microchip'],
            ['name' => 'EV & Solar Hardware', 'slug' => 'ev-solar', 'icon' => 'fa-solar-panel'],
            ['name' => 'Gaming & VR Gear', 'slug' => 'gaming', 'icon' => 'fa-gamepad'],
            ['name' => 'Smart Home Automation', 'slug' => 'smart-home', 'icon' => 'fa-house-signal'],
        ];

        $categories = collect($catData)->map(fn ($c) => Category::updateOrCreate(['slug' => $c['slug']], $c));

        // 3. Promo Coupon
        Coupon::updateOrCreate(['code' => 'MEGA20'], [
            'type' => 'percentage',
            'value' => 20,
            'min_order_amount' => 50,
            'is_active' => true,
        ]);

        // 4. Sample Flash Deal & Featured Products
        $products = [
            [
                'name' => 'Ultra-Pro 4K Spatial Gaming Headset',
                'brand' => 'ApexGear',
                'price' => 149.99,
                'compare_at_price' => 219.99,
                'stock' => 14,
                'is_featured' => true,
                'is_flash_deal' => true,
                'category_id' => $categories[2]->id,
            ],
            [
                'name' => 'Smart LiFePO4 24V 100Ah BMS Power Pack',
                'brand' => 'VoltCore',
                'price' => 489.00,
                'compare_at_price' => 599.00,
                'stock' => 8,
                'is_featured' => true,
                'is_flash_deal' => true,
                'category_id' => $categories[1]->id,
            ],
            [
                'name' => '6kW Hybrid MPPT Solar Inverter Wi-Fi',
                'brand' => 'SolarNex',
                'price' => 799.00,
                'compare_at_price' => 950.00,
                'stock' => 5,
                'is_featured' => true,
                'is_flash_deal' => false,
                'category_id' => $categories[1]->id,
            ],
            [
                'name' => 'Mechanical RGB Hot-Swap Keyboard 75%',
                'brand' => 'KeyChronX',
                'price' => 89.50,
                'compare_at_price' => 119.00,
                'stock' => 32,
                'is_featured' => true,
                'is_flash_deal' => true,
                'category_id' => $categories[0]->id,
            ],
        ];

        foreach ($products as $idx => $p) {
            $attributes = array_merge($p, [
                'vendor_id' => $vendor1->id,
                'sku' => 'NXM-SKU-10' . ($idx + 1),
                'description' => 'Production-grade hardware backed by 12-month warranty and instant dispatch.',
            ]);

            if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'user_id')) {
                $attributes['user_id'] = $vendor1->id;
            }

            $prod = Product::updateOrCreate(['name' => $p['name']], $attributes);

            Review::updateOrCreate(
                ['user_id' => $customer->id, 'product_id' => $prod->id],
                ['rating' => 5, 'title' => 'Outstanding build quality!', 'comment' => 'Fast delivery and genuine specs.', 'is_verified_purchase' => true]
            );
        }

        // 5. Seed 14-Day Order History for Multi-Graph Dashboards
        $allProducts = Product::all();
        foreach (range(13, 0) as $daysAgo) {
            $date = Carbon::today()->subDays($daysAgo)->addHours(rand(9, 20));
            $prod = $allProducts->random();
            $qty = rand(1, 3);

            $order = Order::create([
                'user_id' => $customer->id,
                'total_price' => $prod->price * $qty,
                'status' => collect(['completed', 'processing', 'pending'])->random(),
                'tracking_number' => 'NXM-' . strtoupper(Str::random(8)),
                'payment_method' => 'card',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $prod->id,
                'quantity' => $qty,
                'price' => $prod->price,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}
