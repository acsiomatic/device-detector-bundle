<?php

namespace Acsiomatic\DeviceDetectorBundle\Decoration;

use DeviceDetector\DeviceDetector as BaseDeviceDetector;

if ('3' === strstr(BaseDeviceDetector::VERSION, '.', true)) {
    /**
     * @internal
     */
    class DecoratedDeviceDetector extends BaseDeviceDetector
    {
        use AutoParser3Trait;
    }
} else {
    /**
     * @internal
     */
    class DecoratedDeviceDetector extends BaseDeviceDetector
    {
        use AutoParser4Trait;
    }
}
