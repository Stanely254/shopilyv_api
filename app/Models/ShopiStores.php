<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopiStores extends Model
{
    use HasFactory;
    protected $table = "shopi_stores";
    /**
     * The attributes that are mass assignable
     * @var array
     */
    protected $fillable = [
        'store'
    ];

    /**
     * The attributes that should cast to native types
     * @var array
     */
    protected $casts = [
        'store' => 'string'
    ];
}
