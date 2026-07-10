<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class AiChatService
{
    public function chat(Plan $plan, string $message, array $selectedGroupIds = [], ?User $user = null): array
    {
        $settings = $this->resolveSettings($user ?? $plan->user);
        $endpoint = $settings['endpoint'] ?? config('ai.chat.endpoint');

        if (empty($endpoint)) {
            throw new \RuntimeException('AI chat endpoint is not configured. Set AI_CHAT_ENDPOINT in your environment or save AI settings for this user.');
        }

        $selectedGroups = $plan->ideaGroups()
            ->with('ideas')
            ->whereIn('id', $selectedGroupIds)
            ->get();

        $payload = [
            'model' => $settings['model'] ?? config('ai.chat.model', 'gpt-4o-mini'),
            'temperature' => (float) ($settings['temperature'] ?? config('ai.chat.temperature', 0.7)),
            'max_tokens' => (int) ($settings['max_tokens'] ?? config('ai.chat.max_tokens', 800)),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $settings['system_prompt'] ?? config(
                        'ai.chat.system_prompt',
                        'You are a helpful planning assistant. Help the user improve their plan with clear, pragmatic suggestions.'
                    ),
                ],
                [
                    'role' => 'user',
                    'content' => $this->buildPrompt($plan, $selectedGroups, $message),
                ],
            ],
        ];

        $response = Http::timeout((int) ($settings['timeout'] ?? config('ai.chat.timeout', 30)))
            ->withHeaders($this->buildHeaders($settings))
            ->post($endpoint, $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('The configured AI provider returned an error.');
        }

        $data = $response->json();
        $content = $this->extractMessageContent($data);

        if (empty($content)) {
            throw new \RuntimeException('The configured AI provider did not return a usable response.');
        }

        return [
            'message' => $content,
            'model' => $payload['model'],
        ];
    }

    private function buildHeaders(array $settings = []): array
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $apiKey = $settings['api_key'] ?? config('ai.chat.api_key');
        if (! empty($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        return $headers;
    }

    private function resolveSettings(?User $user = null): array
    {
        if ($user?->relationLoaded('aiSettings')) {
            $settings = $user->aiSettings;
        } else {
            $settings = $user?->aiSettings()->first();
        }

        if (! $settings) {
            return [];
        }

        return [
            'endpoint' => $settings->endpoint,
            'api_key' => $settings->api_key,
            'model' => $settings->model,
            'temperature' => $settings->temperature,
            'max_tokens' => $settings->max_tokens,
            'timeout' => $settings->timeout,
            'system_prompt' => $settings->system_prompt,
        ];
    }

    private function buildPrompt(Plan $plan, $selectedGroups, string $message): string
    {
        $planLines = [
            'Plan name: ' . $plan->name,
            'Plan description: ' . ($plan->description ?? 'No description provided.'),
            'User request: ' . $message,
        ];

        if ($selectedGroups->isNotEmpty()) {
            $planLines[] = 'Selected groups:';

            foreach ($selectedGroups as $group) {
                $planLines[] = '- ' . $group->name . ' (' . $group->ideas->count() . ' ideas)';

                foreach ($group->ideas as $idea) {
                    $planLines[] = '  • ' . $idea->text . ($idea->description ? ' — ' . $idea->description : '');
                }
            }
        } else {
            $planLines[] = 'No specific groups selected. Consider the whole plan context.';
        }

        return implode(PHP_EOL, $planLines);
    }

    private function extractMessageContent(array $response): ?string
    {
        if (! empty($response['choices'][0]['message']['content'])) {
            return (string) $response['choices'][0]['message']['content'];
        }

        if (! empty($response['message']['content'])) {
            return (string) $response['message']['content'];
        }

        return null;
    }
}
