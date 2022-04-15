<?php

namespace Acsiomatic\DeviceDetectorBundle\Factory;

use Acsiomatic\DeviceDetectorBundle\Contracts\ClientHintsFactoryInterface;
use DeviceDetector\ClientHints;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final class ClientHintsFactory implements ClientHintsFactoryInterface
{
    public function createClientHintsFromRequest(Request $request): ClientHints
    {
        $headers = [];
        foreach ($request->headers->keys() as $key) {
            $headers[$key] = $request->headers->get($key);
        }

        return ClientHints::factory($headers);
    }
}
