<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    public function createApplication()
    {
        if (! file_exists(__DIR__.'/../.env')) {
            file_put_contents(__DIR__.'/../.env', "APP_KEY=base64:testingappkey1234567890abcdef=\nAPP_ENV=testing\nAPP_DEBUG=false\n");
        }

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
