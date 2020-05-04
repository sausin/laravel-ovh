<?php

namespace Sausin\LaravelOvh\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SetTempUrlKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ovh:set-temp-url-key
                            {--key= : The key you want to set up on your container}
                            {--force : Forcibly set a new key on the container}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set temp url key on the private container, making the use of Storage::temporaryUrl() possible';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('force')) {
            $this->setContainerKey();

            return 0;
        }

        if ($this->checkContainerHasKey()) {
            $this->info('Container already has a key');

            return 1;
        }

        $this->setContainerKey();
    }

    protected function setContainerKey()
    {
        $key = $this->option('key') ?? $this->getRandomKey();

        try {
            Storage::disk('ovh')
            ->getAdapter()
            ->getContainer()
            ->resetMetadata(['Temp-Url-Key' => $key]);

            $this->info('Success! The key has been set as: '.$key);

            return 0;
        } catch (\Exception $e) {
            $this->info($e->getMessage());

            return 1;
        }
    }

    protected function getRandomKey()
    {
        return hash('sha512', time());
    }

    protected function checkContainerHasKey()
    {
        $data = Storage::disk('ovh')->getAdapter()->getContainer()->getMetaData();

        return array_key_exists('Temp-Url-Key', $data);
    }
}
