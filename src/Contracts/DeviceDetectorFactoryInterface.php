<?php

namespace Acsiomatic\DeviceDetectorBundle\Contracts;

use DeviceDetector\DeviceDetector;

interface DeviceDetectorFactoryInterface
{
    public function createDeviceDetector(): DeviceDetector;
}
