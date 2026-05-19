<?php

namespace App\Models;

// ✅ ОБЯЗАТЕЛЬНЫЕ ИМПОРТЫ ДЛЯ LARAVEL AUTH
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
    // FILLABLE - какие поля можно массово заполнять
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
        'last_login_at',
    ];

    // ============================================================
    // HIDDEN - не показывать в JSON
    // ============================================================
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ============================================================
    // CASTS - типизация данных (Laravel 11)
    // ============================================================
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // ✅ Laravel 11 автоматически хеширует
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ============================================================
    // SCOPES - удобные запросы
    // ============================================================

    /**
     * Получить только активных пользователей
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Получить только верифицированных пользователей
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Получить пользователей по временной зоне
     */
    public function scopeInTimezone($query, $timezone)
    {
        return $query->where('timezone', $timezone);
    }

    /**
     * Получить активных в течение X дней
     */
    public function scopeRecentlyActive($query, $days = 7)
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }

    // ============================================================
    // RELATIONS - ваши связи
    // ============================================================

    /**
     * Планы, созданные этим пользователем
     */
    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * Планы, к которым есть доступ (через plan_members)
     */
    public function planMembers()
    {
        return $this->hasMany(PlanMember::class);
    }

    /**
     * Все доступные планы (созданные + добавленные)
     */
    public function accessiblePlans()
    {
        return Plan::where('user_id', $this->id)
            ->orWhereHas('members', function ($q) {
                $q->where('user_id', $this->id);
            });
    }

    /**
     * API токены пользователя
     */
    public function apiTokens()
    {
        return $this->hasMany(ApiToken::class);
    }

    /**
     * Логи активности пользователя
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Сессии пользователя
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    // ============================================================
    // CUSTOM METHODS - ваши методы
    // ============================================================

    /**
     * Проверить, верифицирован ли email пользователя
     * 
     * @return bool
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Отметить email как верифицированный
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
     * Обновить время последнего входа
     * 
     * @return bool
     */
    public function updateLastLogin(): bool
    {
        return $this->update(['last_login_at' => now()]);
    }

    /**
     * Получить количество доступных планов
     * 
     * @return int
     */
    public function getAccessiblePlansCount(): int
    {
        return $this->accessiblePlans()->count();
    }

    /**
     * Проверить, является ли админом плана
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
     * Получить роль в плане
     * 
     * @param Plan $plan
     * @return string|null
     */
    public function getRoleInPlan(Plan $plan): ?string
    {
        return $plan->getUserRole($this);
    }

    // ============================================================
    // EVENTS - события жизненного цикла модели
    // ============================================================

    /**
     * Запустить при создании новой модели
     */
    protected static function booted(): void
    {
        // При создании пользователя, установить is_verified в false
        static::creating(function ($user) {
            if (!isset($user->is_verified)) {
                $user->is_verified = false;
            }
        });
    }
}
