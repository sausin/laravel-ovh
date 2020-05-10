<?php

namespace Sausin\LaravelOvh;

use Illuminate\Filesystem\Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory as MemoryStore;
use League\Flysystem\Filesystem;
use OpenStack\ObjectStore\v1\Models\Container;
use OpenStack\OpenStack;

class OVHServiceProvider extends ServiceProvider
{
    /** @var OVHConfiguration */
    private OVHConfiguration $config;

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->configureCommands();

        $this->configureStorage();
    }

    /**
     * Configures available commands.
     */
    protected function configureCommands(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Commands\SetTempUrlKey::class,
        ]);
    }

    /**
     * Configures extended filesystem storage for interaction with OVH Object Storage.
     */
    protected function configureStorage(): void
    {
        Storage::extend('ovh', function ($app, array $config) {
            $cache = Arr::pull($config, 'cache');

            // Creates a Configuration instance.
            $this->config = OVHConfiguration::make($config);

            $client = $this->makeOpenStackClient();

            // Get the Object Storage container.
            $container = $client->objectStoreV1()->getContainer($this->config->getContainerName());

            return $this->makeFileSystem($container, $cache);
        });
    }

    /**
     * Creates an OpenStack client instance, needed for interaction with OVH OpenStack.
     *
     * @return OpenStack
     */
    protected function makeOpenStackClient(): OpenStack
    {
        return new OpenStack([
            'authUrl' => $this->config->getAuthUrl(),
            'region' => $this->config->getRegion(),
            'user' => [
                'name' => $this->config->getUsername(),
                'password' => $this->config->getPassword(),
                'domain' => [
                    'name' => $this->config->getUserDomain(),
                ],
            ],
            'scope' => [
                'project' => [
                    'id' => $this->config->getProjectId(),
                ],
            ],
        ]);
    }

    /**
     * Creates a Filesystem instance for interaction with the Object Storage.
     *
     * @param Container $container
     * @param array|bool|null
     * @return Filesystem
     */
    protected function makeFileSystem(Container $container, $cache): Filesystem
    {
        $adapter = new OVHSwiftAdapter($container, $this->config);

        if ($cache) {
            $adapter = new CachedAdapter($adapter, $this->createCacheStore($cache));
        }

        return new Filesystem(
            $adapter,
            [
                'swiftLargeObjectThreshold' => $this->config->getSwiftLargeObjectThreshold(),
                'swiftSegmentSize' => $this->config->getSwiftSegmentSize(),
                'swiftSegmentContainer' => $this->config->getSwiftSegmentContainer(),
            ]
        );
    }

    /**
     * Create a cache store instance.
     *
     * @param array|bool $config
     * @return \League\Flysystem\Cached\CacheInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function createCacheStore($config)
    {
        if ($config === true) {
            return new MemoryStore;
        }

        return new Cache(
            $this->app['cache']->store($config['store']),
            $config['prefix'] ?? 'flysystem',
            $config['expire'] ?? null
        );
    }
}
