<?php

namespace Acsiomatic\DeviceDetectorBundle\Factory;

use Acsiomatic\DeviceDetectorBundle\Contracts\DeviceDetectorFactoryInterface;
use DeviceDetector\Cache\PSR6Bridge;
use DeviceDetector\DeviceDetector;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
final class DeviceDetectorFactory implements DeviceDetectorFactoryInterface
{
    /**
     * @var bool
     */
    private $skipBotDetection;

    /**
     * @var bool
     */
    private $discardBotInformation;

    /**
     * @var CacheItemPoolInterface|null
     */
    private $cache;

    public function __construct(
        bool $skipBotDetection,
        bool $discardBotInformation,
        CacheItemPoolInterface $cache = null
    ) {
        $this->skipBotDetection = $skipBotDetection;
        $this->discardBotInformation = $discardBotInformation;
        $this->cache = $cache;
    }

    public function createDeviceDetector(): DeviceDetector
    {
        $detector = new DeviceDetector();

        $detector->skipBotDetection($this->skipBotDetection);
        $detector->discardBotInformation($this->discardBotInformation);

        if ($this->cache) {
            $detector->setCache(new PSR6Bridge($this->cache));
        }

        return $detector;
    }

    public static function createDeviceDetectorFromRequestStack(
        DeviceDetectorFactoryInterface $factory,
        RequestStack $requestStack
    ): DeviceDetector {
        $detector = $factory->createDeviceDetector();

        $request = $requestStack->getMasterRequest();
        if ($request) {
            // Third argument is a BC layer for Symfony 4.3 and older
            $userAgent = $request->headers->get('user-agent', '', true);
            $detector->setUserAgent((string) $userAgent);
        }

        return $detector;
    }
}
