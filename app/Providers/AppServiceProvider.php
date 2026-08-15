<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        if ($this->app->environment('production')) {
            $request = app()->runningInConsole() ? null : request();
            $appUrl = $this->resolveAppUrl($request);

            if ($appUrl) {
                $appUrl = rtrim($appUrl, '/');

                config(['app.url' => $appUrl]);
                config(['app.asset_url' => $appUrl]);
                config(['filesystems.disks.public.url' => $appUrl.'/storage']);
                URL::forceRootUrl($appUrl);
                URL::forceScheme(parse_url($appUrl, PHP_URL_SCHEME) ?: 'https');
            }

            if ($request) {
                $this->configureSanctumStatefulDomains($request, $appUrl);
            }

            Request::setTrustedProxies(
                ['0.0.0.0/0', '::/0'],
                Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_HOST
                    | Request::HEADER_X_FORWARDED_PORT
                    | Request::HEADER_X_FORWARDED_PROTO
            );
        }
    }

    protected function resolveAppUrl(?Request $request): ?string
    {
        $configuredUrl = null;

        if ($this->app->bound('config')) {
            $configuredUrl = $this->app['config']->get('app.url');
        }

        if (! $configuredUrl) {
            $configuredUrl = env('APP_URL');
        }

        if ($request === null) {
            return $configuredUrl;
        }

        $isSecureRequest = $this->requestIsSecure($request);

        if ($configuredUrl) {
            $parsedUrl = parse_url($configuredUrl);
            $configuredHost = $parsedUrl['host'] ?? null;
            $requestHost = $request->getHost();
            $host = $configuredHost && ! in_array(strtolower($configuredHost), ['localhost', '127.0.0.1'], true)
                ? $configuredHost
                : ($requestHost ?: $configuredHost);
            $port = $parsedUrl['port'] ?? $request->getPort();
            $configuredScheme = $parsedUrl['scheme'] ?? $request->getScheme();
            $scheme = $isSecureRequest ? 'https' : $configuredScheme;

            if ($host === null) {
                return null;
            }

            $appUrl = $scheme.'://'.$host;

            if ($port !== null && $port !== 80 && $port !== 443) {
                $appUrl .= ':'.$port;
            }

            return $appUrl;
        }

        $scheme = $isSecureRequest ? 'https' : $request->getScheme();

        if ($request->getHost()) {
            return $scheme.'://'.$request->getHost();
        }

        return null;
    }

    protected function requestIsSecure(Request $request): bool
    {
        $forwardedProto = $request->headers->get('X-Forwarded-Proto');

        if (is_string($forwardedProto) && $forwardedProto !== '') {
            return str_contains($forwardedProto, 'https');
        }

        return $request->isSecure();
    }

    protected function configureSanctumStatefulDomains(Request $request, ?string $appUrl): void
    {
        $domains = config('sanctum.stateful', []);
        $hosts = [];

        foreach ($domains as $domain) {
            $hosts[] = trim($domain);
        }

        if ($request->getHost()) {
            $hosts[] = $request->getHost();
        }

        if ($appUrl) {
            $parsed = parse_url($appUrl);
            if (! empty($parsed['host'])) {
                $hosts[] = $parsed['host'];
            }
        }

        $hosts = array_values(array_unique(array_filter($hosts)));

        config(['sanctum.stateful' => $hosts]);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
