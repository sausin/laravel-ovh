# Laravel OVH Object Storage driver


[![Latest Version on Packagist](https://img.shields.io/packagist/v/sausin/laravel-ovh.svg?style=flat-square)](https://packagist.org/packages/sausin/laravel-ovh)
[![Build Status](https://img.shields.io/travis/sausin/laravel-ovh/master.svg?style=flat-square)](https://travis-ci.org/sausin/laravel-ovh)
[![Quality Score](https://img.shields.io/scrutinizer/g/sausin/laravel-ovh.svg?style=flat-square)](https://scrutinizer-ci.com/g/sausin/laravel-ovh)
[![StyleCI](https://styleci.io/repos/85194981/shield?branch=master)](https://styleci.io/repos/85194981)
[![Total Downloads](https://img.shields.io/packagist/dt/sausin/laravel-ovh.svg?style=flat-square)](https://packagist.org/packages/sausin/laravel-ovh)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)


Laravel `Storage` facade provides support for many different filesystems.

This is a wrapper to combine others' work to integrate with laravel and provide support for [OVH Object Storage](https://www.ovh.ie/public-cloud/storage/object-storage/).

# Installing

Install via composer:
```
composer require sausin/laravel-ovh
```

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
            'urlKey' => env('OVH_URL_KEY'),
        ],
```

define the correct env variables above in your .env file and you should now have a working OVH Object Storage setup :)

# Usage

Refere to extensive laravel [documentation](https://laravel.com/docs/5.4/filesystem) for usage. Of note - this package includes support for the following additional methods:

`Storage::url()`

and

`Storage::temporaryUrl()`

The temporary url is relevant for private containers where files are not publicly accessible under normal conditions. This generates a temporary url with expiry (see details [here](https://github.com/laravel/framework/pull/20375) for usage).

Note that this requires the container to have a proper header. The key in the header should match the `urlKey` specified in `filesystems.php`. For details on how to setup the header on your OVH container, see [here](https://www.ovh.com/us/g2007.share_object_via_temporary_url#generate_your_temporary_url).

# Credits
- the SwiftAdapter was created by Nimbusoft (https://github.com/nimbusoftltd/flysystem-openstack-swift) and I have just modified it to provide the url function
- cyberx86 did the hard work of figuring out how to get the Openstack setup going with OVH (https://www.thatsgeeky.com/2016/08/openstack-php-and-ovh/)
- rackspace for maintaining the Openstack repo (https://github.com/php-opencloud/openstack)
- obviously the creator of league flysystem!