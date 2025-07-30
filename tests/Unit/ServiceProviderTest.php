<?php

namespace AntiCmsBuilder\Tests\Unit;

use AntiCmsBuilder\AntiCmsBuilderServiceProvider;
use AntiCmsBuilder\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_service_provider_is_registered()
    {
        $providers = $this->app->getLoadedProviders();
        
        $this->assertArrayHasKey(AntiCmsBuilderServiceProvider::class, $providers);
    }

    public function test_package_is_discoverable()
    {
        $this->assertTrue(class_exists(AntiCmsBuilderServiceProvider::class));
    }

    public function test_commands_are_registered()
    {
        // Check if the command is available in console
        $commands = $this->app['Illuminate\Contracts\Console\Kernel']->all();
        
        $this->assertArrayHasKey('page:build', $commands);
    }
}