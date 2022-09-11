<?php

namespace Acsiomatic\DeviceDetectorBundle\Factory;

use DeviceDetector\DeviceDetector;
use ProxyManager\Configuration;
use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\Proxy\AccessInterceptorInterface;

/**
 * @internal
 */
final class DeviceDetectorProxyFactory
{
    /**
     * @var string
     */
    private const NAMESPACE = 'AcsiomaticDeviceDetectorBundle';

    /**
     * @var string
     */
    private $proxyDir;

    public function __construct(string $cacheDir)
    {
        $this->proxyDir = $cacheDir.'/'.self::NAMESPACE;
    }

    public function getProxyDir(): string
    {
        return $this->proxyDir;
    }

    public function createDeviceDetectorProxy(): DeviceDetector
    {
        $configuration = $this->createConfiguration();

        $instance = new DeviceDetector();

        $autoParserForMagicCall = $this->buildAutoParserCallerForMagicCall();
        $autoParser = $this->buildAutoParserCaller();
        $prefixInterceptors = [
            '__call' => $autoParserForMagicCall,
            'getBot' => $autoParser,
            'getBrand' => $autoParser,
            'getBrandName' => $autoParser,
            'getClient' => $autoParser,
            'getDevice' => $autoParser,
            'getDeviceName' => $autoParser,
            'getModel' => $autoParser,
            'getOs' => $autoParser,
            'isBot' => $autoParser,
            'isBrowser' => $autoParser,
            'isDesktop' => $autoParser,
            'isMobile' => $autoParser,
        ];

        return (new AccessInterceptorValueHolderFactory($configuration))
            ->createProxy(
                $instance,
                $prefixInterceptors
            );
    }

    private function createConfiguration(): Configuration
    {
        $config = new Configuration();
        $config->setProxiesNamespace(self::NAMESPACE);
        $config->setGeneratorStrategy(new FileWriterGeneratorStrategy(new FileLocator($this->proxyDir)));
        $config->setProxiesTargetDir($this->proxyDir);

        $config->getProxyAutoloader();

        return $config;
    }

    /**
     * @return callable(
     *     AccessInterceptorInterface<DeviceDetector> $proxy,
     *     DeviceDetector $real,
     *     string $method,
     *     array $arguments,
     * )
     */
    private function buildAutoParserCallerForMagicCall(): callable
    {
        return static function (
            AccessInterceptorInterface $proxy,
            DeviceDetector $real,
            string $method,
            array $arguments
        ): void {
            if (!$real->isParsed() && 0 === stripos($arguments['methodName'] ?? '', 'is')) {
                $real->parse();
            }
        };
    }

    /**
     * @return callable(
     *     AccessInterceptorInterface<DeviceDetector> $proxy,
     *     DeviceDetector $real,
     * )
     */
    private function buildAutoParserCaller(): callable
    {
        return static function (
            AccessInterceptorInterface $proxy,
            DeviceDetector $real
        ): void {
            if (!$real->isParsed()) {
                $real->parse();
            }
        };
    }
}
