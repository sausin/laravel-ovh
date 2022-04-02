<?php

namespace Sausin\LaravelOvh;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
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
            Commands\SetCORSHeaders::class,
        ]);
    }

    /**
     * Configures extended filesystem storage for interaction with OVH Object Storage.
     */
    protected function configureStorage(): void
    {
        Storage::extend('ovh', function ($app, array $config) {
            // Creates a Configuration instance.
            $this->config = OVHConfiguration::make($config);

            $client = $this->makeOpenStackClient();

            // Get the Object Storage container.
            $container = $client->objectStoreV1()->getContainer($this->config->getContainerName());

            return $this->makeFileSystem($container);
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
     * @return Filesystem
     */
    protected function makeFileSystem(Container $container): Filesystem
    {
        return new Filesystem(
            new OVHSwiftAdapter($container, $this->config, $this->config->getPrefix()),
            array_filter([
                'swiftLargeObjectThreshold' => $this->config->getSwiftLargeObjectThreshold(),
                'swiftSegmentSize' => $this->config->getSwiftSegmentSize(),
                'swiftSegmentContainer' => $this->config->getSwiftSegmentContainer(),
            ])
        );
    }
}
