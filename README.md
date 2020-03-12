# Laravel OVH Object Storage driver


[![Latest Version on Packagist](https://img.shields.io/packagist/v/sausin/laravel-ovh.svg?style=flat-square)](https://packagist.org/packages/sausin/laravel-ovh)
[![](https://github.com/sausin/laravel-ovh/workflows/CI%20laravel-ovh/badge.svg?branch=master)](https://github.com/sausin/laravel-ovh/actions?query=workflow%3A%22CI+laravel-ovh%22)
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
Please see below for the details on various branches. You can choose the version of the package which is suitable for your development.

| Package version | PHP compatibility | Laravel versions | Spl. features of OVH                   | Status              |
| --------------- | :---------------: | :--------------: | :------------------------------------: | :------------------ |
| `1.2.x`         | `7.0`, `7.1`      | `>5.4`, `<5.8`   | `temporaryUrl()`                       | Deprecated          |
| `2.x`           | `7.0`, `7.1`      | `>5.4`, `<=6.x`  | `temporaryUrl()`                       | Deprecated          |
| `3.x`           | `7.0`, `7.1`      | `>5.4`, `<=7.x`  | `temporaryUrl()`, `expiring objects`   | Active (no support) |
| `4.x`           | `7.0`, `7.1`      | `>=7.x`          | `temporaryUrl()`, `expiring objects`   | Active              |

Then include the service provider in `config/app.php`
```php
Sausin\LaravelOvh\OVHServiceProvider::class
```
in the providers array. This step is not required for Laravel 5.5 and above as the service provider is automatically registered!

Define the ovh driver in the `config/filesystems.php`
as below
```php
'ovh' => [
    'server' => env('OVH_URL', 'https://auth.cloud.ovh.net/v3/'),
    'driver' => 'ovh',
    'user' => env('OVH_USER'),
    'pass' => env('OVH_PASS'),
    'userDomain' => env('OVH_USER_DOMAIN', 'Default'),
    'region' => env('OVH_REGION', 'GRA'),
    'container' => env('OVH_CONTAINER'),
    'projectId' => env('OVH_PROJECT_ID'),
    'urlKey' => env('OVH_URL_KEY'),
    // optional variable and only if you have setup a custom endpoint
    'endpoint' => env('OVH_CUSTOM_ENDPOINT'),
    // optional variables for handling large objects
    'swiftLargeObjectThreshold' => env('OVH_LARGE_OBJECT_THRESHOLD'),
    'swiftSegmentSize' => env('OVH_SEGMENT_SIZE'),
    'swiftSegmentContainer' => env('OVH_SEGMENT_CONTAINER'),
],
```

define the correct env variables above in your .env file (to correspond to the values above) and you should now have a working OVH Object Storage setup :). The URL is normally not going to be any different for OVH users and hence doesn't need to be specified. To get the values for remaining variables (like `user`, `region`, `container` etc), you can download the configuration file with details in your OVH control panel (`Public cloud -> Project Management -> Users & Roles -> Download Openstack's RC file (v3)`). 

Be sure to run
```
php artisan config:cache
```
again if you've been using the caching on your config file.


# Usage

Refer to extensive laravel [documentation](https://laravel.com/docs/5.5/filesystem) for usage. Of note - this package includes support for the following additional methods:

`Storage::url()`

and

`Storage::temporaryUrl()`

The temporary url is relevant for private containers where files are not publicly accessible under normal conditions. This generates a temporary url with expiry (see details [here](https://github.com/laravel/framework/pull/20375) for usage).

Note that this requires the container to have a proper header. The key in the header should match the `urlKey` specified in `filesystems.php`. For details on how to setup the header on your OVH container, see [here](https://docs.ovh.com/gb/en/public-cloud/share_an_object_via_a_temporary_url/#generate-the-key). Alternatively, you can use the commandline
```php
php artisan ovh:create-temp-url-key "your-private-key-without-quotes"
```
and the package will set the relevant key on your container. If a key has already been setup previously, the package will inform you of the same. If you'd like to setup a new key anyway, you may use the `--force` flag with the command. 

## Uploading expiring objects

If you would like to upload objects which expire (i.e. get auto deleted) at a certain time in future, you can add `deleteAt` or `deleteAfter` configuration options when uploading the object.

For eg, below code will upload a file which expires after one hour:
```php
Storage::disk('ovh')->put('path/to/file.jpg', $contents, ['deleteAfter' => 60*60])
```

Usage of these variables is explained in the OVH documentation [here](https://github.com/ovh/docs/blob/develop/pages/platform/public-cloud/setup_automatic_deletion_of_objects/guide.en-gb.md)

# Credits
- thephpleage for the awesome [flysystem](https://github.com/thephpleague/flysystem)!
- [SwiftAdapter](https://github.com/nimbusoftltd/flysystem-openstack-swift) by Nimbusoft
- Rackspace for maintaining the [Openstack repo](https://github.com/php-opencloud/openstack)
