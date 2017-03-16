# laravel-ovh
Wrapper to combine others' work to integrate with laravel

# Installing

Install the package via composer

`composer require sausin/laravel-ovh`

Then include the service provider in config/app.php
```php
Sausin\LaravelOvh\OVHServiceProvider::class
```
in the providers array

Define the ovh driver in the config/filesystems.php
as below
```php
        'ovh' => [
            'server' => env('OVH_URL'),
            'driver' => 'ovh',
            'user' => env('OVH_USER'),
            'pass' => env('OVH_PASS'),
            'region' => env('OVH_REGION'),
            'tenantName' => env('OVH_TENANT_NAME'),
            'container' => env('OVH_CONTAINER'),
            'projectId' => env('OVH_PROJECT_ID'),
        ],
```

define the correct env variables above in your .env file and you should now have a working OVH Object Storage setup :)

# Credits
- the SwiftAdapter was created by Nimbusoft (https://github.com/nimbusoftltd/flysystem-openstack-swift) and I have just modified it to provide the url function
- cyberx86 did the hard work of figuring out how to get the Openstack setup going with OVH (https://www.thatsgeeky.com/2016/08/openstack-php-and-ovh/)
- rackspace for maintaining the Openstack repo (https://github.com/php-opencloud/openstack)
- obviously the creator of league flysystem!
