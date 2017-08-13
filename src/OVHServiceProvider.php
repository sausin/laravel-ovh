<?php

namespace Sausin\LaravelOvh;

use GuzzleHttp\Client;
use OpenStack\OpenStack;
use GuzzleHttp\HandlerStack;
use League\Flysystem\Filesystem;
use OpenStack\Identity\v2\Service;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use OpenStack\Common\Transport\Utils as TransportUtils;

class OVHServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('ovh', function ($app, $config) {
            // this is needed because default setup of openstack leads to authentication
            // going to wrong path of the auth url as OVH uses deprecated version
            $httpClient = new Client([
                'base_uri' => TransportUtils::normalizeUrl($config['server']),
                'handler'  => HandlerStack::create(),
            ]);

            // setup the client for OpenStack v1
            $client = new OpenStack([
                'authUrl' => $config['server'],
                'region' => $config['region'],
                'username' => $config['user'],
                'password' => $config['pass'],
                'tenantName' => $config['tenantName'],
                'identityService' => Service::factory($httpClient),
            ]);

            // get the container
            $container = $client->objectStoreV1()->getContainer($config['container']);

            // provide the url generating variables
            $urlVars = [
                $config['region'],
                $config['projectId'],
                $config['container'],
                isset($config['urlKey']) ? $config['urlKey'] : null,
            ];

            return new Filesystem(new OVHSwiftAdapter($container, $urlVars));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Filesystem::class];
    }
}
