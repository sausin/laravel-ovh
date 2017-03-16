<?php

namespace Sausin\LaravelOvh;

use Storage;
use GuzzleHttp\Client;
use OpenStack\OpenStack;
use GuzzleHttp\HandlerStack;
use League\Flysystem\Filesystem;
use OpenStack\Identity\v2\Service;
use Illuminate\Support\ServiceProvider;
use Sausin\LaravelOvh\SwiftAdapter;
use OpenStack\Common\Transport\Utils as TransportUtils;

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
            // this is needed because default setup of openstack leads to authentication
            // going to wrong path of the auth url as OVH uses deprecated version
            $httpClient = new Client([
                'base_uri' => TransportUtils::normalizeUrl($config['server']),
                'handler'  => HandlerStack::create(),
            ]);

            $client = new OpenStack([
                'authUrl' => $config['server'],
                'region' => $config['region'],
                'username' => $config['user'],
                'password' => $config['pass'],
                'tenantName' => $config['tenantName'],
                'identityService' => Service::factory($httpClient),
            ]);

            $container = $client->objectStoreV1()->getContainer($config['container']);

            $urlBasePathVars = [$config['region'], $config['projectId'], $config['container']];

            return new Filesystem(new SwiftAdapter($container, $urlBasePathVars));
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

