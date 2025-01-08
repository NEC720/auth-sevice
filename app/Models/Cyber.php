<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cyber extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'opening_hours'];
}
