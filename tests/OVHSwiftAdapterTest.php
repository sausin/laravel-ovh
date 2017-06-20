<?php

use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Stream;
use League\Flysystem\Config;
use Sausin\LaravelOvh\OVHSwiftAdapter;

class OVHSwiftAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->config = new Config([]);
        $this->urlBasePathVars = ['region', 'projectId', 'container'];
        $this->container = Mockery::mock('OpenStack\ObjectStore\v1\Models\Container');
        $this->container->name = 'container-name';
        $this->object = Mockery::mock('OpenStack\ObjectStore\v1\Models\Object');
        $this->adapter = new OVHSwiftAdapter($this->container, $this->urlBasePathVars);
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
        $this->object->name = 'hello/world';
        $this->object->lastModified = date('Y-m-d');
        $this->object->contentType = 'mimetype';
        $this->object->contentLength = 1234;

        $this->container->shouldNotReceive('getObject');

        $url = $this->adapter->getUrl('hello');

        $this->assertEquals($url, 'https://storage.region.cloud.ovh.net/v1/AUTH_projectId/container/hello');
    }

    // public function testWriteAndUpdateLargeObjectSteams()
    // {
    //     foreach (['write', 'update'] as $method) {
    //         $psrStream = new Stream(Psr7\stream_for('data', ['size' => 104857600]));

    //         $this->container->shouldReceive('createLargeObject')->once()->with([
    //             'name' => 'hello',
    //             'content' => $psrStream
    //         ])->andReturn($this->object);

    //         $response = $this->adapter->$method('hello', $psrStream, $this->config);

    //         $this->assertEquals($response, [
    //             'type' => 'file',
    //             'dirname' => null,
    //             'path' => null,
    //             'timestamp' =>  null,
    //             'mimetype' => null,
    //             'size' => null,
    //         ]);
    //     }
    // }
}
