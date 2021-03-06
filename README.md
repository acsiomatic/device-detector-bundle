# AcsiomaticDeviceDetectorBundle

[![Tests](https://github.com/acsiomatic/device-detector-bundle/workflows/Tests/badge.svg)](https://github.com/acsiomatic/device-detector-bundle/actions?query=workflow%3ATests+branch%3Amain)
[![Static Analysis](https://github.com/acsiomatic/device-detector-bundle/workflows/Static%20Analysis/badge.svg)](https://github.com/acsiomatic/device-detector-bundle/actions?query=workflow%3A%22Static+Analysis%22+branch%3Amain)
[![Coding Standards](https://github.com/acsiomatic/device-detector-bundle/workflows/Coding%20Standards/badge.svg)](https://github.com/acsiomatic/device-detector-bundle/actions?query=workflow%3A%22Coding+Standards%22+branch%3Amain)
[![codecov](https://codecov.io/gh/acsiomatic/device-detector-bundle/branch/main/graph/badge.svg?token=zUb7y8rBdo)](https://codecov.io/gh/acsiomatic/device-detector-bundle)

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

You can inject `DeviceDetector` as a service.
This bundle sets up this class according to the configurations under the `acsiomatic_device_detector` section in order to provide information about the current request.

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

The `DeviceDetector` service is assigned to the Twig templates as `device` variable.

```twig
{% do device.parse %}

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
        $deviceDetector->parse();

        return $deviceDetector->isSmartphone();
    }
}
```

[DeviceDetector class]: https://github.com/matomo-org/device-detector/blob/master/DeviceDetector.php
[DeviceDetector]: https://github.com/matomo-org/device-detector
[Symfony]: https://symfony.com/
[Twig global variable]: https://symfony.com/doc/current/templates.html#template-variables
[service]: https://symfony.com/doc/current/service_container.html#fetching-and-using-services
