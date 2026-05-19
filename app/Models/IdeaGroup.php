<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdeaGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'name',
        'description',
        'sort_order',
        'idea_count',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relations
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function ideas()
    {
        return $this->hasMany(Idea::class, 'group_id');
    }

    // Scopes
    public function scopeForPlan($query, $planId)
    {
        return $query->where('plan_id', $planId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
