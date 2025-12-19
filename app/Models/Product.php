<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'stock',
        'image',
        'discount'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    // --- Relationships ---

    /**
     * A Product belongs to one User (Vendor).
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * A Product can be in many CartItems.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // HELPER: Calculate Final Price automatically
    // Usage: $product->final_price
    public function getFinalPriceAttribute()
    {
        if ($this->discount > 0) {
            $discountAmount = ($this->price * $this->discount) / 100;
            return round($this->price - $discountAmount, 2);
        }
        return $this->price;
    }
}
