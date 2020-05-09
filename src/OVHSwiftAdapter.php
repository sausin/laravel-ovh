<?php

namespace Sausin\LaravelOvh;

use DateTimeInterface;
use League\Flysystem\Config;
use Nimbusoft\Flysystem\OpenStack\SwiftAdapter;
use OpenStack\Common\Error\BadResponseError;
use OpenStack\ObjectStore\v1\Models\Container;

class OVHSwiftAdapter extends SwiftAdapter
{
    /** @var OVHConfiguration */
    protected OVHConfiguration $config;

    /**
     * OVHSwiftAdapter constructor.
     *
     * @param Container $container
     * @param OVHConfiguration $config
     * @param string|null $prefix
     */
    public function __construct(Container $container, OVHConfiguration $config, ?string $prefix = null)
    {
        parent::__construct($container, $prefix);

        $this->config = $config;
    }

    /**
     * Gets the endpoint url of the bucket.
     *
     * @param string|null $path
     * @return string
     */
    protected function getEndpoint(?string $path = null): string
    {
        $url = !empty($this->config->endpoint)
            // Allows assigning custom endpoint url
            ? rtrim($this->config->endpoint, '/').'/'
            // If no custom endpoint assigned, use traditional swift v1 endpoint
            : sprintf(
                'https://storage.%s.cloud.ovh.net/v1/AUTH_%s/%s/',
                $this->config->region,
                $this->config->projectId,
                $this->config->container
            );

        if (!empty($path)) {
            $url .= ltrim($path, '/');
        }

        return $url;
    }

    /**
     * Custom function to comply with the Storage::url() function in laravel
     * without checking the existence of a file (faster).
     *
     * @param string $path
     * @return string
     */
    public function getUrl($path)
    {
        return $this->getEndpoint($path);
    }

    /**
     * Custom function to get an url with confirmed file existence.
     *
     * @param string $path
     * @return string
     * @throws BadResponseError
     */
    public function getUrlConfirm($path): string
    {
        // check if object exists
        try {
            $this->has($path);
        } catch (BadResponseError $e) {
            throw $e;
        }

        return $this->getEndpoint($path);
    }

    /**
     * Generate a temporary URL for private containers.
     *
     * @param string $path
     * @param DateTimeInterface $expiresAt
     * @param array $options
     * @return string
     */
    public function getTemporaryUrl(string $path, DateTimeInterface $expiresAt, array $options = []): string
    {
        // Ensure $path doesn't begin with a slash
        $path = ltrim($path, '/');

        // Get the method
        $method = $options['method'] ?? 'GET';

        // The url on the OVH host
        $codePath = sprintf(
            '/v1/AUTH_%s/%s/%s',
            $this->config->projectId,
            $this->config->container,
            $path
        );

        // Body for the HMAC hash
        $body = sprintf("%s\n%s\n%s", $method, $expiresAt->getTimestamp(), $codePath);

        // The actual hash signature
        $signature = hash_hmac('sha1', $body, $this->config->tempUrlKey);

        // Return signed url
        return sprintf(
            '%s?temp_url_sig=%s&temp_url_expires=%s',
            $this->getEndpoint($path),
            $signature,
            $expiresAt->getTimestamp()
        );
    }

    /**
     * Expose the container to allow for modification to metadata.
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Include support for object deletion.
     *
     * @param string $path
     * @param Config $config
     * @return array
     * @see SwiftAdapter
     */
    protected function getWriteData($path, $config): array
    {
        $data = ['name' => $path];

        if ($config->has('deleteAfter')) {
            $data['deleteAfter'] = $config->get('deleteAfter');
        } elseif ($config->has('deleteAt')) {
            $data['deleteAt'] = $config->get('deleteAt');
        } elseif (!empty($this->config->deleteAfter)) {
            $data['deleteAfter'] = $this->config->deleteAfter;
        }

        return $data;
    }
}
