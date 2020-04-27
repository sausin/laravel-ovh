<?php

namespace Sausin\LaravelOvh\Tests;

use Carbon\Carbon;
use League\Flysystem\Config;
use Mockery;
use OpenStack\ObjectStore\v1\Models\Container;
use OpenStack\ObjectStore\v1\Models\StorageObject;
use Sausin\LaravelOvh\OVHSwiftAdapter;

class CustomEndpointTest extends \PHPUnit\Framework\TestCase
{
    /** @var Mockery\LegacyMockInterface|Mockery\MockInterface|Container */
    private $container;

    /** @var Mockery\LegacyMockInterface|Mockery\MockInterface|StorageObject */
    private $object;

    /** @var OVHSwiftAdapter */
    private $adapter;

    public function setUp()
    {
        $urlVars = [
            'region' => 'region',
            'projectId' => 'projectId',
            'container' => 'container',
            'urlKey' => 'meykey',
            'endpoint' => 'http://custom.endpoint',
        ];

        $this->container = Mockery::mock('OpenStack\ObjectStore\v1\Models\Container');

        $this->container->name = 'container-name';
        $this->object = Mockery::mock('OpenStack\ObjectStore\v1\Models\StorageObject');
        $this->adapter = new OVHSwiftAdapter($this->container, $urlVars);
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
