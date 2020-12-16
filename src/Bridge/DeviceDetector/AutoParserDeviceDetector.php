<?php

namespace Acsiomatic\DeviceDetectorBundle\Bridge\DeviceDetector;

use DeviceDetector\DeviceDetector;

if ('3' === strstr(DeviceDetector::VERSION, '.', true)) {
    /**
     * @internal
     */
    class AutoParserDeviceDetector extends DeviceDetector
    {
        use AutoParserFor3Trait;
    }
} else {
    /**
     * @internal
     */
    class AutoParserDeviceDetector extends DeviceDetector
    {
        use AutoParserFor4Trait;
    }
}
