<?php

namespace Sausin\LaravelOvh\Tests;

use Mockery;
use OpenStack\ObjectStore\v1\Models\Container;
use OpenStack\ObjectStore\v1\Models\StorageObject;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Sausin\LaravelOvh\OVHConfiguration;
use Sausin\LaravelOvh\OVHSwiftAdapter;

class TestCase extends PHPUnitTestCase
{
    /** @var OVHConfiguration */
    protected OVHConfiguration $config;

    /** @var Mockery\LegacyMockInterface|Mockery\MockInterface|Container */
    protected $container;

    /** @var Mockery\LegacyMockInterface|Mockery\MockInterface|StorageObject */
    protected $object;

    /** @var OVHSwiftAdapter */
    protected OVHSwiftAdapter $adapter;

    public function setUp(): void
    {
        $this->config = OVHConfiguration::make([
            'authUrl' => '',
            'projectId' => 'AwesomeProject',
            'region' => 'TestingGround',
            'userDomain' => 'Default',
            'username' => '',
            'password' => '',
            'containerName' => 'my-container',
        ]);

        $this->container = Mockery::mock('OpenStack\ObjectStore\v1\Models\Container');

        $this->container->name = $this->config->getContainerName();
        $this->object = Mockery::mock('OpenStack\ObjectStore\v1\Models\StorageObject');
        $this->adapter = new OVHSwiftAdapter($this->container, $this->config);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
