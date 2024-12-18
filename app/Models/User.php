<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'phone',
        // 'phone_verified_at',
        'password',
        'address',
        'img',
        'role_id',
        'provider_id',
        'plan_id',
        'storage_used',
        'api_token',
        'provider'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // public function sendEmailVerificationNotification()
    // {
    //     // $this->notify(new CustomVerifyEmail());

    //     // Obtenez le token JWT de l'utilisateur connecté
    //     $token = JWTAuth::getToken();
    //     $tokenString = $token ? $token->get() : null;

    //     // Envoyer la notification avec le token JWT
    //     $this->notify(new CustomVerifyEmail($tokenString));
    // }

    public function testUser()
    {
        $nec = DB::select("SELECT * FROM users WHERE email ='necjunana@gmail.com'");
        dd($nec);
    }

}
