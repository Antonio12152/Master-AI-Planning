<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Idea extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'plan_id',
        'text',
        'description',
        'status',
        'priority',
        'tags',
        'sort_order',
        'likes_count',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relations
    public function group()
    {
        return $this->belongsTo(IdeaGroup::class, 'group_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // Scopes
    public function scopeForPlan($query, $planId)
    {
        return $query->where('plan_id', $planId);
    }

    public function scopeForGroup($query, $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 2);
    }
}
