<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class AiSettings extends Model
{
    protected $table = 'ai_settings';

    protected $fillable = [
        'user_id',
        'endpoint',
        'api_key',
        'model',
        'temperature',
        'max_tokens',
        'timeout',
        'system_prompt',
    ];

    protected $casts = [
        'temperature' => 'float',
        'max_tokens' => 'integer',
        'timeout' => 'integer',
    ];

    public function setApiKeyAttribute($value): void
    {
        $this->attributes['api_key'] = $value === null || $value === '' ? null : Crypt::encryptString($value);
    }

    public function getApiKeyAttribute($value): string|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Crypt::decryptString($value);
    }

    public function setEndpointAttribute($value): void
    {
        $this->attributes['endpoint'] = $value === null || $value === '' ? null : $value;
    }

    public function setSystemPromptAttribute($value): void
    {
        $this->attributes['system_prompt'] = $value === null || $value === '' ? null : $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
