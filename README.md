# AcsiomaticDeviceDetectorBundle

[![PHPUnit](https://github.com/acsiomatic/device-detector-bundle/actions/workflows/phpunit.yaml/badge.svg)](https://github.com/acsiomatic/device-detector-bundle/actions/workflows/phpunit.yaml)
[![PHP Static Analysis](https://github.com/acsiomatic/device-detector-bundle/actions/workflows/phpstan.yaml/badge.svg)](https://github.com/acsiomatic/device-detector-bundle/actions/workflows/phpstan.yaml)
[![Rector](https://github.com/acsiomatic/device-detector-bundle/actions/workflows/rector.yaml/badge.svg)](https://github.com/acsiomatic/device-detector-bundle/actions/workflows/rector.yaml)
[![PHP Coding Standards](https://github.com/acsiomatic/device-detector-bundle/actions/workflows/php-cs-fixer.yaml/badge.svg)](https://github.com/acsiomatic/device-detector-bundle/actions/workflows/php-cs-fixer.yaml)

The `AcsiomaticDeviceDetectorBundle` provides integration of the [DeviceDetector] library into the [Symfony] framework.

> [DeviceDetector] is a Universal Device Detection library that parses User Agents and Browser Client Hints to detect devices (desktop, tablet, mobile, tv, cars, console, etc.), clients (browsers, feed readers, media players, PIMs, ...), operating systems, brands and models.
> 
> From https://github.com/matomo-org/device-detector

This bundle provides the [DeviceDetector class] as a [service], and a [Twig global variable].

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage in controllers](#usage-in-controllers)
- [Usage in Twig](#usage-in-twig)

## Installation

This bundle is compatible with [Symfony] from `3.4` to `6.x`, and [DeviceDetector] from `4.0` to `6.x`.

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

    # If true, DeviceDetector will trigger parser() when necessary
    auto_parse: true

    # Version truncation behavior, it may assume: major, minor, patch, build, or none
    # By default minor versions will be returned (e.g. X.Y)
    version_truncation: 'minor'
```

## Usage in controllers

You can inject `DeviceDetector` as a service.
This bundle sets up this class according to the configurations under the `acsiomatic_device_detector` section in order to provide information about the current request.

```php
use DeviceDetector\DeviceDetector;

class MyController
{
    public function index(DeviceDetector $device)
    {
        if ($device->isSmartphone()) {
            // ...
        }
    }
}
```

Note that you need to call `$device->parse()` before asking for device's information if `auto_parse` configuration is false.

## Usage in Twig

The `DeviceDetector` service is assigned to the Twig templates as `device` variable.

```twig
{% if device.isSmartphone %}
    {# ... #}
{% endif %}
```

## Parsing custom request

You might want to parse other request than the current one.
This bundle provides a service that implements `DeviceDetectorFactoryInterface`.
This service provides a method that creates fresh `DeviceDetector` instances according to the configurations under the `acsiomatic_device_detector` section, but it doesn't attach them to any request.

```php
use Acsiomatic\DeviceDetectorBundle\Contracts\DeviceDetectorFactoryInterface;

class SmartphoneDeterminer
{
    /**
     * @var DeviceDetectorFactoryInterface
     */
    private $deviceDetectorFactory;

    public function __construct(DeviceDetectorFactoryInterface $factory)
    {
        $this->deviceDetectorFactory = $factory;
    }

    public function isSmartphone(string $userAgent): bool
    {
        $deviceDetector = $this->deviceDetectorFactory->createDeviceDetector();
        $deviceDetector->setUserAgent($userAgent);

        return $deviceDetector->isSmartphone();
    }
}
```

## Custom parsers

You can inject custom parsers to the `DeviceDetector` instance by providing them as services.

If [autoconfigure] is enabled, they will be injected automatically.
Otherwise, you need to add the corresponding tag to each custom parsers:

- `acsiomatic.device_detector.bot_parser`
- `acsiomatic.device_detector.client_parser`
- `acsiomatic.device_detector.device_parser`

[autoconfigure]: https://symfony.com/doc/current/service_container.html#the-autoconfigure-option
[DeviceDetector class]: https://github.com/matomo-org/device-detector/blob/master/DeviceDetector.php
[DeviceDetector]: https://github.com/matomo-org/device-detector
[Symfony]: https://symfony.com/
[Twig global variable]: https://symfony.com/doc/current/templates.html#template-variables
[service]: https://symfony.com/doc/current/service_container.html#fetching-and-using-services
