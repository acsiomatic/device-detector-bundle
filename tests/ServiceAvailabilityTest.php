<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests;

use Acsiomatic\DeviceDetectorBundle\AcsiomaticDeviceDetectorBundle;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler\CallbackContainerPass;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler\CompilerPassFactory;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\HttpKernel\Kernel;
use DeviceDetector\DeviceDetector;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ServiceAvailabilityTest extends TestCase
{
    /**
     * @return void
     */
    public function testDeviceDetectorServiceExistsAndIsPrivate()
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendCompilerPass(
            new CallbackContainerPass(
                static function (ContainerBuilder $containerBuilder) {
                    self::assertTrue($containerBuilder->hasDefinition(DeviceDetector::class));
                    self::assertFalse($containerBuilder->getDefinition(DeviceDetector::class)->isPublic());
                }
            )
        );

        $kernel->boot();
    }

    /**
     * @return void
     */
    public function testDeviceDetectorServiceMustNotBeAutomaticallyParsed()
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');

        static::assertFalse($deviceDetector->isParsed());
    }
}
