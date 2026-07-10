<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiSettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $settings = $request->user()->aiSettings()->first();

        return response()->json([
            'settings' => $settings ? [
                'endpoint' => $settings->endpoint,
                'has_api_key' => ! empty($settings->api_key),
                'model' => $settings->model,
                'temperature' => $settings->temperature,
                'max_tokens' => $settings->max_tokens,
                'timeout' => $settings->timeout,
                'system_prompt' => $settings->system_prompt,
            ] : null,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['nullable', 'string', 'max:2048'],
            'api_key' => ['nullable', 'string', 'max:2048'],
            'model' => ['nullable', 'string', 'max:255'],
            'temperature' => ['nullable', 'numeric', 'between:0,2'],
            'max_tokens' => ['nullable', 'integer', 'min:1', 'max:4000'],
            'timeout' => ['nullable', 'integer', 'min:1', 'max:300'],
            'system_prompt' => ['nullable', 'string', 'max:4000'],
        ]);

        $settings = $request->user()->aiSettings()->firstOrNew();
        $settings->fill($validated);
        $settings->user_id = $request->user()->id;
        $settings->save();

        return response()->json([
            'message' => 'AI settings saved successfully',
            'settings' => [
                'endpoint' => $settings->endpoint,
                'has_api_key' => ! empty($settings->api_key),
                'model' => $settings->model,
                'temperature' => $settings->temperature,
                'max_tokens' => $settings->max_tokens,
                'timeout' => $settings->timeout,
                'system_prompt' => $settings->system_prompt,
            ],
        ]);
    }
}
