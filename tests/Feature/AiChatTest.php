<?php

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Http;

it('returns an AI response from a configured chat endpoint', function () {
    Http::fake([
        'https://example.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Here is a helpful suggestion.',
                    ],
                ],
            ],
        ], 200),
    ]);

    config()->set('ai.chat.endpoint', 'https://example.com/v1/chat/completions');
    config()->set('ai.chat.api_key', 'secret-key');
    config()->set('ai.chat.model', 'gpt-4o-mini');

    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->postJson("/api/plans/{$plan->id}/ai/chat", [
        'message' => 'Help me improve this plan',
        'selected_group_ids' => [],
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Here is a helpful suggestion.');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.com/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer secret-key');
    });
});

it('uses a user-specific AI configuration when available', function () {
    Http::fake([
        'https://custom.example/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Saved settings response.',
                    ],
                ],
            ],
        ], 200),
    ]);

    $user = User::factory()->create();
    $user->aiSettings()->create([
        'endpoint' => 'https://custom.example/v1/chat/completions',
        'api_key' => 'custom-key',
        'model' => 'claude-3-5-sonnet',
        'temperature' => 0.2,
        'max_tokens' => 1200,
        'timeout' => 45,
        'system_prompt' => 'Use a concise tone.',
    ]);

    $plan = Plan::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->postJson("/api/plans/{$plan->id}/ai/chat", [
        'message' => 'Help me improve this plan',
        'selected_group_ids' => [],
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Saved settings response.');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://custom.example/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer custom-key');
    });
});

it('saves and retrieves a user AI settings profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/api/ai-settings', [
        'endpoint' => 'https://provider.example/v1/chat/completions',
        'api_key' => 'saved-key',
        'model' => 'gpt-4.1',
        'temperature' => 0.3,
        'max_tokens' => 1500,
        'timeout' => 60,
        'system_prompt' => 'Be concise.',
    ]);

    $response->assertOk()
        ->assertJsonPath('settings.endpoint', 'https://provider.example/v1/chat/completions')
        ->assertJsonPath('settings.model', 'gpt-4.1')
        ->assertJsonPath('settings.has_api_key', true)
        ->assertJsonMissingPath('settings.api_key');

    $storedSettings = $user->fresh()->aiSettings()->first();
    expect($storedSettings->getRawOriginal('api_key'))->not->toBe('saved-key');

    $this->actingAs($user)->getJson('/api/ai-settings')
        ->assertOk()
        ->assertJsonPath('settings.endpoint', 'https://provider.example/v1/chat/completions')
        ->assertJsonPath('settings.has_api_key', true)
        ->assertJsonMissingPath('settings.api_key');
});
