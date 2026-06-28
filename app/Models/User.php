<?php

namespace App\Models;

// ✅    LARAVEL AUTH
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    // ============================================================
    // TRAITS
    // ============================================================
    use HasApiTokens, HasFactory, Notifiable;

    // ============================================================
    // FILLABLE -     
    // ============================================================
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'bio',
        'timezone',
        'is_active',
        'is_verified',
        'is_admin',
        'last_login_at',
    ];

    // ============================================================
    // HIDDEN -    JSON
    // ============================================================
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ============================================================
    // CASTS -   (Laravel 11)
    // ============================================================
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // ✅ Laravel 11  
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'is_admin' => 'boolean',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ============================================================
    // SCOPES -  
    // ============================================================

    /**
     *    
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     *    
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     *     
     */
    public function scopeInTimezone($query, $timezone)
    {
        return $query->where('timezone', $timezone);
    }

    /**
     *     X 
     */
    public function scopeRecentlyActive($query, $days = 7)
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }

    // ============================================================
    // RELATIONS -  
    // ============================================================

    /**
     * ,   
     */
    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * ,     ( plan_members)
     */
    public function planMembers()
    {
        return $this->hasMany(PlanMember::class);
    }

    /**
     *    ( + )
     */
    public function accessiblePlans()
    {
        return Plan::where('user_id', $this->id)
            ->orWhereHas('members', function ($q) {
                $q->where('user_id', $this->id);
            });
    }

    /**
     * API  
     */
    public function apiTokens()
    {
        return $this->hasMany(ApiToken::class);
    }

    /**
     *   
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     *  
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    // ============================================================
    // CUSTOM METHODS -  
    // ============================================================

    /**
     * ,   email 
     * 
     * @return bool
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     *  email  
     * 
     * @return bool
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     *    
     * 
     * @return bool
     */
    public function updateLastLogin(): bool
    {
        return $this->update(['last_login_at' => now()]);
    }

    /**
     *    
     * 
     * @return int
     */
    public function getAccessiblePlansCount(): int
    {
        return $this->accessiblePlans()->count();
    }

    /**
     * ,    
     * 
     * @param Plan $plan
     * @return bool
     */
    public function isAdminOfPlan(Plan $plan): bool
    {
        return $plan->user_id === $this->id || 
               $plan->members()
                   ->where('user_id', $this->id)
                   ->where('role', 'admin')
                   ->exists();
    }

    /**
     *    
     * 
     * @param Plan $plan
     * @return string|null
     */
    public function getRoleInPlan(Plan $plan): ?string
    {
        return $plan->getUserRole($this);
    }

    /**
     * Check if the user has enabled two-factor authentication.
     */
    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return !is_null($this->two_factor_secret);
    }

    // ============================================================
    // EVENTS -    
    // ============================================================

    /**
     *     
     */
    protected static function booted(): void
    {
        //   ,  is_verified  false
        static::creating(function ($user) {
            if (!isset($user->is_verified)) {
                $user->is_verified = false;
            }
        });
    }
}
