<?php
// app/Models/Category.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'accent_color'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
