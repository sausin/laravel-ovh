<?php

namespace Sausin\LaravelOvh\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use OpenStack\ObjectStore\v1\Models\Container;

class SetTempUrlKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ovh:set-temp-url-key
                            {--disk=ovh : The disk using your OVH container}
                            {--key= : The key you want to set up on your container}
                            {--force : Forcibly set a new key on the container}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set temp url key on the private container, making the use of Storage::temporaryUrl() possible';

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
     * If the '--force' flag is provided, a new Temp URL Key will be generated and
     * forcefully set in the Container's metadata, overriding any existing keys.
     *
     * If the command is not forced and there's an existing key, the User will be
     * prompted to override the existing keys.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            $disk = $this->getDisk();

            $adapter = Storage::disk($disk)->getAdapter();

            $this->container = $adapter->getContainer();
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return;
        }

        $this->containerMeta = $this->container->getMetadata();

        if ($this->option('force') || $this->askIfShouldOverrideExistingKey()) {
            $this->setContainerKey();
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
    protected function askIfShouldOverrideExistingKey(): bool
    {
        if (!array_key_exists('Temp-Url-Key', $this->containerMeta)) {
            return true; // Yeah, override the non-existing key.
        }

        return $this->confirm(
            'A Temp URL Key already exists in your container, would you like to override it?',
            false
        );
    }

    /**
     * Generates a random Temp URL Key.
     *
     * For more details, please refer to:
     *  - https://docs.ovh.com/gb/en/public-cloud/share_an_object_via_a_temporary_url/#generate-the-temporary-address-tempurl
     *
     * @return string
     */
    protected function getRandomKey(): string
    {
        return hash('sha512', time());
    }

    /**
     * Updates the Temp URL Key for the Container.
     *
     * @return void
     */
    protected function setContainerKey(): void
    {
        $key = $this->option('key') ?? $this->getRandomKey();
        $meta = $this->getMeta();

        try {
            $this->container->resetMetadata($meta + ['Temp-Url-Key' => $key]);

            $this->info('Successfully set Temp URL Key to: '.$key);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * If other meta keys exist, get them.
     *
     * @return array
     */
    protected function getMeta(): array
    {
        $meta = [];
        $metaKeys = ['Access-Control-Allow-Origin', 'Access-Control-Max-Age'];

        foreach ($metaKeys as $key) {
            if (array_key_exists($key, $this->containerMeta)) {
                $meta += [$key => $this->containerMeta[$key]];
            }
        }

        return $meta;
    }

    /**
     * Check if selected disk is correct. If not, provide options to user.
     *
     * @return string
     */
    public function getDisk(): string
    {
        $available = array_keys(array_filter(Config::get('filesystems.disks'), function ($d) {
            return $d['driver'] === 'ovh';
        }));

        $selected = $this->option('disk');

        if (in_array($selected, $available)) {
            return $selected;
        }

        return $this->choice(
            'Selected disk not correct. Please choose from below options:',
            $available,
        );
    }
}
