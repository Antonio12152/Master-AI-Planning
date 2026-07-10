<?php

return [
    'chat' => [
        'endpoint' => env('AI_CHAT_ENDPOINT'),
        'api_key' => env('AI_CHAT_API_KEY'),
        'model' => env('AI_CHAT_MODEL', 'gpt-4o-mini'),
        'temperature' => env('AI_CHAT_TEMPERATURE', 0.7),
        'max_tokens' => env('AI_CHAT_MAX_TOKENS', 800),
        'timeout' => env('AI_CHAT_TIMEOUT', 30),
        'system_prompt' => env('AI_CHAT_SYSTEM_PROMPT', 'You are a helpful planning assistant. Help the user improve their plan with clear, pragmatic suggestions.'),
    ],
];
