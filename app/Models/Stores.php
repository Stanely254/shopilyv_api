<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stores extends Model
{
    use HasFactory;

    protected $table = "stores";

    /**
     * The attributes that are mass assignable
     * @var array
     */
    protected $fillable = [
        'name', 'location_name'
    ];
}
