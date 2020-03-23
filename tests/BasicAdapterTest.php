<?php

namespace Sausin\LaravelOvh\Tests;

use Carbon\Carbon;
use League\Flysystem\Config;
use Mockery;
use Sausin\LaravelOvh\OVHSwiftAdapter;

class BasicAdapterTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->config = new Config([]);
        $this->urlVars = [
            'region' => 'region',
            'projectId' => 'projectId',
            'container' => 'container',
            'urlKey' => 'meykey',
            'endpoint' => null,
        ];

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

    public function testAutoDeleteObjectsWork()
    {
        // test for deleteAt property
        $this->container->shouldReceive('createObject')->once()->with([
            'name' => 'hello',
            'content' => 'world',
            'deleteAt' => 651234
        ])->andReturn($this->object);

        $this->config->set('deleteAt', 651234);
        $response = $this->adapter->write('hello', 'world', $this->config);

        $this->assertEquals($response, [
            'type' => 'file',
            'dirname' => null,
            'path' => null,
            'timestamp' =>  null,
            'mimetype' => null,
            'size' => null,
        ]);

        // test for deleteAfter property
        $this->container->shouldReceive('createObject')->once()->with([
            'name' => 'hello',
            'content' => 'world',
            'deleteAfter' => 60
        ])->andReturn($this->object);

        $this->config->set('deleteAfter', 60);
        $response = $this->adapter->write('hello', 'world', $this->config);

        $this->assertEquals($response, [
            'type' => 'file',
            'dirname' => null,
            'path' => null,
            'timestamp' =>  null,
            'mimetype' => null,
            'size' => null,
        ]);
    }

    public function testTemporaryUrlMethod()
    {
        $this->object->shouldNotReceive('retrieve');
        $this->container->shouldNotReceive('getObject');

        $url = $this->adapter->getTemporaryUrl('hello.jpg', Carbon::now()->addMinutes(10));

        $this->assertNotNull($url);
    }
}
