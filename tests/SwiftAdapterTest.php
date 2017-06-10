<?php

use GuzzleHttp\Psr7\Stream;
use League\Flysystem\Config;
use Sausin\LaravelOvh\SwiftAdapter;

class SwiftAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        
        $this->config = new Config([]);
        $this->container = Mockery::mock('OpenStack\ObjectStore\v1\Models\Container');
        $this->container->name = 'container-name';
        $this->object = Mockery::mock('OpenStack\ObjectStore\v1\Models\Object');
        $this->adapter = new SwiftAdapter($this->container);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testWriteAndUpdate()
    {
        foreach (['write', 'update'] as $method) {
            $this->container->shouldReceive('createObject')->once()->with([
                'name' => 'hello',
                'content' => 'world'
            ])->andReturn($this->object);

            $response = $this->adapter->$method('hello', 'world', $this->config);

            $this->assertEquals($response, [
                'type' => 'file',
                'dirname' => null,
                'path' => null,
                'timestamp' =>  null,
                'mimetype' => null,
                'size' => null,
            ]);
        }
    }

    public function testWriteAndUpdateStream()
    {
        foreach (['writeStream', 'updateStream'] as $method) {
            $stream = fopen('data://text/plain;base64,'.base64_encode('world'), 'r');
            $psrStream = new Stream($stream);

            $this->container->shouldReceive('createObject')->once()->with([
                'name' => 'hello',
                'stream' => $psrStream
            ])->andReturn($this->object);

            $response = $this->adapter->$method('hello', $stream, $this->config);

            $this->assertEquals($response, [
                'type' => 'file',
                'dirname' => null,
                'path' => null,
                'timestamp' =>  null,
                'mimetype' => null,
                'size' => null,
            ]);
        }
    }

    public function testRename()
    {
        $this->object->shouldReceive('retrieve')->once();
        $this->object->shouldReceive('copy')->once()->with([
            'destination' => '/container-name/world'
        ]);
        $this->object->shouldReceive('delete')->once();

        $this->container->shouldReceive('getObject')
            ->once()
            ->with('hello')
            ->andReturn($this->object);

        $response = $this->adapter->rename('hello', 'world');

        $this->assertTrue($response);
    }

    public function testDelete()
    {
        $this->object->shouldReceive('retrieve')->once();
        $this->object->shouldReceive('delete')->once();

        $this->container->shouldReceive('getObject')
            ->once()
            ->with('hello')
            ->andReturn($this->object);

        $response = $this->adapter->delete('hello');

        $this->assertTrue($response);
    }

    public function testDeleteDir()
    {
        $times = rand(1, 10);

        $generator = function() use ($times) {
            for ($i = 1; $i <= $times; $i++) {
                yield $this->object;
            }
        };

        $objects = $generator();

        $this->container->shouldReceive('listObjects')
            ->once()
            ->with([
                'prefix' => 'hello'
            ])
            ->andReturn($objects);

        $this->object->shouldReceive('delete')->times($times);

        $response = $this->adapter->deleteDir('hello');

        $this->assertTrue($response);
    }

    public function testCreateDir()
    {
        $dir = $this->adapter->createDir('hello', $this->config);

        $this->assertEquals($dir, [
            'path' => 'hello'
        ]);
    }

    public function testHas()
    {
        $this->object->shouldReceive('retrieve')->once();

        $this->container
            ->shouldReceive('getObject')
            ->once()
            ->with('hello')
            ->andReturn($this->object);

        $has = $this->adapter->has('hello');

        $this->assertEquals($has, [
            'type' => 'file',
            'dirname' => null,
            'path' => null,
            'timestamp' =>  null,
            'mimetype' => null,
            'size' => null,
        ]);
    }

    public function testRead()
    {
        $stream = Mockery::mock('GuzzleHttp\Psr7\Stream');
        $stream->shouldReceive('close');
        $stream->shouldReceive('getContents')->once()->andReturn('hello world');

        $this->object->shouldReceive('retrieve')->once();
        $this->object->shouldReceive('download')
            ->once()
            ->andReturn($stream);

        $this->container
            ->shouldReceive('getObject')
            ->once()
            ->with('hello')
            ->andReturn($this->object);

        $data = $this->adapter->read('hello');

        $this->assertEquals($data, [
            'type' => 'file',
            'dirname' => null,
            'path' => null,
            'timestamp' =>  null,
            'mimetype' => null,
            'size' => null,
            'contents' => 'hello world'
        ]);
    }

    public function testReadStream()
    {
        $stream = fopen('data://text/plain;base64,'.base64_encode('world'), 'r');
        $psrStream = new Stream($stream);

        $this->object->shouldReceive('retrieve')->once();
        $this->object->shouldReceive('download')
            ->once()
            ->andReturn($psrStream);

        $this->container
            ->shouldReceive('getObject')
            ->once()
            ->with('hello')
            ->andReturn($this->object);

        $data = $this->adapter->readStream('hello');

        $this->assertEquals('world', stream_get_contents($data['stream']));
    }

    public function testListContents()
    {
        $times = rand(1, 10);

        $generator = function() use ($times) {
            for ($i = 1; $i <= $times; $i++) {
                yield $this->object;
            }
        };

        $objects = $generator();

        $this->container->shouldReceive('listObjects')
            ->once()
            ->with([
                'prefix' => 'hello'
            ])
            ->andReturn($objects);

        $contents = $this->adapter->listContents('hello');

        for ($i = 1; $i <= $times; $i++) {
            $data[] = [
                'type' => 'file',
                'dirname' => null,
                'path' => null,
                'timestamp' =>  null,
                'mimetype' => null,
                'size' => null,
            ];
        }

        $this->assertEquals($data, $contents);
    }

    public function testMetadataMethods()
    {
        $methods = [
            'getMetadata',
            'getSize',
            'getMimetype',
            'getTimestamp'
        ];

        foreach ($methods as $method) {
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

            $metadata = $this->adapter->$method('hello');

            $this->assertEquals($metadata, [
                'type' => 'file',
                'dirname' => 'hello',
                'path' => 'hello/world',
                'timestamp' =>  strtotime(date('Y-m-d')),
                'mimetype' => 'mimetype',
                'size' => 1234,
            ]);
        }
    }

    public function testUrlMethod()
    {
    }

    public function testBigObject()
    {
    }
}
