<?php

namespace Acsiomatic\DeviceDetectorBundle\Factory;

use Acsiomatic\DeviceDetectorBundle\Decoration\DecoratedDeviceDetector;
use DeviceDetector\Cache\PSR6Bridge;
use DeviceDetector\DeviceDetector;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
abstract class DeviceDetectorFactory
{
    public static function create(
        RequestStack $stack,
        bool $skipBotDetection,
        bool $discardBotInformation,
        CacheItemPoolInterface $cache = null
    ): DeviceDetector {
        $detector = new DecoratedDeviceDetector();

        $detector->skipBotDetection($skipBotDetection);
        $detector->discardBotInformation($discardBotInformation);

        if ($cache) {
            $detector->setCache(new PSR6Bridge($cache));
        }

        $request = $stack->getMasterRequest();
        if ($request) {
            // Third argument is a BC layer for Symfony 4.3 and older
            $userAgent = $request->headers->get('user-agent', '', true);
            $detector->setUserAgent((string) $userAgent);
        }

        return $detector;
    }
}
