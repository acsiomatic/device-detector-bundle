<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler;

use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Adapter\TraceableAdapter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

abstract class CompilerPassFactory
{
    public static function createPublicAlias(string $alias, string $id): CompilerPassInterface
    {
        return new CallbackContainerPass(
            static function (ContainerBuilder $containerBuilder) use ($alias, $id) {
                $containerBuilder
                    ->setAlias($alias, $id)
                    ->setPublic(true)
                ;
            }
        );
    }

    public static function createTraceableCache(string $id): CompilerPassInterface
    {
        return new CallbackContainerPass(
            static function (ContainerBuilder $containerBuilder) use ($id) {
                if (!$containerBuilder->hasDefinition($id)) {
                    $containerBuilder->register($id, NullAdapter::class);
                }

                $containerBuilder
                    ->register(sprintf('%s.traceable', $id), TraceableAdapter::class)
                    ->setDecoratedService($id, sprintf('%s.inner', $id))
                    ->addArgument(new Reference(sprintf('%s.inner', $id)))
                ;
            }
        );
    }
}
