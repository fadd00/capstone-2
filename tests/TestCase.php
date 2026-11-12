<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * Creates the application.
     *
     * Compatible with Laravel 10, 11, and 12/13 application bootstrappers.
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        if (is_object($app) && method_exists($app, 'create')) {
            return $app->create();
        }

        if (is_object($app) && method_exists($app, 'make')) {
            $app->make(Kernel::class)->bootstrap();
        }

        return $app;
    }
}
