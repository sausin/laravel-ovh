<?php

namespace Sausin\LaravelOvh;

use BadMethodCallException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
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

            return new Filesystem(new OVHSwiftAdapter($container, $this->getVars($config)));
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
        $needKeys = ['server', 'region', 'user', 'pass', 'userDomain', 'projectId', 'container'];

        if (count(array_intersect($needKeys, array_keys($config))) === count($needKeys)) {
            return;
        }

        // if the configuration wasn't complete, throw an exception
        throw new BadMethodCallException('Need following keys '.implode(', ', $needKeys));
    }

    /**
     * Make the client needed for interaction with OVH OpenStack.
     *
     * @param  array $config
     * @return \OpenStack\OpenStack
     */
    protected function makeClient($config)
    {
        // setup the client for OpenStack
        return new OpenStack([
            'authUrl' => $config['server'],
            'region' => $config['region'],
            'user' => [
                'name' => $config['user'],
                'password' => $config['pass'],
                'domain' => [
                    'name' => $config['userDomain'],
                ],
            ],
            'scope' => [
                'project' => [
                    'id' => $config['projectId'],
                ],
            ],
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
        $largeObjectConfig = [];

        $passThroughConfig = [
            'swiftLargeObjectThreshold',
            'swiftSegmentSize',
            'swiftSegmentContainer',
        ];

        foreach ($passThroughConfig as $key) {
            if (isset($config[$key])) {
                $largeObjectConfig[$key] = $config[$key];
            }
        }

        return [
            'region' => $config['region'],
            'projectId' => $config['projectId'],
            'container' => $config['container'],
            'urlKey' => isset($config['urlKey']) ? $config['urlKey'] : null,
            'endpoint' => isset($config['endpoint']) ? $config['endpoint'] : null,
        ] + $largeObjectConfig;
    }
}
