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
                            {--force : Forcibly set a new key on the container} 
                            {--disk=ovh : Select the ovh disk}';

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
        $key = $this->option('key') ?: $this->getRandomKey();

        if ($this->option('force')) {

            $this->setContainerKey($key);

            return 0;
        }

        if ($this->checkContainerHasKey()) {

            $this->info('Container already has a key');

            return 1;
        }




        $this->setContainerKey($key);
    }

    protected function setContainerKey($key)
    {

        try {

            $this->getDisk()
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
        $data = $this->getDisk()->getAdapter()->getContainer()->getMetaData();

        return array_key_exists('Temp-Url-Key', $data);
    }

    protected function getDisk()
    {
        return Storage::disk($this->option('disk'));
    }
}
