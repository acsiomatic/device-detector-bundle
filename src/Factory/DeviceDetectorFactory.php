<?php

namespace Acsiomatic\DeviceDetectorBundle\Factory;

use Acsiomatic\DeviceDetectorBundle\Contracts\DeviceDetectorFactoryInterface;
use DeviceDetector\Cache\PSR6Bridge;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\AbstractBotParser;
use DeviceDetector\Parser\Client\AbstractClientParser;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
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

    /**
     * @var iterable<AbstractBotParser>
     */
    private $botParsers = [];

    /**
     * @var iterable<AbstractClientParser>
     */
    private $clientParsers = [];

    /**
     * @var iterable<AbstractDeviceParser>
     */
    private $deviceParsers = [];

    /**
     * @param iterable<AbstractBotParser>    $botParsers
     * @param iterable<AbstractClientParser> $clientParsers
     * @param iterable<AbstractDeviceParser> $deviceParsers
     */
    public function __construct(
        bool $skipBotDetection,
        bool $discardBotInformation,
        ?CacheItemPoolInterface $cache,
        ?DeviceDetectorProxyFactory $proxyFactory,
        iterable $botParsers,
        iterable $clientParsers,
        iterable $deviceParsers
    ) {
        $this->skipBotDetection = $skipBotDetection;
        $this->discardBotInformation = $discardBotInformation;
        $this->cache = $cache;
        $this->proxyFactory = $proxyFactory;
        $this->botParsers = $botParsers;
        $this->clientParsers = $clientParsers;
        $this->deviceParsers = $deviceParsers;
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

        foreach ($this->botParsers as $botParser) {
            $detector->addBotParser($botParser);
        }

        foreach ($this->clientParsers as $botParser) {
            $detector->addClientParser($botParser);
        }

        foreach ($this->deviceParsers as $botParser) {
            $detector->addDeviceParser($botParser);
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
