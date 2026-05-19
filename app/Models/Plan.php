<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'status',
        'color',
        'icon',
        'idea_count',
        'group_count',
        'member_count',
        'is_public',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'archived_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ideaGroups()
    {
        return $this->hasMany(IdeaGroup::class);
    }

    public function ideas()
    {
        return $this->hasMany(Idea::class);
    }

    public function members()
    {
        return $this->hasMany(PlanMember::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    // Методы контроля доступа
    /**
     * Проверить, может ли пользователь просматривать план
     */
    public function canView(User $user): bool
    {
        // Владелец может всегда просматривать
        if ($this->user_id === $user->id) {
            return true;
        }

        // Только члены плана могут просматривать (через plan_members)
        return $this->members()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Проверить, может ли пользователь редактировать план
     */
    public function canEdit(User $user): bool
    {
        // Только владелец или админ/редактор могут редактировать
        if ($this->user_id === $user->id) {
            return true;
        }

        $member = $this->members()
            ->where('user_id', $user->id)
            ->first();

        return $member && in_array($member->role, ['admin', 'editor']);
    }

    /**
     * Проверить, может ли пользователь управлять членами плана
     */
    public function canManageMembers(User $user): bool
    {
        // Только владелец или админ могут управлять членами
        if ($this->user_id === $user->id) {
            return true;
        }

        $member = $this->members()
            ->where('user_id', $user->id)
            ->first();

        return $member && $member->role === 'admin';
    }

    /**
     * Получить роль пользователя в плане
     */
    public function getUserRole(User $user): ?string
    {
        if ($this->user_id === $user->id) {
            return 'owner'; // Владелец
        }

        return $this->members()
            ->where('user_id', $user->id)
            ->first()?->role;
    }
}
