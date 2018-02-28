<?php

namespace Sausin\LaravelOvh\Tests;

use Mockery;
use Carbon\Carbon;
use League\Flysystem\Config;
use Sausin\LaravelOvh\OVHSwiftAdapter;

class OVHSwiftAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->config = new Config([]);
        $this->urlVars = ['region', 'projectId', 'container', 'meykey'];

        $this->container = Mockery::mock('OpenStack\ObjectStore\v1\Models\Container');

        $this->container->name = 'container-name';
        $this->object = Mockery::mock('OpenStack\ObjectStore\v1\Models\StorageObject');
        $this->adapter = new OVHSwiftAdapter($this->container, $this->urlVars);
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

        $this->assertEquals($url, 'https://storage.region.cloud.ovh.net/v1/AUTH_projectId/container/hello');
    }

    public function testUrlMethod()
    {
        $this->object->shouldNotReceive('retrieve');
        $this->container->shouldNotReceive('getObject');

        $url = $this->adapter->getUrl('hello');

        $this->assertEquals($url, 'https://storage.region.cloud.ovh.net/v1/AUTH_projectId/container/hello');
    }

    public function testTemporaryUrlMethod()
    {
        $this->object->shouldNotReceive('retrieve');
        $this->container->shouldNotReceive('getObject');

        $url = $this->adapter->getTemporaryUrl('hello.jpg', Carbon::now()->addMinutes(10));

        $this->assertNotNull($url);
    }
}
