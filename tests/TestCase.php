<?php

namespace AntiCmsBuilder\Tests;

use AntiCmsBuilder\AntiCmsBuilderServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Additional setup for tests
    }

    protected function getPackageProviders($app)
    {
        return [
            AntiCmsBuilderServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup test environment
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}