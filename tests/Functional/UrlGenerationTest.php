<?php

namespace Sausin\LaravelOvh\Tests\Functional;

use DateInterval;
use DateTime;
use Sausin\LaravelOvh\Tests\TestCase;

class UrlGenerationTest extends TestCase
{
    public function testCanGenerateUrl()
    {
        $this->object->shouldNotReceive('retrieve', 'getObject');

        $url = $this->adapter->getUrl('hello');

        $this->assertEquals('https://storage.TestingGround.cloud.ovh.net/v1/AUTH_AwesomeProject/my-container/hello', $url);
    }

    public function testCanGenerateUrlOnCustomEndpoint()
    {
        $this->config->setEndpoint('http://custom.endpoint');

        $this->object->shouldNotReceive('retrieve', 'getObject');

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

        $this->assertEquals('https://storage.TestingGround.cloud.ovh.net/v1/AUTH_AwesomeProject/my-container/hello', $url);
    }

    public function testCanGenerateUrlWithFileConfirmationOnCustomEndpoint()
    {
        $this->config->setEndpoint('http://custom.endpoint');

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
        $this->config->setTempUrlKey('my-key');

        $this->object->shouldNotReceive('retrieve', 'getObject');

        $url = $this->adapter->getTemporaryUrl('hello.jpg', new DateTime('2004-09-22'));

        $this->assertNotNull($url);
    }

    public function testCanGenerateTemporaryUrlOnCustomEndpoint()
    {
        $this->config
            ->setEndpoint('http://custom.endpoint')
            ->setTempUrlKey('my-key');

        $this->object->shouldNotReceive('retrieve', 'getObject');

        $url = $this->adapter->getTemporaryUrl('hello.jpg', new DateTime('2015-10-21'));

        $this->assertNotNull($url);
    }

    public function testTemporaryUrlWillFailIfNoKeyProvided()
    {
        $this->expectException('InvalidArgumentException');

        $this->adapter->getTemporaryUrl('hello.jpg', new DateTime('1979-06-13'));
    }

    public function testCanGenerateFormPostSignature()
    {
        $this->config->setTempUrlKey('my-key');

        $this->object->shouldNotReceive('retrieve', 'getObject');

        $signature = $this->adapter->getFormPostSignature('images', (new DateTime())->add(new DateInterval('PT5M')));

        $this->assertNotNull($signature);
    }

    public function testFormPostSignatureWillFailIfNoKeyProvided()
    {
        $this->expectException('InvalidArgumentException');

        $this->adapter->getFormPostSignature('images', new DateTime());
    }

    public function testFormPostWillFailIfExpirationIsNotInTheFuture()
    {
        $this->config->setTempUrlKey('my-key');

        $this->expectException('InvalidArgumentException');

        $this->adapter->getFormPostSignature('images', new DateTime('2010-07-28'));
    }
}
