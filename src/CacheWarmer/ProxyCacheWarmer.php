<?php

namespace Acsiomatic\DeviceDetectorBundle\CacheWarmer;

use Acsiomatic\DeviceDetectorBundle\Factory\DeviceDetectorProxyFactory;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * @internal
 */
final class ProxyCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var DeviceDetectorProxyFactory
     */
    private $proxyFactory;

    public function __construct(DeviceDetectorProxyFactory $proxyFactory)
    {
        $this->proxyFactory = $proxyFactory;
    }

    public function isOptional(): bool
    {
        return false;
    }

    /**
     * @param string $cacheDir
     *
     * @return string[]
     */
    public function warmUp($cacheDir)
    {
        $proxyDir = $this->proxyFactory->getProxyDir();
        if (!is_dir($proxyDir)) {
            if (!mkdir($proxyDir, 0777, true) && !is_dir($proxyDir)) {
                throw new \RuntimeException(sprintf('Unable to create the DeviceDetector Proxy directory "%s".', $proxyDir));
            }
        } elseif (!is_writable($proxyDir)) {
            throw new \RuntimeException(sprintf('The DeviceDetector Proxy directory "%s" is not writeable for the current system user.', $proxyDir));
        }

        $this->proxyFactory->createDeviceDetectorProxy();

        $files = [];

        $names = scandir($proxyDir) ?: [];
        foreach ($names as $name) {
            $file = $proxyDir.'/'.$name;
            if (!is_dir($file)) {
                $files[] = $file;
            }
        }

        return $files;
    }
}
