<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    use HasFactory;
    protected $table = "customers";

    /**
     * The attributes that are mass assignable
     * @var array
     */
    protected $fillable = ['username', 'phone', 'company_id'];
    
}
