# Laravel OVH Object Storage driver


[![Latest Version on Packagist](https://img.shields.io/packagist/v/sausin/laravel-ovh.svg?style=flat-square)](https://packagist.org/packages/sausin/laravel-ovh)
[![Continuous Integration](https://github.com/sausin/laravel-ovh/workflows/CI%20laravel-ovh/badge.svg?branch=master)](https://github.com/sausin/laravel-ovh/actions?query=workflow%3A%22CI+laravel-ovh%22)
[![Quality Score](https://img.shields.io/scrutinizer/g/sausin/laravel-ovh.svg?style=flat-square)](https://scrutinizer-ci.com/g/sausin/laravel-ovh)
[![Total Downloads](https://img.shields.io/packagist/dt/sausin/laravel-ovh.svg?style=flat-square)](https://packagist.org/packages/sausin/laravel-ovh)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)


Laravel `Storage` facade provides support for many different filesystems.

This is a wrapper to provide support in Laravel for [OVH Object Storage](https://www.ovh.ie/public-cloud/storage/object-storage/).

# Installation

Install via composer:
```
composer require sausin/laravel-ovh
```

Please see below for the details on various branches. You can choose the version of the package which is suitable for your development.
Also, take note of the upgrade.

| Package version | PHP compatibility | Laravel versions | Special features of OVH                   | Status     |
| --------------- | :---------------: | :--------------: | :---------------------------------------: | :--------- |
| `1.2.x`         | `^7.0 - ^7.1`     | `>=5.4`, `<=5.8` | Temporary Url Support                     | Deprecated |
| `2.x`           | `>=7.1`           | `>=5.4`, `<=6.x` | Above + Expiring Objects + Custom Domains | Deprecated |
| `3.x`           | `>=7.1`           | `>=5.4`, `<=7.x` | Above + Keystone v3 API                   | Deprecated |
| `4.x`           | `>=7.2`           | `>=5.4`          | Above + Set private key on container      | Deprecated |
| `5.x`           | `>=7.4`           | `>=5.8`          | Above + Config-based Expiring Objects + Form Post Signature + Prefix     | Maintained     |
| `6.x`           | `>=7.4`           | `>=7.x`          | PHP 8 support     | Active     |

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
    'containerName' => env('OS_CONTAINER_NAME'),

    // Since v1.2
    // Optional variable and only if you are using temporary signed urls.
    // You can also set a new key using the command 'php artisan ovh:set-temp-url-key'.
    'tempUrlKey' => env('OS_TEMP_URL_KEY'),

    // Since v2.1
    // Optional variable and only if you have setup a custom endpoint.
    'endpoint' => env('OS_CUSTOM_ENDPOINT'),

    // Optional variables for handling large objects.
    // Defaults below are 300MB threshold & 100MB segments.
    'swiftLargeObjectThreshold' => env('OS_LARGE_OBJECT_THRESHOLD', 300 * 1024 * 1024),
    'swiftSegmentSize' => env('OS_SEGMENT_SIZE', 100 * 1024 * 1024),
    'swiftSegmentContainer' => env('OS_SEGMENT_CONTAINER', null),

    // Optional variable and only if you would like to DELETE all uploaded object by DEFAULT.
    // This allows you to set an 'expiration' time for every new uploaded object to
    // your container. This will not affect objects already in your container.
    //
    // If you're not willing to DELETE uploaded objects by DEFAULT, leave it empty.
    // Really, if you don't know what you're doing, you should leave this empty as well.
    'deleteAfter' => env('OS_DEFAULT_DELETE_AFTER', null),
    
    // Optional variable to set a prefix on all paths
    'prefix' => null,
],
```
Define the correct env variables above in your .env file (to correspond to the values above),
and you should now have a working OVH Object Storage setup :smile:.

The environment variable `OS_AUTH_URL` is normally not going to be any different for OVH users and hence doesn't need to
be specified. To get the values for remaining variables (like `OS_USERNAME`, `OS_REGION_NAME`, `OS_CONTAINER_NAME`,
etc...), you can download the configuration file with details from OVH's Horizon or Control Panel:
- **OVH Control Panel**: `Public cloud -> Project Management -> Users & Roles -> Download Openstack's RC file`
- **OVH Horizon**: `Project -> API Access -> Download OpenStack RC File -> Identity API v3`

Be sure to clear your app's config cache after finishing this library's configuration:
```sh
php artisan config:cache
```

**NOTE**: Downloading your RC config file from **OVH Control Panel** will provide **Identity v2** variable names.
However, for this package, the following variables are equivalent:

| `laravel-ovh` variable name | OVH's RC variable name |
| --------------------------- | ---------------------- |
| `OS_PROJECT_ID`             | `OS_TENANT_ID`         |
| `OS_PROJECT_NAME`           | `OS_TENANT_NAME`       |

You can safely place the values from the Identity v2 variables and place them in the corresponding variable for this package.

## Upgrade Notes

### From 3.x to 4.x
Starting with `4.x` branch, the variables to be defined in the `.env` file
have been renamed to reflect the names used by OpenStack in their configuration file. This is to
remove any discrepancy in understanding which variable should go where. This also means that
the package might fail to work unless the variable names in the `.env` file are updated.

### From 4.x to 5.x
Starting with `5.x` branch, the variables to be defined in the `config/filesystems.php`
file have been renamed to better correspond with the names used by OpenStack in their configuration file. This
is intended to give the developer a better understanding of the contents of each configuration
key. If you're coming from `3.x`, updating the variable names in the `.env` might be essential to prevent failure.

# Usage

Refer to the extensive [Laravel Storage Documentation](https://laravel.com/docs/7.x/filesystem) for usage.

**NOTE:** This package includes support for the following additional methods:
```php
Storage::url()
Storage::temporaryUrl()
```

The `temporaryUrl()` method is relevant for private containers where files are not publicly accessible
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

# Generate a key for a specific disk
php artisan ovh:set-temp-url-key --disk="other-ovh-disk"

# Set a specific key
php artisan ovh:set-temp-url-key --key=your-private-key
```
The package will then set the relevant key on your container and present it to you. If a key
has already been set up previously, the package will warn you before overriding the existing
key. If you'd like to force a new key anyway, you may use the `--force` flag with the command.

Once you got your key configured in your container, you must add it to your `.env` file:
```dotenv
OS_TEMP_URL_KEY='your-private-key'
``` 

## Configuring a Custom Domain Name (Custom Endpoint)

OVH's Object Storage allows you to point a Custom Domain Name or Endpoint to an individual
container. For this, you must setup some records with your DNS provider, which will authorize
the forwarded requests coming from your Endpoint to OVH's servers.

In order to use a Custom Domain Name, you must specify it in your `.env` file:
```dotenv
OS_CUSTOM_ENDPOINT="http://my-endpoint.example.com"
```

For more information, please refer to [OVH's Custom Domain Documentation](https://docs.ovh.com/gb/en/storage/pcs/link-domain/).

## Uploading Automatically Expiring Objects

This library allows you to add expiration time to uploaded objects. There are 2 ways to do it:

1. Specifying expiration time programmatically:
    - You can either specify the number of seconds after which the uploaded object should be deleted:
        ```php
        // Automatically expire after 1 hour of being uploaded.
        Storage::disk('ovh')->put('path/to/file.jpg', $contents, ['deleteAfter' => 60*60]);
        ```
    - Or, you can also specify a timestamp after which the uploaded object should be deleted:
        ```php
        // Automatically delete at the beginning of next month.
        Storage::disk('ovh')->put('path/to/file.jpg', $contents, ['deleteAt' => now()->addMonth()->startOfMonth()])
        ```

2. Specifying default expiration time via `.env` file. This will set an expiration time (in seconds)
to every newly uploaded object by default:
    ```dotenv
    # Delete every object after 3 days of being uploaded
    OS_DELETE_AFTER=259200
    ```

For more information about these variables,  please refer to
[OVH's Automatic Object Deletion Documentation](https://docs.ovh.com/gb/en/storage/configure_automatic_object_deletion/)

## Large Object Support

This library can help you optimize the upload speeds of large objects (such as videos or disk images)
automatically by detecting file size thresholds and splitting the file into lighter segments. This will
improve upload speeds by writing multiple segments into multiple Object Storage nodes simultaneously.

By default, the size threshold to detect a Large Object is set to 300MB, and the segment size to split
the file is set to 100MB. If you would like to change these values, you must specify the following
variables in your `.env` file (in Bytes):
```dotenv
# Set size threshold to 1GB
OS_LARGE_OBJECT_THRESHOLD=1073741824
# Set segment size to 200MB
OS_SEGMENT_SIZE=209715200
```

If you would like to use a separate container for storing your Large Object Segments,
you can do so by specifing the following variable in your `.env` file:
```dotenv
OS_SEGMENT_CONTAINER="large-object-container-name"
```

Using a separate container for storing the segments of your Large Objects can be beneficial in
some cases, to learn more about this, please refer to
[OpenStack's Last Note on Using Swift for Large Objects](https://docs.openstack.org/swift/stein/overview_large_objects.html#using-swift)

To learn more about segmented uploads for large objects, please refer to:
- [OVH's Optimizing Large Object Uploads Documentation](https://docs.ovh.com/gb/en/storage/optimised_method_for_uploading_files_to_object_storage/)
- [OpenStack's Large Object Support Documentation](https://docs.openstack.org/swift/latest/overview_large_objects.html)

## Form Post Middleware

While this feature in not documented by the OVH team, it's explained in the
[OpenStack's Documentation](https://docs.openstack.org/swift/latest/api/form_post_middleware.html).

This feature allows for uploading of files _directly_ to the OVH servers rather than going through the application servers
(thus improving the efficiency in the upload cycle).

You must generate a valid FormPost signature, for which you can use the following function:
```php
Storage::disk('ovh')->getAdapter()->getFormPostSignature($path, $expiresAt, $redirect, $maxFileCount, $maxFileSize);
```
Where:
- `$path` is the directory path in which you would like to store the files.
- `$expiresAt` is a `DateTimeInterface` object that specifies a date in which the FormPost signature will expire.
- `$redirect` is the URL to which the user will be redirected once all files finish uploading. Defaults to `null` to prevent redirects.
- `$maxFileCount` is the max quantity of files that the user will be able to upload using the signature. Defaults to `1` file.
- `$maxFileSize` is the size limit that each uploaded file can have. Defaults to `25 MB` (`25*1024*1024`).

After obtaining the signature, you need to pass the signature data to your HTML form:
```blade
<form action="{{ $url }}" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="redirect" value="{{ $redirect }}">
    <input type="hidden" name="max_file_count" value="{{ $maxFileCount }}">
    <input type="hidden" name="max_file_size" value="{{ $maxFileSize }}">

    <input type="hidden" name="expires" value="{{ $expiresAt->getTimestamp() }}">
    <input type="hidden" name="signature" value="{{ $signature }}">

    <input type="file">
</form>
```

> **NOTE**: The upload method in the form _must_ be type of `POST`.

> **NOTE**: As this will be a cross origin request, appropriate headers are needed on the container. See the use of command `php artisan ovh:set-cors-headers` further.

The `$url` variable refers to the path URL to your container, you can get it by passing the path to the adapter `getUrl`:
```php
$url = Storage::disk('ovh')->getAdapter()->getUrl($path);
```

> **NOTE**: If you've setup a custom domain for your Object Storage container, you can use that domain (along with the corresponding path)
> to upload your files without exposing your OVH's URL scheme.

### Examples

```php
// Generate a signature that allows an upload to the 'images' directory for the next 10 minutes.
Storage::disk('ovh')->getAdapter()->getFormPostSignature('images', now()->addMinutes(10));

// Generate a signature that redirects to a url after successful file upload to the root of the container.
Storage::disk('ovh')->getAdapter()->getFormPostSignature('', now()->addMinutes(5), route('file-uploaded'));

// Generate a signature that allows upload of 3 files until next day.
Storage::disk('ovh')->getAdapter()->getFormPostSignature('', now()->addDay(), null, 3);

// Generate a signature that allows to upload 1 file of 1GB until the next hour.
Storage::disk('ovh')->getAdapter()->getFormPostSignature('', now()->addHour(), null, 1, 1 * 1024 * 1024 * 1024);
```
## Setting up Access Control headers on the container
For the setup above to work correctly, the container must have the correct headers set on it. This package provides a convenient way to set them up using the below command
```php
php artisan ovh:set-cors-headers
```
By default this will allow all origins to be able to upload on the container. However, if you would like to allow only specific origin(s) you may use the `--origins` flag.

If these headers were already set previously, the command will seek confirmation before overriding the existing headers.

## Prefix & Multi-tenancy

As noted above, `prefix` parameter was introduced in release 5.3.0. This means that any path specified when using the package will be prefixed with the given string. Nothing is added by default (or if the parameter has not been set at all).

For example, when `prefix` has been set as `foo` in the config, the following command:
```php
Storage::disk('ovh')->url('/');
```
will generate a url as if it was requested with a path of `/foo` (i.e. the specified prefix has been used).

This is particularly powerful in a multi-tenant setup. The same container can be used for all tenants and yet each tenant can have its own folder, almost automatically. The middleware where the tenant is being set can be updated, and using the below command:
```php
Config::set('filesystems.disks.ovh.prefix', 'someprefixvalue')
```
a separate custom prefix will be set for each tenant!

Both examples above assume the disk has been named as `ovh` in the config. Replace with the correct name for your case.

# Credits
- ThePHPLeague for the awesome [Flysystem](https://github.com/thephpleague/flysystem)!
- [Chris Harvey](https://github.com/chrisnharvey) for the [Flysystem OpenStack SwiftAdapter](https://github.com/nimbusoftltd/flysystem-openstack-swift).
- Rackspace for maintaining the [PHP OpenStack Repo](https://github.com/php-opencloud/openstack).
