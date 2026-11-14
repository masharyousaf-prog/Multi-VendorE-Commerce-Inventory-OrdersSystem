<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_price',
        'total_amount',
        'status',
        'tracking_number',
        'coupon_code',
        'discount_amount',
        'payment_method',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_postal_code',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Returns dynamic 5-step tracking pipeline backed by the order's actual status.
     */
    public function getTrackingStepsAttribute(): array
    {
        $rankMap = [
            'pending' => 1,
            'processing' => 2,
            'packed' => 3,
            'shipped' => 4,
            'delivered' => 5,
            'completed' => 5,
            'cancelled' => 0,
        ];

        $currentRank = $rankMap[strtolower($this->status ?? 'pending')] ?? 1;

        return [
            [
                'step' => 1,
                'icon' => 'fa-file-invoice',
                'title' => '1. Order Placed',
                'desc' => $this->created_at ? $this->created_at->format('M d, h:i A') : 'Registered',
                'completed' => $currentRank >= 1,
                'current' => $currentRank === 1,
            ],
            [
                'step' => 2,
                'icon' => 'fa-clipboard-check',
                'title' => '2. Order Confirmed',
                'desc' => 'COD Verified',
                'completed' => $currentRank >= 2,
                'current' => $currentRank === 2,
            ],
            [
                'step' => 3,
                'icon' => 'fa-boxes-packing',
                'title' => '3. Vendor Packing',
                'desc' => 'Warehouse Prep',
                'completed' => $currentRank >= 3,
                'current' => $currentRank === 3,
            ],
            [
                'step' => 4,
                'icon' => 'fa-truck-fast',
                'title' => '4. In Transit',
                'desc' => 'Handed to Courier',
                'completed' => $currentRank >= 4,
                'current' => $currentRank === 4,
            ],
            [
                'step' => 5,
                'icon' => 'fa-house-circle-check',
                'title' => '5. Delivered',
                'desc' => 'COD Collected',
                'completed' => $currentRank >= 5,
                'current' => $currentRank === 5,
            ],
        ];
    }
}
