<?php

namespace Sausin\LaravelOvh\Tests\Functional;

class SetCORSHeadersTest extends CommandTestCase
{
    public function testSetsDefaultHeadersWhenNoneExist()
    {
        $this->container->shouldReceive('getMetadata')->once()->andReturn([]);
        $this->container->shouldReceive('resetMetadata')->once()->with([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Max-Age' => '3600',
        ]);

        $this->artisan('ovh:set-cors-headers')->assertExitCode(0);
    }

    public function testSetsHeadersWithCustomOriginsAndMaxAge()
    {
        $this->container->shouldReceive('getMetadata')->once()->andReturn([]);
        $this->container->shouldReceive('resetMetadata')->once()->with([
            'Access-Control-Allow-Origin' => 'https://a.example https://b.example',
            'Access-Control-Max-Age' => '600',
        ]);

        $this->artisan('ovh:set-cors-headers', [
            '--origins' => ['https://a.example', 'https://b.example'],
            '--max-age' => '600',
        ])->assertExitCode(0);
    }

    public function testPromptsBeforeOverridingExistingHeaders()
    {
        $this->container->shouldReceive('getMetadata')->once()->andReturn([
            'Access-Control-Allow-Origin' => 'https://existing.example',
        ]);
        $this->container->shouldNotReceive('resetMetadata');

        $this->artisan('ovh:set-cors-headers')
            ->expectsConfirmation('Some CORS Meta keys are already set on the container. Do you want to override them?', 'no')
            ->assertExitCode(0);
    }

    public function testForceOverridesExistingHeaders()
    {
        $this->container->shouldReceive('getMetadata')->once()->andReturn([
            'Access-Control-Allow-Origin' => 'https://existing.example',
        ]);
        $this->container->shouldReceive('resetMetadata')->once()->with([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Max-Age' => '3600',
        ]);

        $this->artisan('ovh:set-cors-headers', ['--force' => true])
            ->assertExitCode(0);
    }

    public function testPreservesTempUrlKeyWhenSettingHeaders()
    {
        $this->container->shouldReceive('getMetadata')->once()->andReturn([
            'Temp-Url-Key' => 'secret',
        ]);
        $this->container->shouldReceive('resetMetadata')->once()->with([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Max-Age' => '3600',
            'Temp-Url-Key' => 'secret',
        ]);

        $this->artisan('ovh:set-cors-headers')->assertExitCode(0);
    }
}
