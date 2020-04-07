<?php

namespace Sausin\LaravelOvh;

use BadMethodCallException;
use Carbon\Carbon;
use Nimbusoft\Flysystem\OpenStack\SwiftAdapter;
use OpenStack\Common\Error\BadResponseError;
use OpenStack\ObjectStore\v1\Models\Container;

class OVHSwiftAdapter extends SwiftAdapter
{
    /**
     * URL base path variables for OVH service
     * the HTTPS url is typically of the format
     * https://storage.[REGION].cloud.ovh.net/v1/AUTH_[PROJECT_ID]/[CONTAINER_NAME].
     * @var array
     */
    protected $urlVars;

    /** Variables from the Filesystem class will be temporarily stored here */
    protected $specialParams;

    /**
     * Constructor.
     *
     * @param Container $container
     * @param array     $urlVars
     * @param string    $prefix
     */
    public function __construct(Container $container, $urlVars = [], $prefix = null)
    {
        parent::__construct($container, $prefix);

        $this->urlVars = $urlVars;
    }

    /**
     * Custom function to comply with the Storage::url() function in laravel
     * without checking the existence of a file (faster).
     *
     * @param  string $path
     * @return string
     */
    public function getUrl($path)
    {
        $this->checkParams();

        return $this->getEndpoint() . $path;
    }

    /**
     * Custom function to get a url with confirmed file existence.
     *
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

        $this->checkParams();

        return $this->getEndpoint() . $path;
    }

    /**
     * Generate a temporary URL for private containers.
     *
     * @param  string   $path
     * @param  Carbon   $expiration
     * @param  array    $options
     * @return string
     */
    public function getTemporaryUrl($path, $expiration, $options = [])
    {
        $this->checkParams();

        // expiry is relative to current time
        $expiresAt = $expiration instanceof Carbon ? $expiration->timestamp : (int) (time() + 60 * 60);

        // get the method
        $method = isset($options['method']) ? $options['method'] : 'GET';

        // the url on the OVH host
        $codePath = sprintf(
            '/v1/AUTH_%s/%s/%s',
            $this->urlVars['projectId'],
            $this->urlVars['container'],
            $path
        );

        // body for the HMAC hash
        $body = sprintf("%s\n%s\n%s", $method, $expiresAt, $codePath);

        // the actual hash signature
        $signature = hash_hmac('sha1', $body, $this->urlVars['urlKey']);

        // return the url
        return sprintf(
            '%s?temp_url_sig=%s&temp_url_expires=%s',
            $this->getEndpoint() . $path,
            $signature,
            $expiresAt
        );
    }

    /**
     * Gets the endpoint url of the bucket.
     *
     * @return string
     */
    protected function getEndpoint()
    {
        $this->checkParams();

        return isset($this->urlVars['endpoint'])
            // allows assigning custom endpoint url
            ? rtrim($this->urlVars['endpoint'], '/') . '/'
            // if no custom endpoint assigned, use traditional swift v1 endpoint
            : sprintf(
                'https://storage.%s.cloud.ovh.net/v1/AUTH_%s/%s/',
                $this->urlVars['region'],
                $this->urlVars['projectId'],
                $this->urlVars['container']
            );
    }

    /**
     * Check if the url support variables have
     * been correctly defined.
     *
     * @return void|BadMethodCallException
     */
    protected function checkParams()
    {
        $needKeys = ['region', 'projectId', 'container', 'urlKey', 'endpoint'];

        if (! is_array($this->urlVars) || count(array_intersect($needKeys, array_keys($this->urlVars))) !== count($needKeys)) {
            throw new BadMethodCallException('Insufficient Url Params', 1);
        }
    }

    /**
     * Include support for object deletion.
     *
     * @param string $path
     * @see Nimbusoft\Flysystem\OpenStack
     *
     * @return array
     */
    protected function getWriteData($path, $config)
    {
        $data = ['name' => $path];

        if ($config->has('deleteAfter')) {
            return $data += ['deleteAfter' => $config->get('deleteAfter')];
        } elseif ($config->has('deleteAt')) {
            return $data += ['deleteAt' => $config->get('deleteAt')];
        }

        return $data;
    }

    /**
     * Expose the container to allow for modification to metadata
     *
     * @return \OpenStack\ObjectStore\v1\Models\Container;
     */
    public function getContainer()
    {
        return $this->container;
    }
}
