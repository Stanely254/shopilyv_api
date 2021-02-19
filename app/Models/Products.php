<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;
    protected $table = "products";

    /**
     * The attributes that are mass assignable
     * @var array
     */
    protected $fillable = [
        'company_id',
        'sku',
        'image'
    ];
}
