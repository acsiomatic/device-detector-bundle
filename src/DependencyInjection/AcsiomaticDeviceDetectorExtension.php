<?php

namespace Acsiomatic\DeviceDetectorBundle\DependencyInjection;

use Acsiomatic\DeviceDetectorBundle\Factory\DeviceDetectorFactory;
use Acsiomatic\DeviceDetectorBundle\Twig\TwigExtension;
use DeviceDetector\DeviceDetector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

/**
 * @internal
 */
final class AcsiomaticDeviceDetectorExtension extends Extension
{
    /**
     * @param array<string, mixed> $configs
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container
            ->register(DeviceDetector::class, DeviceDetector::class)
            ->setPublic(false)
            ->setFactory([DeviceDetectorFactory::class, 'create'])
            ->setArguments([
                new Reference(RequestStack::class),
                $config['bot']['skip_detection'],
                $config['bot']['discard_information'],
                null !== $config['cache']['pool'] ? new Reference($config['cache']['pool']) : null,
            ])
        ;

        if (null !== $config['twig']['variable_name'] && class_exists(Environment::class)) {
            $container
                ->register(TwigExtension::class, TwigExtension::class)
                ->setPublic(false)
                ->addTag('twig.extension')
                ->setArguments([
                    $config['twig']['variable_name'],
                    new Reference(DeviceDetector::class),
                ])
            ;
        }
    }
}
