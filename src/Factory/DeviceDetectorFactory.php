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
    private $skipBotDetection = false;

    /**
     * @var bool
     */
    private $discardBotInformation = false;

    /**
     * @var CacheItemPoolInterface|null
     */
    private $cache;

    /**
     * @var DeviceDetectorProxyFactory|null
     */
    private $proxyFactory;

    public function __construct(
        bool $skipBotDetection,
        bool $discardBotInformation,
        ?CacheItemPoolInterface $cache,
        ?DeviceDetectorProxyFactory $proxyFactory
    ) {
        $this->skipBotDetection = $skipBotDetection;
        $this->discardBotInformation = $discardBotInformation;
        $this->cache = $cache;
        $this->proxyFactory = $proxyFactory;
    }

    public function createDeviceDetector(): DeviceDetector
    {
        $detector = $this->proxyFactory !== null
            ? $this->proxyFactory->createDeviceDetectorProxy()
            : new DeviceDetector();

        $detector->skipBotDetection($this->skipBotDetection);
        $detector->discardBotInformation($this->discardBotInformation);

        if ($this->cache !== null) {
            $detector->setCache(new PSR6Bridge($this->cache));
        }

        return $detector;
    }

    public static function createDeviceDetectorFromRequestStack(
        DeviceDetectorFactoryInterface $factory,
        RequestStack $requestStack
    ): DeviceDetector {
        $detector = $factory->createDeviceDetector();

        $request = method_exists($requestStack, 'getMasterRequest')
            ? $requestStack->getMasterRequest() // BC for Symfony 5.2 and older
            : $requestStack->getMainRequest();

        if ($request) {
            $userAgent = $request->headers->get('user-agent', '');
            $detector->setUserAgent((string) $userAgent);
        }

        return $detector;
    }
}
