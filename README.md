# AcsiomaticDeviceDetectorBundle

The `AcsiomaticDeviceDetectorBundle` provides integration of the [DeviceDetector][dd] library into the [Symfony][sf] framework.

> [DeviceDetector][dd] is a Universal Device Detection library that parses User Agents and Browser Client Hints to detect devices (desktop, tablet, mobile, tv, cars, console, etc.), clients (browsers, feed readers, media players, PIMs, ...), operating systems, brands and models.
>
> From https://github.com/matomo-org/device-detector

This bundle provides the [`DeviceDetector` class][dd-class] as [service][sf-service], and [Twig global variable][twig-global-variables].

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Usage in controllers](#usage-in-controllers)
  - [Usage in Twig](#usage-in-twig)
  - [Usage in route condition](#usage-in-route-condition)
  - [Parsing custom request](#parsing-custom-request)
- [Release Policy](#release-policy)
  - [Dependencies Compatibility Policy](#dependencies-compatibility-policy)

## Installation

You can install the bundle using [Composer]:

```bash
composer require acsiomatic/device-detector-bundle
```

## Configuration

You can configure the bundle using the `acsiomatic_device_detector` configuration section:

```yaml
# config/packages/acsiomatic_device_detector.yaml

acsiomatic_device_detector:

    # If true, DeviceDetector will trigger parser() when necessary
    auto_parse: true

    # Version truncation behavior, it may assume: major, minor, patch, build, or none
    # By default minor versions will be returned (e.g. X.Y)
    version_truncation: 'minor'

    cache:

        # If null, it will disable caching
        pool: 'cache.app'

    bot:

        # If true getBot() will only return true if a bot was detected (speeds up detection a bit)
        discard_information: false

        # If true, bot detection will completely be skipped (bots will be detected as regular devices then)
        skip_detection: false

    twig:

        # If null, it will not assign Twig variable
        variable_name: 'device'

    routing:

        # If null, it will not tag DeviceDetector as routing condition service
        condition_service_alias: 'device'
```

## Usage

### Usage in controllers

You can inject `DeviceDetector` as a service.
This bundle sets up an instance of this class according to the configurations under the `acsiomatic_device_detector` section in order to provide information about the main request.

```php
use DeviceDetector\DeviceDetector;
use Symfony\Component\HttpFoundation\Response;

class MyController
{
    public function index(DeviceDetector $device): Response
    {
        if ($device->isSmartphone()) {
            // ...
        }
    }
}
```

> [!NOTE]
> You need to call `$device->parse()` before asking for device's information if `auto_parse` configuration is false.

### Usage in Twig

The `DeviceDetector` service is assigned to the Twig templates as `device` variable.

```twig
{% if device.isSmartphone %}
    {# ... #}
{% endif %}
```

### Usage in route condition

The `DeviceDetector` is tagged as routing condition service.

```php
use Symfony\Component\HttpFoundation\Response;

class MyController
{
    #[Route('/page', condition: "service('device').isSmartphone()")]
    public function index(): Response
    {
        // this endpoint is available only for smartphone devices
    }
}
```

### Parsing custom request

You might want to parse other request than the current one.
This bundle provides a service that implements [DeviceDetectorFactoryInterface](src%2FContracts%2FDeviceDetectorFactoryInterface.php).
This service provides methods that create fresh `DeviceDetector` instances according to the configurations under the `acsiomatic_device_detector` section, but it doesn't attach them to any request.

## Custom parsers

You can inject custom parsers into `DeviceDetector` by providing them as services.

If [autoconfigure][sf-autoconfigure] is enabled, the bundle injects custom parsers.
Otherwise, you need to add the corresponding tag to each custom parsers:

- `acsiomatic.device_detector.bot_parser`
- `acsiomatic.device_detector.client_parser`
- `acsiomatic.device_detector.device_parser`

## Release Policy

There is a **single** maintained branch per time.
This branch targets a minor version.

A maintained version reaches its end-of-life when a new minor version is released.

### Dependencies Compatibility Policy

This library is compatible with maintained versions of
[PHP][php-versions],
[Device Detector][dd-versions], and
[Symfony][sf-versions].

[composer]: https://getcomposer.org/
[dd-class]: https://github.com/matomo-org/device-detector/blob/master/DeviceDetector.php
[dd-versions]: https://github.com/matomo-org/device-detector/branches
[dd]: https://github.com/matomo-org/device-detector
[php-versions]: https://www.php.net/supported-versions.php
[sf-autoconfigure]: https://symfony.com/doc/current/service_container.html#the-autoconfigure-option
[sf-service]: https://symfony.com/doc/current/service_container.html#fetching-and-using-services
[sf-versions]: https://symfony.com/releases#sf-versions
[sf]: https://symfony.com/
[twig-global-variables]: https://symfony.com/doc/current/templates.html#global-variables
