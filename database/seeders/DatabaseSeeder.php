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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $passwordHash = Hash::make('password');

        // 1. Wipe existing data to ensure clean slate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('reviews')->truncate();
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('products')->truncate();
        DB::table('wishlists')->truncate();
        DB::table('coupons')->truncate();
        DB::table('users')->truncate();
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Admin Account
        User::create([
            'name' => 'Executive Admin',
            'email' => 'admin@nexamart.com',
            'password' => $passwordHash,
            'role' => 'admin',
            'is_active' => true,
            'created_at' => $now->copy()->subMonths(5),
            'updated_at' => $now,
        ]);

        // 3. Seed 6 Vendors with Distinct Market Share Ratios
        $vendorSpecs = [
            ['email' => 'vendor@nexamart.com', 'name' => 'Apex Tech Hub', 'store_name' => 'Apex Global Electronics', 'share' => 140],
            ['email' => 'voltcore@nexamart.com', 'name' => 'VoltCore Energy', 'store_name' => 'VoltCore Solar & EV', 'share' => 105],
            ['email' => 'novagear@nexamart.com', 'name' => 'Nova Gaming', 'store_name' => 'NovaGear Esports Official', 'share' => 85],
            ['email' => 'lumina@nexamart.com', 'name' => 'Lumina Smart Home', 'store_name' => 'Lumina Living Systems', 'share' => 70],
            ['email' => 'titanpro@nexamart.com', 'name' => 'Titan Industrial', 'store_name' => 'TitanPro Tools & Auto', 'share' => 55],
            ['email' => 'urbanpulse@nexamart.com', 'name' => 'UrbanPulse Lifestyle', 'store_name' => 'UrbanPulse Studio', 'share' => 45],
        ];

        $vendors = [];
        foreach ($vendorSpecs as $vIdx => $v) {
            $vendors[] = User::create([
                'name' => $v['name'],
                'email' => $v['email'],
                'store_name' => $v['store_name'],
                'store_slug' => Str::slug($v['store_name']),
                'store_description' => 'Official verified storefront for ' . $v['store_name'],
                'commission_rate' => 10.00,
                'password' => $passwordHash,
                'role' => 'vendor',
                'is_active' => true,
                'created_at' => $now->copy()->subMonths(rand(1, 5))->addDays(rand(1, 10)),
                'updated_at' => $now,
            ]);
        }

        // 4. Seed 12 Customers with Varied Purchase Frequencies
        $customerSpecs = [
            ['email' => 'customer@nexamart.com', 'name' => 'Verified Buyer', 'weight' => 18],
            ['email' => 'ahmed.khan@example.com', 'name' => 'Ahmed Khan', 'weight' => 15],
            ['email' => 'sara.malik@example.com', 'name' => 'Sara Malik', 'weight' => 13],
            ['email' => 'usman.tariq@example.com', 'name' => 'Usman Tariq', 'weight' => 11],
            ['email' => 'zainab.ali@example.com', 'name' => 'Zainab Ali', 'weight' => 10],
            ['email' => 'bilal.hassan@example.com', 'name' => 'Bilal Hassan', 'weight' => 9],
            ['email' => 'fatima.noor@example.com', 'name' => 'Fatima Noor', 'weight' => 7],
            ['email' => 'hamza.sheikh@example.com', 'name' => 'Hamza Sheikh', 'weight' => 6],
            ['email' => 'ayesha.siddiqui@example.com', 'name' => 'Ayesha Siddiqui', 'weight' => 4],
            ['email' => 'omar.farooq@example.com', 'name' => 'Omar Farooq', 'weight' => 3],
            ['email' => 'hina.raza@example.com', 'name' => 'Hina Raza', 'weight' => 2],
            ['email' => 'danish.mehmood@example.com', 'name' => 'Danish Mehmood', 'weight' => 2],
        ];

        $customers = [];
        $weightedCustomerIds = [];
        foreach ($customerSpecs as $c) {
            $cust = User::create([
                'name' => $c['name'],
                'email' => $c['email'],
                'password' => $passwordHash,
                'role' => 'customer',
                'is_active' => true,
                'created_at' => $now->copy()->subMonths(rand(0, 5))->subDays(rand(1, 25)),
                'updated_at' => $now,
            ]);
            $customers[] = $cust;
            for ($w = 0; $w < $c['weight']; $w++) {
                $weightedCustomerIds[] = $cust->id;
            }
        }

        // 5. Seed 8 Categories
        $catData = [
            ['id' => 1, 'name' => 'Consumer Electronics', 'slug' => 'electronics', 'icon' => 'fa-microchip', 'accent_color' => '#4f46e5'],
            ['id' => 2, 'name' => 'EV & Solar Hardware', 'slug' => 'ev-solar', 'icon' => 'fa-solar-panel', 'accent_color' => '#059669'],
            ['id' => 3, 'name' => 'Gaming & Peripherals', 'slug' => 'gaming', 'icon' => 'fa-gamepad', 'accent_color' => '#ea580c'],
            ['id' => 4, 'name' => 'Smart Home Devices', 'slug' => 'smart-home', 'icon' => 'fa-house-signal', 'accent_color' => '#0284c7'],
            ['id' => 5, 'name' => 'Audio & Headphones', 'slug' => 'audio', 'icon' => 'fa-headphones', 'accent_color' => '#7c3aed'],
            ['id' => 6, 'name' => 'Laptops & Workstations', 'slug' => 'computing', 'icon' => 'fa-laptop-code', 'accent_color' => '#2563eb'],
            ['id' => 7, 'name' => 'Automotive & Tools', 'slug' => 'automotive', 'icon' => 'fa-screwdriver-wrench', 'accent_color' => '#d97706'],
            ['id' => 8, 'name' => 'Cameras & Drones', 'slug' => 'photography', 'icon' => 'fa-camera', 'accent_color' => '#e11d48'],
        ];

        foreach ($catData as $c) {
            Category::create($c);
        }

        // 6. Promo Coupon
        Coupon::create([
            'code' => 'MEGA20',
            'type' => 'percentage',
            'value' => 20,
            'min_order_amount' => 50,
            'is_active' => true,
        ]);

        // 7. Seed 500 Products with Base64 SVG Images (100% reliable, zero network requests)
        $adjectives = ['Ultra', 'Pro', 'Max', 'Smart', 'Precision', 'Hyper', 'Elite', 'Quantum', 'Turbo', 'Prime', 'Aero', 'Stealth'];
        $nouns = [
            'Gaming Headset', 'LiFePO4 Battery', 'Solar Inverter', 'RGB Keyboard',
            'Studio Headphones', 'NVMe SSD', 'Mesh Router', 'Curved Monitor',
            'Hub Motor Kit', 'Smart Hub', 'Action Camera', 'Power Station',
            'Wireless Mouse', 'GaN Charger', 'Robot Vacuum', 'Torque Wrench'
        ];
        $brands = ['ApexGear', 'VoltCore', 'SolarNex', 'KeyChronX', 'NovaTech', 'Lumina', 'TitanPro', 'PulseAudio'];

        $productRows = [];
        $weightedProductIndices = [];
        $prodCounter = 1;

        foreach ($vendorSpecs as $vIdx => $vSpec) {
            $vendorModel = $vendors[$vIdx];
            $countForVendor = $vSpec['share'];
            $salesMultiplier = max(1, 6 - $vIdx);

            for ($i = 0; $i < $countForVendor; $i++) {
                $catId = ($prodCounter % 8) + 1;
                $adj = $adjectives[$prodCounter % count($adjectives)];
                $noun = $nouns[$prodCounter % count($nouns)];
                $brand = $brands[$vIdx % count($brands)];

                $price = round(rand(29, 890) + 0.99, 2);
                $hasDiscount = ($prodCounter % 3 === 0);
                $compareAt = $hasDiscount ? round($price * 1.25, 2) : null;
                $discountPct = $hasDiscount ? 20 : 0;

                $prodName = "{$brand} {$adj} {$noun} Gen-" . rand(1, 9);

                // Generate a lightweight Base64 SVG string instead of calling external APIs
                $svgText1 = htmlspecialchars($brand);
                $svgText2 = htmlspecialchars($adj . ' ' . $noun);
                $svg = sprintf(
                    '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600"><rect width="100%%" height="100%%" fill="#f8fafc"/><text x="50%%" y="45%%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="42" font-weight="bold" fill="#4f46e5">%s</text><text x="50%%" y="55%%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="24" fill="#64748b">%s</text></svg>',
                    $svgText1, $svgText2
                );
                $dataUri = 'data:image/svg+xml;base64,' . base64_encode($svg);

                $productRows[] = [
                    'id' => $prodCounter,
                    'vendor_id' => $vendorModel->id,
                    'user_id' => $vendorModel->id,
                    'category_id' => $catId,
                    'name' => $prodName,
                    'sku' => 'NXM-' . str_pad((string) $prodCounter, 5, '0', STR_PAD_LEFT),
                    'brand' => $brand,
                    'description' => "Engineered by {$brand} for high reliability and performance. Includes full manufacturer warranty, verified quality inspection, and express courier fulfillment.",
                    'specifications' => json_encode([
                        'Brand' => $brand,
                        'Model SKU' => 'NXM-' . str_pad((string) $prodCounter, 5, '0', STR_PAD_LEFT),
                        'Seller Storefront' => $vendorModel->store_name,
                        'Warranty' => '12 Months Official Coverage',
                    ]),
                    'price' => $price,
                    'compare_at_price' => $compareAt,
                    'discount' => $discountPct,
                    'stock' => ($prodCounter % 17 === 0) ? rand(1, 4) : rand(10, 85),
                    'image' => $dataUri, // Guaranteed to load instantly
                    'is_featured' => ($prodCounter <= 24),
                    'is_flash_deal' => ($prodCounter <= 20),
                    'views_count' => rand(25, 950),
                    'created_at' => $now->copy()->subDays(rand(1, 60)),
                    'updated_at' => $now,
                ];

                for ($m = 0; $m < $salesMultiplier; $m++) {
                    $weightedProductIndices[] = $prodCounter - 1;
                }

                $prodCounter++;
            }
        }

        // Insert products in chunks to prevent memory limits
        foreach (array_chunk($productRows, 100) as $chunk) {
            DB::table('products')->insert($chunk);
        }

        // 8. Seed 320 Orders & ~750 OrderItems (Accurately Distributed in Time for Graphs)
        $orderRows = [];
        $orderItemRows = [];
        $statuses = ['pending', 'processing', 'packed', 'shipped', 'delivered', 'delivered'];
        $cities = ['Islamabad', 'Lahore', 'Karachi', 'Rawalpindi', 'Faisalabad', 'Multan'];

        for ($oId = 1; $oId <= 320; $oId++) {
            // Split orders: 60% in the last 14 days (for daily charts), 40% in the last 5 months (for monthly charts)
            if ($oId <= 192) {
                $daysAgo = rand(0, 13);
                $orderDate = $now->copy()->subDays($daysAgo)->subHours(rand(1, 22));
            } else {
                $orderDate = $now->copy()->subMonths(rand(1, 5))->addDays(rand(1, 28));
            }

            $customerId = $weightedCustomerIds[array_rand($weightedCustomerIds)];
            $orderStatus = $statuses[array_rand($statuses)];
            $itemsCount = rand(1, 4);
            $orderTotal = 0;

            for ($k = 0; $k < $itemsCount; $k++) {
                $pIndex = $weightedProductIndices[array_rand($weightedProductIndices)];
                $pRow = $productRows[$pIndex];
                $qty = rand(1, 3);
                $linePrice = $pRow['price'];
                $orderTotal += ($linePrice * $qty);

                $orderItemRows[] = [
                    'order_id' => $oId,
                    'product_id' => $pRow['id'],
                    'product_name' => $pRow['name'],
                    'price' => $linePrice,
                    'quantity' => $qty,
                    'discount_applied' => $pRow['discount'],
                    'status' => $orderStatus,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ];
            }

            $orderRows[] = [
                'id' => $oId,
                'user_id' => $customerId,
                'total_price' => round($orderTotal, 2),
                'total_amount' => round($orderTotal, 2),
                'status' => $orderStatus,
                'tracking_number' => 'NXM-' . strtoupper(Str::random(4)) . '-' . (100000 + $oId),
                'coupon_code' => ($oId % 5 === 0) ? 'MEGA20' : null,
                'discount_amount' => ($oId % 5 === 0) ? round($orderTotal * 0.20, 2) : 0,
                'payment_method' => 'cod',
                'shipping_name' => 'Verified Recipient #' . $customerId,
                'shipping_phone' => '+92 300 ' . rand(1000000, 9999999),
                'shipping_address' => 'House ' . rand(1, 99) . ', Street ' . rand(1, 40) . ', Sector F-' . rand(6, 11),
                'shipping_city' => $cities[$oId % count($cities)],
                'shipping_postal_code' => (string) rand(44000, 75000),
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ];
        }

        foreach (array_chunk($orderRows, 100) as $chunk) {
            DB::table('orders')->insert($chunk);
        }
        foreach (array_chunk($orderItemRows, 150) as $chunk) {
            DB::table('order_items')->insert($chunk);
        }

        // 9. Seed Verified Customer Reviews across Products
        $reviewRows = [];
        $headlines = [
            'Excellent build quality and fast shipping!',
            'Great value for the price, works as advertised.',
            'Solid performance, highly recommended seller.',
            'Packaging was secure and specs match description.',
            'Very satisfied with this purchase!'
        ];

        for ($pId = 1; $pId <= 400; $pId++) {
            $cust = $customers[$pId % count($customers)];
            $reviewRows[] = [
                'user_id' => $cust->id,
                'product_id' => $pId,
                'rating' => rand(4, 5),
                'title' => $headlines[$pId % count($headlines)],
                'comment' => 'Tested thoroughly upon arrival. Performs reliably and arrived ahead of schedule.',
                'is_verified_purchase' => true,
                'created_at' => $now->copy()->subDays(rand(1, 30)),
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($reviewRows, 100) as $chunk) {
            DB::table('reviews')->insert($chunk);
        }
    }
}
