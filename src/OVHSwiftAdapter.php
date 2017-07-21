<?php

namespace Sausin\LaravelOvh;

use GuzzleHttp\Psr7\Stream;
use League\Flysystem\Config;
use GuzzleHttp\Psr7\LimitStream;
use OpenStack\Common\Error\BadResponseError;
use OpenStack\ObjectStore\v1\Models\Container;
use Nimbusoft\Flysystem\OpenStack\SwiftAdapter;

class OVHSwiftAdapter extends SwiftAdapter
{
    /**
     * URL base path variables for OVH service
     * the HTTPS url is typically of the format
     * https://storage.[REGION].cloud.ovh.net/v1/AUTH_[PROJECT_ID]/[CONTAINER_NAME]
     * @var array
     */
    protected $urlBasePathVars;

    /**
     * Constructor
     *
     * @param Container $container
     * @param string    $prefix
     */
    public function __construct(Container $container, $urlBasePathVars = [], $prefix = null)
    {
        $this->setPathPrefix($prefix);
        $this->container = $container;

        $this->urlBasePathVars = $urlBasePathVars;
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config, $size = 0)
    {
        $path = $this->applyPathPrefix($path);
        
        $data = ['name' => $path];
        $type = 'content';

        if (is_a($contents, 'GuzzleHttp\Psr7\Stream')) {
            $type = 'stream';
        }
        
        $data[$type] = $contents;

        if ($type === 'stream' && $size > 314572800) {
            // set the segment size to 100MB
            // as suggested in OVH docs
            $data['segmentSize'] = 104857600;
            $data['segmentContainer'] = $this->container->name;

            $response = $this->container->createLargeObject($data);
        } else {
            $response = $this->container->createObject($data);
        }

        return $this->normalizeObject($response);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->write($path, new Stream($resource), $config, fstat($resource)['size']);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->write($path, new Stream($resource), $config, fstat($resource)['size']);
    }
    
    /**
     * Custom function to comply with the Storage::url() function
     * @param  string $path
     * @return string
     */
    public function getUrl($path)
    {
        if (!$this->urlBasePathVars) {
            throw new \Exception("Empty array", 1);
        }
        
        $urlBasePath = sprintf(
            'https://storage.%s.cloud.ovh.net/v1/AUTH_%s/%s/',
            $this->urlBasePathVars[0],
            $this->urlBasePathVars[1],
            $this->urlBasePathVars[2]
        );

        return $urlBasePath . $path;
    }
    
    /**
     * Custom function to comply with the Storage::url() function
     * @param  string $path
     * @return string
     */
    public function getUrlConfirm($path)
    {
        // check if object exists
        try {
            $this->getTimestamp($path);
        } catch (BadResponseError $e) {
            throw $e;
        }

        if (!$this->urlBasePathVars) {
            throw new \Exception("Empty array", 1);
        }

        $urlBasePath = sprintf(
            'https://storage.%s.cloud.ovh.net/v1/AUTH_%s/%s/',
            $this->urlBasePathVars[0],
            $this->urlBasePathVars[1],
            $this->urlBasePathVars[2]
        );

        return $urlBasePath . $path;
    }
}
