<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'plan',
        'queries_this_month',
        'last_query_at',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_query_at' => 'datetime',
        'trial_ends_at' => 'datetime'
    ];

    public function queries()
    {
        return $this->hasMany(Query::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function canMakeQuery(): bool
    {
        if ($this->plan === 'free') {
            return $this->queries_this_month < 3;
        }
        
        return true; // Premium y professional tienen consultas ilimitadas
    }
}
