<?php

namespace Sausin\LaravelOvh;

use Carbon\Carbon;
use BadMethodCallException;
use OpenStack\ObjectStore\v1\Service;
use OpenStack\Common\Error\BadResponseError;
use OpenStack\ObjectStore\v1\Models\Container;
use Nimbusoft\Flysystem\OpenStack\SwiftAdapter;

class OVHSwiftAdapter extends SwiftAdapter
{
    /**
     * URL base path variables for OVH service
     * the HTTPS url is typically of the format
     * https://storage.[REGION].cloud.ovh.net/v1/AUTH_[PROJECT_ID]/[CONTAINER_NAME].
     * @var array
     */
    protected $urlVars;

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

        $urlBasePath = sprintf(
            'https://storage.%s.cloud.ovh.net/v1/AUTH_%s/%s/',
            $this->urlVars[0],
            $this->urlVars[1],
            $this->urlVars[2]
        );

        return $urlBasePath.$path;
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

        $urlBasePath = sprintf(
            'https://storage.%s.cloud.ovh.net/v1/AUTH_%s/%s/',
            $this->urlVars[0],
            $this->urlVars[1],
            $this->urlVars[2]
        );

        return $urlBasePath.$path;
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
            $this->urlVars[1],
            $this->urlVars[2],
            $path
        );

        // body for the HMAC hash
        $body = sprintf("%s\n%s\n%s", $method, $expiresAt, $codePath);

        // the actual hash signature
        $signature = hash_hmac('sha1', $body, $this->urlVars[3]);

        // return the url
        return sprintf(
            '%s%s?temp_url_sig=%s&temp_url_expires=%s',
            sprintf('https://storage.%s.cloud.ovh.net', $this->urlVars[0]),
            $codePath,
            $signature,
            $expiresAt
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
        if (! is_array($this->urlVars) || count($this->urlVars) !== 4) {
            throw new BadMethodCallException('Insufficient Url Params', 1);
        }
    }
}
