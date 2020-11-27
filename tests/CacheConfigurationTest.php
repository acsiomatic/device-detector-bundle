<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests;

use Acsiomatic\DeviceDetectorBundle\AcsiomaticDeviceDetectorBundle;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler\CompilerPassFactory;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\HttpKernel\Kernel;
use DeviceDetector\DeviceDetector;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Cache\Adapter\TraceableAdapter;

final class CacheConfigurationTest extends TestCase
{
    /**
     * @return void
     */
    public function testCacheAppIsAutomaticallyAttachedToDeviceDetectorService()
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendCompilerPass(CompilerPassFactory::createTraceableCache('cache.app'));
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('cache.app.public', 'cache.app'));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent('Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0');

        /** @var TraceableAdapter $cacheApp */
        $cacheApp = $kernel->getContainer()->get('cache.app.public');

        $cacheApp->clearCalls();
        $deviceDetector->parse();

        static::assertNotEmpty($cacheApp->getCalls());
    }

    /**
     * @return void
     */
    public function testCustomPoolConfiguration()
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['cache' => ['pool' => 'cache.not_app']]);
        $kernel->appendCompilerPass(CompilerPassFactory::createTraceableCache('cache.app'));
        $kernel->appendCompilerPass(CompilerPassFactory::createTraceableCache('cache.not_app'));
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('cache.app.public', 'cache.app'));
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('cache.not_app.public', 'cache.not_app'));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent('Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0');

        /** @var TraceableAdapter $cacheApp */
        $cacheApp = $kernel->getContainer()->get('cache.app.public');

        /** @var TraceableAdapter $cacheNotApp */
        $cacheNotApp = $kernel->getContainer()->get('cache.not_app.public');

        $cacheApp->clearCalls();
        $cacheNotApp->clearCalls();
        $deviceDetector->parse();

        static::assertEmpty($cacheApp->getCalls());
        static::assertNotEmpty($cacheNotApp->getCalls());
    }

    /**
     * @return void
     */
    public function testDisablingCacheConfiguration()
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['cache' => ['pool' => null]]);
        $kernel->appendCompilerPass(CompilerPassFactory::createTraceableCache('cache.app'));
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('cache.app.public', 'cache.app'));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent('Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0');

        /** @var TraceableAdapter $cacheApp */
        $cacheApp = $kernel->getContainer()->get('cache.app.public');

        $cacheApp->clearCalls();
        $deviceDetector->parse();

        static::assertEmpty($cacheApp->getCalls());
    }
}
