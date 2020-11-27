# AcsiomaticDeviceDetectorBundle

The `AcsiomaticDeviceDetectorBundle` provides integration of the [DeviceDetector] library into the [Symfony] framework.

> [DeviceDetector] is a Universal Device Detection library that parses User Agents and detects devices (desktop, tablet, mobile, tv, cars, console, etc.), clients (browsers, feed readers, media players, PIMs, ...), operating systems, brands and models.
> 
> From https://github.com/matomo-org/device-detector

This bundle provides the [DeviceDetector class] as a [service], and a [Twig global variable].

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage in controllers](#usage-in-controllers)
- [Usage in Twig](#usage-in-twig)

## Installation

This bundle is compatible with [Symfony] from `3.4` to `5.x`, and [DeviceDetector] from `3.9` to `4.x`.

You can install the bundle using Symfony Flex:

```bash
composer require acsiomatic/device-detector-bundle
```

## Configuration

You can configure the bundle using the `acsiomatic_device_detector` configuration section:

```yaml
# config/packages/acsiomatic_device_detector.yaml

acsiomatic_device_detector:
    cache:
        pool: 'cache.app' # null value disables caching
    bot:
        skip_detection: false
        discard_information: false
    twig:
        variable_name: 'device' # null value disables variable assignment
```

## Usage in controllers

```php
use DeviceDetector\DeviceDetector;

class MyController
{
    public function index(DeviceDetector $device)
    {
        $device->parse();

        if ($device->isSmartphone()) {
            // ...
        }
    }
}
```

Note that you need to call `parse()` to ask for device's information.

## Usage in Twig

```twig
{% do device.parse %}

{% if device.isSmartphone %}
    {# ... #}
{% endif %}
```

[DeviceDetector class]: https://github.com/matomo-org/device-detector/blob/master/DeviceDetector.php
[DeviceDetector]: https://github.com/matomo-org/device-detector
[Symfony]: https://symfony.com/
[Twig global variable]: https://symfony.com/doc/current/templates.html#template-variables
[service]: https://symfony.com/doc/current/service_container.html#fetching-and-using-services
