<?php

namespace Acsiomatic\DeviceDetectorBundle\Contracts;

use DeviceDetector\ClientHints;
use Symfony\Component\HttpFoundation\Request;

interface ClientHintsFactoryInterface
{
    public function createClientHintsFromRequest(Request $request): ClientHints;
}
