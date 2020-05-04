# Laravel OVH Object Storage driver


[![Latest Version on Packagist](https://img.shields.io/packagist/v/sausin/laravel-ovh.svg?style=flat-square)](https://packagist.org/packages/sausin/laravel-ovh)
[![](https://github.com/sausin/laravel-ovh/workflows/CI%20laravel-ovh/badge.svg?branch=master)](https://github.com/sausin/laravel-ovh/actions?query=workflow%3A%22CI+laravel-ovh%22)
[![Quality Score](https://img.shields.io/scrutinizer/g/sausin/laravel-ovh.svg?style=flat-square)](https://scrutinizer-ci.com/g/sausin/laravel-ovh)
[![Total Downloads](https://img.shields.io/packagist/dt/sausin/laravel-ovh.svg?style=flat-square)](https://packagist.org/packages/sausin/laravel-ovh)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)


Laravel `Storage` facade provides support for many different filesystems.

This is a wrapper to provide support in Laravel for [OVH Object Storage](https://www.ovh.ie/public-cloud/storage/object-storage/).

# Installing

Install via composer:
```
composer require sausin/laravel-ovh
```

Please see below for the details on various branches. You can choose the version of the package which is suitable for your development. Also take note of the upgrade

| Package version   | PHP compatibility | Laravel versions  | Special features of OVH               | Status        |
| ----------------- | :---------------: | :---------------: | :-----------------------------------: | :------------ |
| `1.2.x`           | `7.0 - 7.1`       | `>=5.4`, `<=5.8`  | `temporaryUrl()`                      | Deprecated    |
| `2.x`             | `>=7.1`           | `>=5.4`, `<=6.x`  | Above + `expiring objects`            | Deprecated    |
| `3.x`             | `>=7.1`           | `>=5.4`, `<=7.x`  | Above                                 | Deprecated    |
| `4.x`             | `>=7.2`           | `>=5.4`           | Above + Set private key on container  | Maintained    |
| `5.x`             | `>=7.4`           | `>=5.4`           | Above + Better Parameter Parity       | Active        |

If you are using Laravel versions older than 5.5, add the service provider to the `providers` array in `config/app.php`:
```php
Sausin\LaravelOvh\OVHServiceProvider::class
```

Define the ovh driver in the `config/filesystems.php`
as below
```php
'ovh' => [
    'driver' => 'ovh',
    'authUrl' => env('OS_AUTH_URL', 'https://auth.cloud.ovh.net/v3/'),
    'projectId' => env('OS_PROJECT_ID'),
    'region' => env('OS_REGION_NAME'),
    'userDomain' => env('OS_USER_DOMAIN_NAME', 'Default'),
    'username' => env('OS_USERNAME'),
    'password' => env('OS_PASSWORD'),
    'container' => env('OS_CONTAINER_NAME'),

    // Optional variable and only if you are using temporary signed urls.
    // You can also set a new key using the command 'php artisan ovh:set-temp-url-key'.
    'tempUrlKey' => env('OS_TEMP_URL_KEY'),

    // Optional variable and only if you have setup a custom endpoint.
    'endpoint' => env('OS_CUSTOM_ENDPOINT'),

    // Optional variables for handling large objects.
    // Defaults below are 300MB & 100MB.
    'swiftLargeObjectThreshold' => env('OS_LARGE_OBJECT_THRESHOLD', 300 * 1024 * 1024),
    'swiftSegmentSize' => env('OS_SEGMENT_SIZE', 100 * 1024 * 1024),
    'swiftSegmentContainer' => env('OS_SEGMENT_CONTAINER'),
],
```
Define the correct env variables above in your .env file (to correspond to the values above),
and you should now have a working OVH Object Storage setup :smile:.

**IMPORTANT:** Starting with `4.x` branch, the variables to be defined in the `.env` file
have been renamed to reflect the names used by OpenStack in their configuration file. This is to
remove any discrepancy in understanding which variable should go where. This also means that
the package might fail to work unless the variable names in the `.env` file are updated.

**IMPORTANT:** Starting with `5.x` branch, the variables to be defined in the `config/filesystems.php`
file have been renamed to reflect the names used by OpenStack in their configuration file. This
is intended to give the developer a better understanding of the contents of each configuration
key. This also means that the package might fail to work unless the variable names in the `.env`
file are updated.

The URL is normally not going to be any different for OVH users and hence doesn't need to
be specified. To get the values for remaining variables (like `user`, `region`, `container`,
etc), you can download the configuration file with details in your OVH control panel
(`Public cloud -> Project Management -> Users & Roles -> Download Openstack's RC file`). 

Be sure to run
```sh
php artisan config:cache
```
again if you've been using the caching on your config file.


# Usage

Refer to the extensive [Laravel Storage Documentation](https://laravel.com/docs/7.x/filesystem) for usage.

**NOTE:** This package includes support for the following additional methods:
```php
Storage::url()
Storage::temporaryUrl()
```

The temporary url is relevant for private containers where files are not publicly accessible
under normal conditions. This generates a temporary signed url. For more details, please refer
to [OVH's Temporary URL Documentation](https://docs.ovh.com/gb/en/public-cloud/share_an_object_via_a_temporary_url/).

Remember that this functionality requires the container to have a proper key stored.
The key in the header should match the `tempUrlKey` specified in `config/filesystems.php`.
For more details on how to set up the header on your OVH container, please refer to
[Generate the temporary address (_tempurl_)](https://docs.ovh.com/gb/en/public-cloud/share_an_object_via_a_temporary_url/#generate-the-temporary-address-tempurl).

Alternatively, since version 4.x you can use the following commands:
```sh
# Automatically generate a key
php artisan ovh:set-temp-url-key

# Set a specific key
php artisan ovh:set-temp-url-key --key=your-private-key
```
The package will then set the relevant key on your container. If a key has already been
set up previously, the package will warn you before overriding the existing key.
If you'd like to set up a new key anyway, you may use the `--force` flag with the command. 

## Uploading expiring objects

If you would like to upload objects which expire (i.e. get auto deleted) at a certain time
in the future, you can add `deleteAt` or `deleteAfter` configuration options when uploading
the object.

For eg, below code will upload a file which expires after one hour:
```php
Storage::disk('ovh')->put('path/to/file.jpg', $contents, ['deleteAfter' => 60*60])
```

Usage of these variables is explained in [OVH's Documentation](https://docs.ovh.com/gb/en/storage/configure_automatic_object_deletion/)

# Credits
- ThePHPLeague for the awesome [Flysystem](https://github.com/thephpleague/flysystem)!
- Nimbusoft for their [SwiftAdapter](https://github.com/nimbusoftltd/flysystem-openstack-swift).
- Rackspace for maintaining the [PHP OpenStack Repo](https://github.com/php-opencloud/openstack).
