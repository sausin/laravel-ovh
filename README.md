# Laravel OVH Object Storage driver

[![Build Status](https://travis-ci.org/sausin/laravel-ovh.svg?branch=master)](https://travis-ci.org/sausin/laravel-ovh)
[![Total Downloads](https://poser.pugx.org/sausin/laravel-ovh/d/total.svg)](https://packagist.org/packages/sausin/laravel-ovh)
[![Latest Stable Version](https://poser.pugx.org/sausin/laravel-ovh/v/stable.svg)](https://packagist.org/packages/sausin/laravel-ovh)
[![License](https://poser.pugx.org/brayniverse/laravel-route-macros/license.svg)](https://opensource.org/licenses/MIT)


Laravel `Storage` facade provides support for many different filesystems.

This is a wrapper to combine others' work to integrate with laravel and provide support for [OVH Object Storage](https://www.ovh.ie/public-cloud/storage/object-storage/).

# Installing

Install the package via composer

`composer require sausin/laravel-ovh`

and make sure to allow all versions in `composer.json`
`"sausin/laravel-ovh": "0.*",`

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

# Usage

Refere to extensive laravel [documentation](https://laravel.com/docs/5.4/filesystem) for usage. Of note - this package includes support for the `Storage::url()` method.

# Credits
- the SwiftAdapter was created by Nimbusoft (https://github.com/nimbusoftltd/flysystem-openstack-swift) and I have just modified it to provide the url function
- cyberx86 did the hard work of figuring out how to get the Openstack setup going with OVH (https://www.thatsgeeky.com/2016/08/openstack-php-and-ovh/)
- rackspace for maintaining the Openstack repo (https://github.com/php-opencloud/openstack)
- obviously the creator of league flysystem!
- @anhphamt for providing the [pull request](https://github.com/nimbusoftltd/flysystem-openstack-swift/pull/3) for large object support
