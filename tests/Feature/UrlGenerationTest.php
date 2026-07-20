<?php

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;

it('uses https for generated URLs in production', function () {
    $this->app['env'] = 'production';
    $this->app['config']->set('app.url', 'https://example.com');
    $this->app['config']->set('app.asset_url', 'https://example.com');
    putenv('APP_URL=https://example.com');

    $provider = new AppServiceProvider($this->app);
    $provider->boot();

    expect(URL::to('/foo'))->toStartWith('https://');
});
