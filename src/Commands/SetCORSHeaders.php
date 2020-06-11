<?php

namespace Sausin\LaravelOvh\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use League\Flysystem\Cached\CachedAdapter;
use OpenStack\ObjectStore\v1\Models\Container;

class SetCORSHeaders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ovh:set-cors-headers
                            {--disk=ovh : The disk using your OVH container}
                            {--origins=* : The origins to be allowed on the containers (multiple allowed)}
                            {--max-age=3600 : The maximum cache validity of pre-flight requests}
                            {--force : Forcibly set the new headers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set CORS headers on the container to make Form POST signaure work flawlessly';

    /**
     * The Object Storage Container.
     *
     * @var Container
     */
    protected $container;

    /** array */
    protected $containerMeta = [];

    /**
     * Execute the console command.
     *
     * If the '--force' flag is provided, the specified keys will be set on the container.
     * This excludes any 'Temp-Url-Key' already present on the container.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            $adapter = Storage::disk($this->option('disk'))->getAdapter();

            if ($adapter instanceof CachedAdapter) {
                $adapter = $adapter->getAdapter();
            }

            $this->container = $adapter->getContainer();
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return;
        }

        $this->containerMeta = $this->container->getMetadata();

        if ($this->option('force') || $this->askIfShouldOverrideExistingParams()) {
            $this->setHeaders();
        }
    }

    /**
     * If there's no existing Temp URL Key present in the Container, continue.
     *
     * Otherwise, if there's already an existing Temp URL Key present in the
     * Container, the User will be prompted to choose if we should override it
     * or not.
     *
     * @return bool
     */
    protected function askIfShouldOverrideExistingParams(): bool
    {
        $metaKeys = ['Access-Control-Allow-Origin', 'Access-Control-Max-Age'];

        if (count(array_intersect($metaKeys, array_keys($this->containerMeta))) === 0) {
            return true;
        }

        return $this->confirm(
            'Some CORS Meta keys are already set on the container. Do you want to override them?',
            false
        );
    }

    /**
     * Updates the Temp URL Key for the Container.
     *
     * @return void
     */
    protected function setHeaders(): void
    {
        $origins = '*';

        if (count($this->option('origins')) !== 0) {
            $origins = implode(' ', $this->option('origins'));
        }

        $maxAge = $this->option('max-age');
        $meta = ['Access-Control-Allow-Origin' => $origins, 'Access-Control-Max-Age' => $maxAge];

        if (array_key_exists('Temp-Url-Key', $this->containerMeta)) {
            $meta += ['Temp-Url-Key' => $this->containerMeta['Temp-Url-Key']];
        }

        try {
            $this->container->resetMetadata($meta);

            $this->info('CORS meta keys successfully set on the container');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
