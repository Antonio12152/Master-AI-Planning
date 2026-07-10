import { Head, usePage } from '@inertiajs/react';
import { Bot, KeyRound, Sparkles, Info, Server, Globe2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SettingsLayout from '@/layouts/SettingsLayout';
import { axiosInstance } from '@/lib/http';

type AiSettingsState = {
    endpoint: string;
    api_key: string;
    model: string;
    temperature: string;
    max_tokens: string;
    timeout: string;
    system_prompt: string;
};

const emptyState: AiSettingsState = {
    endpoint: '',
    api_key: '',
    model: '',
    temperature: '0.7',
    max_tokens: '800',
    timeout: '30',
    system_prompt: 'You are a helpful planning assistant.',
};

export default function AiSettingsPage() {
    const { auth } = usePage().props as { auth?: { user?: { name?: string } } };
    const [settings, setSettings] = useState<AiSettingsState>(emptyState);
    const [hasApiKey, setHasApiKey] = useState(false);
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState<string | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const loadSettings = async () => {
            try {
                const response = await axiosInstance.get('/ai-settings');
                setSettings({
                    ...emptyState,
                    ...(response.data.settings || {}),
                });
                setHasApiKey(Boolean(response.data.settings?.has_api_key));
            } catch {
                setError('Unable to load your saved AI settings.');
            }
        };

        void loadSettings();
    }, []);

    const handleSave = async (event: React.FormEvent) => {
        event.preventDefault();
        setSaving(true);
        setMessage(null);
        setError(null);

        try {
            const response = await axiosInstance.put('/ai-settings', {
                ...settings,
                temperature: Number(settings.temperature),
                max_tokens: Number(settings.max_tokens),
                timeout: Number(settings.timeout),
            });

            setSettings({
                ...emptyState,
                ...(response.data.settings || {}),
            });
            setHasApiKey(Boolean(response.data.settings?.has_api_key));
            setMessage('AI settings saved successfully.');
        } catch {
            setError('Unable to save your AI settings.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <>
            <Head title="AI settings" />

            <SettingsLayout currentPage="ai">
                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title="AI provider"
                        description="Set the endpoint and model that your AI chat should use"
                    />

                    <div className="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <div className="mb-6 flex items-start gap-3">
                            <div className="rounded-lg bg-primary/10 p-2 text-primary">
                                <Bot size={20} />
                            </div>
                            <div>
                                <h2 className="text-lg font-semibold">Use your own LLM</h2>
                                <p className="text-sm text-muted-foreground">
                                    These values are stored for {auth?.user?.name || 'your account'} and used by the plan AI chat.
                                </p>
                            </div>
                        </div>

                        <div className="mb-6 rounded-lg border border-blue-500/20 bg-blue-500/10 p-4 text-sm text-blue-100">
                            <div className="mb-3 flex items-center gap-2 font-semibold">
                                <Info size={16} />
                                Compatibility help
                            </div>
                            <ul className="space-y-2 text-sm text-blue-100/90">
                                <li>• Use any provider with an OpenAI-compatible chat endpoint.</li>
                                <li>• Public cloud providers: enter the full HTTPS endpoint and API key.</li>
                                <li>• Private or local servers: the app server must be able to reach that endpoint.</li>
                                <li>• If your provider needs no auth, leave the API key empty.</li>
                            </ul>
                        </div>

                        <form className="space-y-5" onSubmit={handleSave}>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2 md:col-span-2">
                                    <Label htmlFor="endpoint">Endpoint</Label>
                                    <Input
                                        id="endpoint"
                                        value={settings.endpoint}
                                        onChange={(event) => setSettings({ ...settings, endpoint: event.target.value })}
                                        placeholder="https://your-provider.example/v1/chat/completions"
                                    />
                                </div>

                                <div className="space-y-2 md:col-span-2">
                                    <Label htmlFor="api_key">API key</Label>
                                    <div className="relative">
                                        <KeyRound className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" size={16} />
                                        <Input
                                            id="api_key"
                                            type="password"
                                            value={settings.api_key}
                                            onChange={(event) => setSettings({ ...settings, api_key: event.target.value })}
                                            placeholder="Optional for providers that do not require auth"
                                            className="pl-10"
                                        />
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        {hasApiKey
                                            ? 'A stored API key is already configured and will stay protected in the database.'
                                            : 'Leave this empty if your provider does not require authentication.'}
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="model">Model</Label>
                                    <Input
                                        id="model"
                                        value={settings.model}
                                        onChange={(event) => setSettings({ ...settings, model: event.target.value })}
                                        placeholder="gpt-4o-mini"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="temperature">Temperature</Label>
                                    <Input
                                        id="temperature"
                                        type="number"
                                        min="0"
                                        max="2"
                                        step="0.1"
                                        value={settings.temperature}
                                        onChange={(event) => setSettings({ ...settings, temperature: event.target.value })}
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="max_tokens">Max tokens</Label>
                                    <Input
                                        id="max_tokens"
                                        type="number"
                                        min="1"
                                        max="4000"
                                        value={settings.max_tokens}
                                        onChange={(event) => setSettings({ ...settings, max_tokens: event.target.value })}
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="timeout">Timeout (seconds)</Label>
                                    <Input
                                        id="timeout"
                                        type="number"
                                        min="1"
                                        max="300"
                                        value={settings.timeout}
                                        onChange={(event) => setSettings({ ...settings, timeout: event.target.value })}
                                    />
                                </div>

                                <div className="space-y-2 md:col-span-2">
                                    <Label htmlFor="system_prompt">System prompt</Label>
                                    <textarea
                                        id="system_prompt"
                                        value={settings.system_prompt}
                                        onChange={(event) => setSettings({ ...settings, system_prompt: event.target.value })}
                                        rows={4}
                                        className="flex min-h-[120px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm outline-none focus-visible:border-ring"
                                        placeholder="Describe how the AI should behave"
                                    />
                                </div>
                            </div>

                            <div className="rounded-lg border border-border/70 bg-muted/30 p-4">
                                <div className="mb-3 flex items-center gap-2 text-sm font-semibold text-foreground">
                                    <Server size={16} />
                                    Example endpoints
                                </div>
                                <div className="grid gap-3 md:grid-cols-2">
                                    <div className="rounded-md border border-border/60 bg-background/70 p-3 text-sm">
                                        <div className="mb-1 flex items-center gap-2 font-medium text-foreground">
                                            <Globe2 size={14} />
                                            Public provider
                                        </div>
                                        <code className="break-all text-xs text-muted-foreground">https://api.openai.com/v1/chat/completions</code>
                                    </div>
                                    <div className="rounded-md border border-border/60 bg-background/70 p-3 text-sm">
                                        <div className="mb-1 flex items-center gap-2 font-medium text-foreground">
                                            <Server size={14} />
                                            Local/private server
                                        </div>
                                        <code className="break-all text-xs text-muted-foreground">http://localhost:11434/v1/chat/completions</code>
                                    </div>
                                </div>
                            </div>

                            <div className="flex flex-wrap items-center gap-3">
                                <Button type="submit" disabled={saving}>
                                    {saving ? 'Saving…' : 'Save AI settings'}
                                </Button>
                                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                    <Sparkles size={16} />
                                    Works with OpenAI-compatible providers.
                                </div>
                            </div>

                            {message && <p className="text-sm font-medium text-green-600">{message}</p>}
                            {error && <p className="text-sm font-medium text-red-600">{error}</p>}
                        </form>
                    </div>
                </div>
            </SettingsLayout>
        </>
    );
}

AiSettingsPage.layout = {
    breadcrumbs: [{ title: 'AI settings' }],
};
