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
        $deleteAt = new \DateTime('2012-12-21');

        $this->container->shouldReceive('createObject')->once()->with([
            'name' => 'hello',
            'content' => 'world',
            'deleteAt' => $deleteAt->getTimestamp(),
        ])->andReturn($this->object);

        $this->flySystemConfig = $this->flySystemConfig->extend([
            'deleteAt' => $deleteAt->getTimestamp(),
        ]);

        $this->adapter->write('hello', 'world', $this->flySystemConfig);

        // Prevent "no assertion error", we're just checking that the deleteAt is correctly passed to the container
        $this->assertTrue(true);
    }

    public function testCanBeDeletedAfterSpecificTime()
    {
        $this->container->shouldReceive('createObject')->once()->with([
            'name' => 'hello',
            'content' => 'world',
            'deleteAfter' => 60,
        ])->andReturn($this->object);

        $this->flySystemConfig = $this->flySystemConfig->extend([
            'deleteAfter' => 60,
        ]);

        $this->adapter->write('hello', 'world', $this->flySystemConfig);

        // Prevent "no assertion error", we're just checking that the deleteAfter is correctly passed to the container
        $this->assertTrue(true);
    }

    public function testCanBeDeleteAfterSpecificTimeFromGlobalConfig()
    {
        $this->container->shouldReceive('createObject')->once()->with([
            'name' => 'hello',
            'content' => 'world',
            'deleteAfter' => 1800,
        ])->andReturn($this->object);

        $this->config->setDeleteAfter(1800);

        $this->adapter->write('hello', 'world', $this->flySystemConfig);

        // Prevent "no assertion error", we're just checking that the deleteAfter is correctly passed to the container
        $this->assertTrue(true);
    }
}
