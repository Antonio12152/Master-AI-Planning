<?php

it('configures a persistent session driver for production', function () {
    $flyConfig = file_get_contents(base_path('fly.toml'));

    expect($flyConfig)->toContain('SESSION_DRIVER = "database"');
    expect($flyConfig)->toContain('SESSION_SECURE_COOKIE = "true"');
});
