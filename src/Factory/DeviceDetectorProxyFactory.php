<?php

namespace Acsiomatic\DeviceDetectorBundle\Factory;

use DeviceDetector\DeviceDetector;
use ProxyManager\Configuration;
use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;

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
        $parseCaller = static function (
            DeviceDetector $proxy,
            DeviceDetector $real
        ): void {
            if (!$real->isParsed()) {
                $real->parse();
            }
        };

        $magicParseCaller = static function (
            DeviceDetector $proxy,
            DeviceDetector $real,
            string $method,
            array $arguments
        ): void {
            if (!$real->isParsed() && 0 === stripos($arguments['methodName'] ?? '', 'is')) {
                $real->parse();
            }
        };

        $configuration = $this->createConfiguration();
        $factory = new AccessInterceptorValueHolderFactory($configuration);

        return $factory->createProxy(new DeviceDetector(), [
            '__call' => $magicParseCaller,
            'getBot' => $parseCaller,
            'getBrand' => $parseCaller,
            'getBrandName' => $parseCaller,
            'getClient' => $parseCaller,
            'getDevice' => $parseCaller,
            'getDeviceName' => $parseCaller,
            'getModel' => $parseCaller,
            'getOs' => $parseCaller,
            'isBot' => $parseCaller,
            'isBrowser' => $parseCaller,
            'isDesktop' => $parseCaller,
            'isMobile' => $parseCaller,
        ]);
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
}
