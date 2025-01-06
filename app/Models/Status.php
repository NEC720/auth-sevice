<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;


    // Spécifiez le nom de la table
    protected $table = 'status';
    protected $fillable = ['name'];
}
