<?php

namespace Acsiomatic\DeviceDetectorBundle\Factory;

use Acsiomatic\DeviceDetectorBundle\Contracts\ClientHintsFactoryInterface;
use Acsiomatic\DeviceDetectorBundle\Contracts\DeviceDetectorFactoryInterface;
use DeviceDetector\Cache\PSR6Bridge;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\AbstractBotParser;
use DeviceDetector\Parser\Client\AbstractClientParser;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 * @readonly
 */
final class DeviceDetectorFactory implements DeviceDetectorFactoryInterface
{
    public function __construct(
        private readonly bool $skipBotDetection,
        private readonly bool $discardBotInformation,
        private readonly int $versionTruncation,
        private readonly ClientHintsFactoryInterface $clientHintsFactory,
        private readonly ?CacheItemPoolInterface $cache,
        private readonly ?DeviceDetectorProxyFactory $proxyFactory,
        /** @var iterable<AbstractBotParser> */
        private readonly iterable $botParsers,
        /** @var iterable<AbstractClientParser> */
        private readonly iterable $clientParsers,
        /** @var iterable<AbstractDeviceParser> */
        private readonly iterable $deviceParsers,
    ) {}

    public function createDeviceDetector(): DeviceDetector
    {
        $detector = $this->proxyFactory instanceof DeviceDetectorProxyFactory
            ? $this->proxyFactory->createDeviceDetectorProxy()
            : new DeviceDetector();

        $detector->skipBotDetection($this->skipBotDetection);
        $detector->discardBotInformation($this->discardBotInformation);

        AbstractDeviceParser::setVersionTruncation($this->versionTruncation);

        if ($this->cache instanceof CacheItemPoolInterface) {
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

        $clientHints = $this->clientHintsFactory->createClientHintsFromRequest($request);
        $detector->setClientHints($clientHints);

        return $detector;
    }

    public static function createDeviceDetectorFromRequestStack(
        DeviceDetectorFactoryInterface $factory,
        RequestStack $requestStack,
    ): DeviceDetector {
        $request = $requestStack->getMainRequest();

        return $request instanceof Request
            ? $factory->createDeviceDetectorFromRequest($request)
            : $factory->createDeviceDetector();
    }
}
