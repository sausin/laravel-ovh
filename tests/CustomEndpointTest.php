<?php

namespace Sausin\LaravelOvh\Tests;

use Carbon\Carbon;
use Mockery;
use Sausin\LaravelOvh\OVHConfiguration;

class CustomEndpointTest extends TestCase
{
    public function getConfig()
    {
        return new OVHConfiguration(
            '',
            'projectId',
            'region',
            '',
            '',
            '',
            'container',
            'mykey',
            'http://custom.endpoint',
            null,
            null,
            null
        );
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testUrlConfirmMethod()
    {
        $this->object->shouldReceive('retrieve')->once();
        $this->object->name = 'hello/world';
        $this->object->lastModified = date('Y-m-d');
        $this->object->contentType = 'mimetype';
        $this->object->contentLength = 1234;

        $this->container
            ->shouldReceive('getObject')
            ->once()
            ->with('hello')
            ->andReturn($this->object);

        $url = $this->adapter->getUrlConfirm('hello');

        $this->assertEquals('http://custom.endpoint/hello', $url);
    }

    public function testUrlMethod()
    {
        $this->object->shouldNotReceive('retrieve');
        $this->container->shouldNotReceive('getObject');

        $url = $this->adapter->getUrl('hello');

        $this->assertEquals('http://custom.endpoint/hello', $url);
    }

    public function testTemporaryUrlMethod()
    {
        $this->object->shouldNotReceive('retrieve');
        $this->container->shouldNotReceive('getObject');

        $url = $this->adapter->getTemporaryUrl('hello.jpg', Carbon::now()->addMinutes(10));

        $this->assertNotNull($url);
    }
}
