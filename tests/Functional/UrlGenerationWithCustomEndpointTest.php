<?php

namespace Sausin\LaravelOvh\Tests\Functional;

use Carbon\Carbon;
use Sausin\LaravelOvh\Tests\TestCase;

class UrlGenerationWithCustomEndpointTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->config->setEndpoint('http://custom.endpoint');
    }

    public function testCanGenerateUrl()
    {
        $this->object->shouldNotReceive('retrieve');
        $this->container->shouldNotReceive('getObject');

        $url = $this->adapter->getUrl('hello');

        $this->assertEquals('http://custom.endpoint/hello', $url);
    }

    public function testCanGenerateUrlWithFileConfirmation()
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

    public function testCanGenerateTemporaryUrl()
    {
        $this->object->shouldNotReceive('retrieve');
        $this->container->shouldNotReceive('getObject');

        $url = $this->adapter->getTemporaryUrl('hello.jpg', Carbon::now()->addMinutes(10));

        $this->assertNotNull($url);
    }
}
