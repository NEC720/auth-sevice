<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visits extends Model
{
    use HasFactory , SoftDeletes;

    protected $table = 'visits'; 

    protected $fillable = [
        'ip_address',
        'user_agent'
    ];
    
}
