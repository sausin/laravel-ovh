<?php

namespace Sausin\LaravelOvh\Tests\Functional;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Mockery;
use OpenStack\ObjectStore\v1\Models\Container;
use OpenStack\ObjectStore\v1\Service as ObjectStoreService;
use OpenStack\OpenStack;
use Orchestra\Testbench\TestCase;
use Sausin\LaravelOvh\OVHServiceProvider;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [TestableOVHServiceProvider::class];
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

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // Regression: in Laravel 13 FilesystemManager::extend() rebinds the closure's
    // $this to the manager itself, so the provider must route through a captured
    // $provider variable. Resolving the disk exercises the registered closure end-to-end.
    public function testCanResolveOvhDiskThroughStorageExtend(): void
    {
        $disk = Storage::disk('ovh');

        $this->assertInstanceOf(FilesystemAdapter::class, $disk);
    }

    public function testCreateDriverReturnsFilesystemAdapter(): void
    {
        $provider = new TestableOVHServiceProvider($this->app);

        $disk = $provider->createDriver([
            'driver' => 'ovh',
            'authUrl' => '',
            'projectId' => 'AwesomeProject',
            'region' => 'TestingGround',
            'userDomain' => 'Default',
            'username' => '',
            'password' => '',
            'containerName' => 'my-container',
        ]);

        $this->assertInstanceOf(FilesystemAdapter::class, $disk);
    }
}

class TestableOVHServiceProvider extends OVHServiceProvider
{
    protected function makeOpenStackClient(): OpenStack
    {
        $container = Mockery::mock(Container::class);

        $objectStore = Mockery::mock(ObjectStoreService::class);
        $objectStore->shouldReceive('getContainer')
            ->andReturn($container);

        $client = Mockery::mock(OpenStack::class);
        $client->shouldReceive('objectStoreV1')
            ->andReturn($objectStore);

        return $client;
    }
}
