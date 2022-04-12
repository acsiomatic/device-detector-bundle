<?php

namespace Acsiomatic\DeviceDetectorBundle\Contracts;

use DeviceDetector\DeviceDetector;
use Symfony\Component\HttpFoundation\Request;

interface DeviceDetectorFactoryInterface
{
    public function createDeviceDetector(): DeviceDetector;

    public function createDeviceDetectorFromRequest(Request $request): DeviceDetector;
}
