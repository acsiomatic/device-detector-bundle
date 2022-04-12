<?php

namespace Acsiomatic\DeviceDetectorBundle\Factory;

use Acsiomatic\DeviceDetectorBundle\Contracts\ClientHintsFactoryInterface;
use Acsiomatic\DeviceDetectorBundle\Contracts\DeviceDetectorFactoryInterface;
use DeviceDetector\Cache\PSR6Bridge;
use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\AbstractBotParser;
use DeviceDetector\Parser\Client\AbstractClientParser;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
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
     * @var ClientHintsFactoryInterface
     */
    private $clientHintsFactory;

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
        ClientHintsFactoryInterface $clientHintsFactory,
        ?CacheItemPoolInterface $cache,
        ?DeviceDetectorProxyFactory $proxyFactory,
        iterable $botParsers,
        iterable $clientParsers,
        iterable $deviceParsers
    ) {
        $this->skipBotDetection = $skipBotDetection;
        $this->discardBotInformation = $discardBotInformation;
        $this->clientHintsFactory = $clientHintsFactory;
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

    public function createDeviceDetectorFromRequest(Request $request): DeviceDetector
    {
        $detector = $this->createDeviceDetector();

        $userAgent = $request->headers->get('user-agent', '');
        $detector->setUserAgent((string) $userAgent);

        if (class_exists(ClientHints::class) && method_exists($detector, 'setClientHints')) {
            $clientHints = $this->clientHintsFactory->createClientHintsFromRequest($request);
            $detector->setClientHints($clientHints);
        }

        return $detector;
    }

    public static function createDeviceDetectorFromRequestStack(
        DeviceDetectorFactoryInterface $factory,
        RequestStack $requestStack
    ): DeviceDetector {
        $request = method_exists($requestStack, 'getMasterRequest')
            ? $requestStack->getMasterRequest() // BC for Symfony 5.2 and older
            : $requestStack->getMainRequest();

        if ($request) {
            return $factory->createDeviceDetectorFromRequest($request);
        }

        return $factory->createDeviceDetector();
    }
}
