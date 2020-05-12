<?php

namespace Sausin\LaravelOvh\Tests\Functional;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Cached\CachedAdapter;
use OpenStack\ObjectStore\v1\Models\Container;
use Orchestra\Testbench\TestCase;
use Sausin\LaravelOvh\OVHServiceProvider;
use Sausin\LaravelOvh\OVHSwiftAdapter;

class CachedAdapterTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();

        $serviceProvider = \Mockery::mock(OVHServiceProvider::class, [$this->app])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $serviceProvider
            ->shouldReceive('makeOpenStackClient->objectStoreV1->getContainer')
            ->with('my-container')
            ->andReturn(\Mockery::mock(Container::class));

        $this->app->register($serviceProvider, true);

        Config::set([
            'filesystems.disks.ovh.driver' => 'ovh',
            'filesystems.disks.ovh.authUrl' => '',
            'filesystems.disks.ovh.projectId' => 'AwesomeProject',
            'filesystems.disks.ovh.region' => 'TestingGround',
            'filesystems.disks.ovh.userDomain' => 'Default',
            'filesystems.disks.ovh.username' => '',
            'filesystems.disks.ovh.password' => '',
            'filesystems.disks.ovh.container' => 'my-container', // To be removed
            'filesystems.disks.ovh.containerName' => 'my-container',
        ]);
    }

    public function testOvhDiskIsNotCached()
    {
        $this->assertInstanceOf(OVHSwiftAdapter::class, Storage::disk('ovh')->getAdapter());
    }

    public function testOvhDiskIsCached()
    {
        Config::set('filesystems.disks.ovh.cache', true);

        /** @var CachedAdapter $ovhDisk */
        $ovhDisk = Storage::disk('ovh')->getAdapter();

        $this->assertInstanceOf(CachedAdapter::class, $ovhDisk);
        $this->assertInstanceOf(OVHSwiftAdapter::class, $ovhDisk->getAdapter());
    }
}
