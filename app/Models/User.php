<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\CustomVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;


class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'img',
        'name',
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'phone',
        'employee_id',
        'password',
        'address',
        'img',
        'role_id',
        'provider_id',
        'plan_id',
        'storage_used',
        'api_token',
        'provider',
        'google2fa_secret',
        'google2fa_enabled',
        'gender_id',
        'date_of_birth',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class)->withDefault(); // Utilise un modèle par défaut si l'ID n'est pas présent
    }

    // public function role()
    // {
    //     return $this->belongsTo(Role::class, 'role_id');
    // }

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
        'email_verified' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'plan_started_at' => 'datetime',
        'date_of_birth' => 'date',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function getPlanDetails()
    {
        return $this->plan()->first();
    }

    public function updatePlanStartDateIfExpired()
    {
        if (!$this->plan || !$this->plan_started_at) {
            return;
        }

        // Récupérer la période du plan en jours
        $periode = (int) $this->plan->periode;

        // Calculer la date d'expiration
        $expirationDate = Carbon::parse($this->plan_started_at)->addDays($periode);

        // Vérifier si la date actuelle est supérieure à la date d'expiration
        if (Carbon::now()->greaterThan($expirationDate)) {
            $this->update([
                'plan_id' => 1,
                'plan_started_at' => Carbon::now(),
            ]);
        }
    }

    public function getFormattedPlanStartDate()
    {
        if (!$this->plan_started_at) {
            return null;
        }

        return [
            'year' => $this->plan_started_at->year,
            'month' => $this->plan_started_at->month - 1, // JavaScript commence les mois à 0
            'day' => $this->plan_started_at->day,
        ];
    }


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

    // Relations
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    //  public function plan()
    //  {
    //      return $this->belongsTo(Plan::class);
    //  }

    //  public function getPlanDetails()
    //  {
    //      return $this->plan()->first();
    //  }


    //  public function provider()
    //  {
    //      return $this->belongsTo(Provider::class);
    //  }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    //  public function cybers()
    //  {
    //      return $this->belongsToMany(Cyber::class, 'cyber_user');
    //  }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function cybers()
    {
        return $this->belongsToMany(Cyber::class, 'cyber_user');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function publicNetworks()
    {
        return $this->hasMany(PublicNetwork::class);
    }

    /**
     * Accesseur pour calculer l'âge à partir de la date de naissance
     */
    public function getAgeAttribute()
    {
        if ($this->date_of_birth) {
            return $this->date_of_birth->age;
        }
        return null;
    }

    /**
     * Accesseur pour formater la date de naissance
     */
    public function getFormattedDateOfBirthAttribute()
    {
        if ($this->date_of_birth) {
            return $this->date_of_birth->format('d/m/Y');
        }
        return null;
    }
}
