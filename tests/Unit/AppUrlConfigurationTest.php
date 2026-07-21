<?php

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

it('prefers https for secure requests when the configured app url is http', function () {
    $app = new Application(base_path());
    $app->instance('request', Request::create('https://master-ai-planning.fly.dev/plans'));
    $provider = new class($app) extends AppServiceProvider {
        public function exposeResolveAppUrl(Request $request): ?string
        {
            return $this->resolveAppUrl($request);
        }
    };

    $request = Request::create('https://master-ai-planning.fly.dev/plans');

    expect($provider->exposeResolveAppUrl($request))->toBe('https://master-ai-planning.fly.dev');
});
