<?php

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

it('includes the current request host in sanctum stateful domains', function () {
    $app = new Application(base_path());
    $app->instance('config', new \Illuminate\Config\Repository());
    $request = Request::create('https://master-ai-planning.fly.dev/plans');
    $app->instance('request', $request);

    $provider = new class($app) extends AppServiceProvider {
        public function exposeConfigureSanctumStatefulDomains(Request $request, ?string $appUrl): void
        {
            $this->configureSanctumStatefulDomains($request, $appUrl);
        }
    };

    $provider->exposeConfigureSanctumStatefulDomains($request, 'https://master-ai-planning.fly.dev');

    expect(config('sanctum.stateful'))->toContain('master-ai-planning.fly.dev');
});
