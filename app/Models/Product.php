<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'user_id',
        'category_id',
        'name',
        'sku',
        'brand',
        'description',
        'specifications',
        'price',
        'compare_at_price',
        'discount',
        'stock',
        'image',
        'is_featured',
        'is_flash_deal',
        'flash_deal_ends_at',
        'views_count',
    ];

    // Automatically append these computed attributes so they are available in Blade and API JSON
    protected $appends = ['image_url', 'discount_percentage', 'average_rating'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_flash_deal' => 'boolean',
            'flash_deal_ends_at' => 'datetime',
            'specifications' => 'array',
        ];
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Resolves the correct image path whether it's an external seeded URL or a local upload.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        // If it's an external seeded URL (like Picsum or Placehold.co), return it directly
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        // Otherwise, serve it via the local media route
        $cleanPath = ltrim(str_replace('public/', '', $this->image), '/');
        return url('/media/' . $cleanPath);
    }

    public function getDiscountPercentageAttribute(): int
    {
        if ($this->compare_at_price && $this->compare_at_price > $this->price) {
            return (int) round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100);
        }
        return (int) ($this->discount ?? 0);
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) ($this->reviews_avg_rating ?? $this->reviews()->avg('rating') ?? 4.5), 1);
    }
}
