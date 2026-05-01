<?php

namespace Sausin\LaravelOvh\Tests\Functional;

use Mockery;

class SetTempUrlKeyTest extends CommandTestCase
{
    public function testSetsProvidedKeyWhenNoneExists()
    {
        $this->container->shouldReceive('getMetadata')->once()->andReturn([]);
        $this->container->shouldReceive('resetMetadata')->once()->with(['Temp-Url-Key' => 'my-key']);

        $this->artisan('ovh:set-temp-url-key', ['--key' => 'my-key'])
            ->assertExitCode(0);
    }

    public function testGeneratesRandomKeyWhenNoneProvided()
    {
        $this->container->shouldReceive('getMetadata')->once()->andReturn([]);
        $this->container->shouldReceive('resetMetadata')->once()->with(Mockery::on(function ($meta) {
            return isset($meta['Temp-Url-Key']) && strlen($meta['Temp-Url-Key']) === 128;
        }));

        $this->artisan('ovh:set-temp-url-key')->assertExitCode(0);
    }

    public function testPromptsBeforeOverridingExistingKey()
    {
        $this->container->shouldReceive('getMetadata')->once()->andReturn(['Temp-Url-Key' => 'existing']);
        $this->container->shouldNotReceive('resetMetadata');

        $this->artisan('ovh:set-temp-url-key', ['--key' => 'new'])
            ->expectsConfirmation('A Temp URL Key already exists in your container, would you like to override it?', 'no')
            ->assertExitCode(0);
    }

    public function testForceOverridesExistingKey()
    {
        $this->container->shouldReceive('getMetadata')->once()->andReturn(['Temp-Url-Key' => 'existing']);
        $this->container->shouldReceive('resetMetadata')->once()->with(['Temp-Url-Key' => 'forced']);

        $this->artisan('ovh:set-temp-url-key', ['--key' => 'forced', '--force' => true])
            ->assertExitCode(0);
    }

    public function testPreservesCorsMetaWhenSettingTempUrlKey()
    {
        $this->container->shouldReceive('getMetadata')->once()->andReturn([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Max-Age' => '3600',
        ]);
        $this->container->shouldReceive('resetMetadata')->once()->with([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Max-Age' => '3600',
            'Temp-Url-Key' => 'my-key',
        ]);

        $this->artisan('ovh:set-temp-url-key', ['--key' => 'my-key'])
            ->assertExitCode(0);
    }
}
