<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicNetwork extends Model
{
    use HasFactory;

    protected $table = 'public_networks';

    protected $fillable = [
        'user_id',
        'platform',
        'url',
        'icon',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
