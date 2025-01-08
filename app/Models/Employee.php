<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;


    protected $fillable = ['cyber_id', 'bio', 'status_id'];

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function cyber()
    {
        return $this->belongsTo(Cyber::class, 'cyber_id');
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
