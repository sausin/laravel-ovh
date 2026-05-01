<?php

namespace Sausin\LaravelOvh\Tests\Functional;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Mockery;
use OpenStack\ObjectStore\v1\Models\Container;
use Orchestra\Testbench\TestCase;
use Sausin\LaravelOvh\OVHServiceProvider;
use Sausin\LaravelOvh\OVHSwiftAdapter;

abstract class CommandTestCase extends TestCase
{
    /** @var Mockery\MockInterface&Container */
    protected $container;

    /** @var Mockery\MockInterface&OVHSwiftAdapter */
    protected $adapter;

    protected function getPackageProviders($app): array
    {
        return [OVHServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('filesystems.disks.ovh', [
            'driver' => 'ovh',
            'authUrl' => '',
            'projectId' => 'AwesomeProject',
            'region' => 'TestingGround',
            'userDomain' => 'Default',
            'username' => '',
            'password' => '',
            'containerName' => 'my-container',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = Mockery::mock(Container::class);
        $this->adapter = Mockery::mock(OVHSwiftAdapter::class);
        $this->adapter->shouldReceive('getContainer')->andReturn($this->container);

        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('getAdapter')->andReturn($this->adapter);

        Storage::shouldReceive('disk')->with('ovh')->andReturn($disk);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
