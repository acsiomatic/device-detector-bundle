<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests;

use Acsiomatic\DeviceDetectorBundle\AcsiomaticDeviceDetectorBundle;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler\CallbackContainerPass;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\HttpKernel\Kernel;
use DeviceDetector\DeviceDetector;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RoutingConditionServiceTest extends TestCase
{
    public function testDeviceDetectorIsTaggedAsRoutingConditionService(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendCompilerPass(
            new CallbackContainerPass(
                static function (ContainerBuilder $containerBuilder): void {
                    self::assertTrue($containerBuilder->hasDefinition(DeviceDetector::class));

                    $definition = $containerBuilder->getDefinition(DeviceDetector::class);

                    self::assertTrue($definition->hasTag('routing.condition_service'));
                    self::assertSame([['alias' => 'device']], $definition->getTag('routing.condition_service'));
                }
            )
        );

        $kernel->boot();
    }

    public function testHonorCustomAlias(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['routing' => ['condition_service_alias' => 'custom']]);
        $kernel->appendCompilerPass(
            new CallbackContainerPass(
                static function (ContainerBuilder $containerBuilder): void {
                    self::assertTrue($containerBuilder->hasDefinition(DeviceDetector::class));

                    $definition = $containerBuilder->getDefinition(DeviceDetector::class);

                    self::assertTrue($definition->hasTag('routing.condition_service'));
                    self::assertSame([['alias' => 'custom']], $definition->getTag('routing.condition_service'));
                }
            )
        );

        $kernel->boot();
    }

    public function testDisabling(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['routing' => ['condition_service_alias' => null]]);
        $kernel->appendCompilerPass(
            new CallbackContainerPass(
                static function (ContainerBuilder $containerBuilder): void {
                    self::assertTrue($containerBuilder->hasDefinition(DeviceDetector::class));
                    self::assertFalse($containerBuilder->getDefinition(DeviceDetector::class)->hasTag('routing.condition_service'));
                }
            )
        );

        $kernel->boot();
    }
}
