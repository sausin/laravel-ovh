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
        $url = !empty($this->config->getEndpoint())
            // Allows assigning custom endpoint url
            ? rtrim($this->config->getEndpoint(), '/').'/'
            // If no custom endpoint assigned, use traditional swift v1 endpoint
            : sprintf(
                'https://storage.%s.cloud.ovh.net/v1/AUTH_%s/%s/',
                $this->config->getRegion(),
                $this->config->getProjectId(),
                $this->config->getContainerName()
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
     * @param \DateTimeInterface $expiresAt
     * @param array $options
     * @return string
     */
    public function getTemporaryUrl(string $path, DateTimeInterface $expiresAt, array $options = []): string
    {
        // Ensure Temp URL Key is provided for the Disk
        if (empty($this->config->getTempUrlKey())) {
            throw new \InvalidArgumentException("No Temp URL Key provided for container '".$this->container->name."'");
        }

        // Ensure $path doesn't begin with a slash
        $path = ltrim($path, '/');

        // Get the method
        $method = $options['method'] ?? 'GET';

        // The url on the OVH host
        $codePath = sprintf(
            '/v1/AUTH_%s/%s/%s',
            $this->config->getProjectId(),
            $this->config->getContainerName(),
            $path
        );

        // Body for the HMAC hash
        $body = sprintf("%s\n%s\n%s", $method, $expiresAt->getTimestamp(), $codePath);

        // The actual hash signature
        $signature = hash_hmac('sha1', $body, $this->config->getTempUrlKey());

        // Return signed url
        return sprintf(
            '%s?temp_url_sig=%s&temp_url_expires=%s',
            $this->getEndpoint($path),
            $signature,
            $expiresAt->getTimestamp()
        );
    }

    public function getFormPostSignature(string $path, DateTimeInterface $expiresAt, string $redirect = '', int $maxFileCount = 1, int $maxFileSize = 26214400): string
    {
        // Ensure Temp URL Key is provided for the Disk
        if (empty($this->config->getTempUrlKey())) {
            throw new \InvalidArgumentException("No Temp URL Key provided for container '".$this->container->name."'");
        }

        // check if the 'expires' values is in the past
        if (($expiresAt->getTimestamp() ?? 0) < time()) {
            throw new \InvalidArgumentException("Value of 'expires' cannot be in the past");
        }

        // Ensure $path doesn't begin with a slash
        $path = ltrim($path, '/');

        // The url on the OVH host
        $codePath = sprintf(
            '/v1/AUTH_%s/%s/%s',
            $this->config->getProjectId(),
            $this->config->getContainerName(),
            $path
        );

        // Body for the HMAC hash
        $body = sprintf("%s\n%s\n%s\n%s\n%s", $codePath, $redirect, $maxFileSize, $maxFileCount, $expiresAt->getTimestamp());

        // The actual hash signature
        return hash_hmac('sha1', $body, $this->config->getTempUrlKey());
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
        $data = parent::getWriteData($path, $config);

        if ($config->has('deleteAfter')) {
            $data['deleteAfter'] = $config->get('deleteAfter');
        } elseif ($config->has('deleteAt')) {
            $data['deleteAt'] = $config->get('deleteAt');
        } elseif (!empty($this->config->getDeleteAfter())) {
            $data['deleteAfter'] = $this->config->getDeleteAfter();
        }

        return $data;
    }
}
