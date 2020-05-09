<?php

namespace Sausin\LaravelOvh\Tests\Functional;

use League\Flysystem\Config;
use Sausin\LaravelOvh\Tests\TestCase;

class ExpiringObjectsTest extends TestCase
{
    protected Config $flySystemConfig;

    public function setUp(): void
    {
        parent::setUp();

        $this->flySystemConfig = new Config();
    }

    public function testCanBeDeletedAtTimestamp()
    {
        $this->container->shouldReceive('createObject')->once()->with([
            'name' => 'hello',
            'content' => 'world',
            'deleteAt' => 651234,
        ])->andReturn($this->object);

        $this->flySystemConfig->set('deleteAt', 651234);
        $response = $this->adapter->write('hello', 'world', $this->flySystemConfig);

        $this->assertEquals([
            'type' => 'file',
            'dirname' => null,
            'path' => null,
            'timestamp' => null,
            'mimetype' => null,
            'size' => null,
        ], $response);
    }

    public function testCanBeDeletedAfterSpecificTime()
    {
        $this->container->shouldReceive('createObject')->once()->with([
            'name' => 'hello',
            'content' => 'world',
            'deleteAfter' => 60,
        ])->andReturn($this->object);

        $this->flySystemConfig->set('deleteAfter', 60);
        $response = $this->adapter->write('hello', 'world', $this->flySystemConfig);

        $this->assertEquals([
            'type' => 'file',
            'dirname' => null,
            'path' => null,
            'timestamp' => null,
            'mimetype' => null,
            'size' => null,
        ], $response);
    }

    public function testCanBeDeleteAfterSpecificTimeFromGlobalConfig()
    {
        $this->container->shouldReceive('createObject')->once()->with([
            'name' => 'hello',
            'content' => 'world',
            'deleteAfter' => 60 * 60,
        ])->andReturn($this->object);

        $this->config->setDeleteAfter(60 * 60);
        $response = $this->adapter->write('hello', 'world', $this->flySystemConfig);

        $this->assertEquals([
            'type' => 'file',
            'dirname' => null,
            'path' => null,
            'timestamp' => null,
            'mimetype' => null,
            'size' => null,
        ], $response);
    }
}
