<?php

namespace Sausin\LaravelOvh;

use BadMethodCallException;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use OpenStack\Common\Transport\Utils as TransportUtils;
use OpenStack\Identity\v2\Service;
use OpenStack\OpenStack;

class OVHServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('ovh', function ($app, $config) {
            // check if the config is complete
            $this->checkConfig($config);

            // create the client
            $client = $this->makeClient($config);

            // get the container
            $container = $client->objectStoreV1()->getContainer($config['container']);

            return new Filesystem(
                new OVHSwiftAdapter($container, $this->getVars($config)),
                $this->getLargeObjectConfig($config)
            );
        });
    }

    /**
     * Check that the config is properly setup.
     *
     * @param  array $config
     * @return void|BadMethodCallException
     */
    protected function checkConfig($config)
    {
        // needed keys
        $needKeys = ['server', 'region', 'user', 'pass', 'tenantName', 'projectId', 'container'];

        if (count(array_intersect($needKeys, array_keys($config))) === count($needKeys)) {
            return;
        }

        // if the configuration wasn't complete, throw an exception
        throw new BadMethodCallException('Need following keys '.implode($needKeys, ', '));
    }

    /**
     * Make the client needed for interaction with OVH OpenStack.
     *
     * @param  array $config
     * @return \OpenStack\OpenStack
     */
    protected function makeClient($config)
    {
        // this is needed because default setup of openstack leads to authentication
        // going to wrong path of the auth url as OVH uses deprecated version
        $httpClient = new Client([
            'base_uri' => TransportUtils::normalizeUrl($config['server']),
            'handler'  => HandlerStack::create(),
        ]);

        // setup the client for OpenStack v1
        return new OpenStack([
            'authUrl' => $config['server'],
            'region' => $config['region'],
            'username' => $config['user'],
            'password' => $config['pass'],
            'tenantName' => $config['tenantName'],
            'identityService' => Service::factory($httpClient),
        ]);
    }

    /**
     * Return the config variables required by the adapter.
     *
     * @param  array &$config
     * @return array
     */
    protected function getVars(&$config)
    {
        return [
            'region' => $config['region'],
            'projectId' => $config['projectId'],
            'container' => $config['container'],
            'urlKey' => isset($config['urlKey']) ? $config['urlKey'] : null,
            'endpoint' => isset($config['endpoint']) ? $config['endpoint'] : null,
        ];
    }

    /**
     * Return the config variables required for large object.
     *
     * @param  array &$config
     * @return array
     */
    protected function getLargeObjectConfig(&$config)
    {
        $largeObjConfig = [];

        $largeObjVars = [
            'swiftLargeObjectThreshold',
            'swiftSegmentSize',
            'swiftSegmentContainer',
        ];

        foreach ($largeObjVars as $key) {
            if (isset($config[$key])) {
                $largeObjConfig[$key] = $config[$key];
            }
        }

        return $largeObjConfig;
    }
}
