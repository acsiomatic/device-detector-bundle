<?php

namespace Acsiomatic\DeviceDetectorBundle\DependencyInjection;

use Acsiomatic\DeviceDetectorBundle\CacheWarmer\ProxyCacheWarmer;
use Acsiomatic\DeviceDetectorBundle\Contracts\DeviceDetectorFactoryInterface;
use Acsiomatic\DeviceDetectorBundle\Factory\DeviceDetectorFactory;
use Acsiomatic\DeviceDetectorBundle\Factory\DeviceDetectorProxyFactory;
use Acsiomatic\DeviceDetectorBundle\Twig\TwigExtension;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\AbstractBotParser;
use DeviceDetector\Parser\Client\AbstractClientParser;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
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
     * @var string
     */
    private const BOT_PARSER_TAG = 'acsiomatic.device_detector.bot_parser';

    /**
     * @var string
     */
    private const CLIENT_PARSER_TAG = 'acsiomatic.device_detector.client_parser';

    /**
     * @var string
     */
    private const DEVICE_PARSER_TAG = 'acsiomatic.device_detector.device_parser';

    /**
     * @param array<string, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        /** @var BundleConfigArray $config */
        $config = $this->processConfiguration($configuration, $configs);

        $this->setupParsers($container);
        $this->setupProxy($container, $config);
        $this->setupFactory($container, $config);
        $this->setupDeviceDetector($container);
        $this->setupTwig($container, $config);
    }

    /**
     * @param BundleConfigArray $config
     */
    private function setupProxy(ContainerBuilder $container, array $config): void
    {
        if (!$config['auto_parse']) {
            return;
        }

        $container
            ->register(ProxyCacheWarmer::class, ProxyCacheWarmer::class)
            ->setPublic(false)
            ->addTag('kernel.cache_warmer')
            ->setArguments([
                new Reference(DeviceDetectorProxyFactory::class),
                $container->getParameter('kernel.cache_dir'),
            ]);

        $container
            ->register(DeviceDetectorProxyFactory::class, DeviceDetectorProxyFactory::class)
            ->setPublic(false)
            ->setArguments([
                $container->getParameter('kernel.cache_dir'),
            ]);
    }

    /**
     * @param BundleConfigArray $config
     */
    private function setupFactory(ContainerBuilder $container, array $config): void
    {
        $container
            ->register(DeviceDetectorFactoryInterface::class, DeviceDetectorFactory::class)
            ->setPublic(false)
            ->setArguments([
                $config['bot']['skip_detection'],
                $config['bot']['discard_information'],
                $config['cache']['pool'] !== null ? new Reference($config['cache']['pool']) : null,
                $config['auto_parse'] ? new Reference(DeviceDetectorProxyFactory::class) : null,
                new TaggedIteratorArgument(self::BOT_PARSER_TAG),
                new TaggedIteratorArgument(self::CLIENT_PARSER_TAG),
                new TaggedIteratorArgument(self::DEVICE_PARSER_TAG),
            ]);
    }

    private function setupDeviceDetector(ContainerBuilder $container): void
    {
        $container
            ->register(DeviceDetector::class, DeviceDetector::class)
            ->setPublic(false)
            ->setFactory([DeviceDetectorFactory::class, 'createDeviceDetectorFromRequestStack'])
            ->setArguments([
                new Reference(DeviceDetectorFactoryInterface::class),
                new Reference(RequestStack::class),
            ]);
    }

    /**
     * @param BundleConfigArray $config
     */
    private function setupTwig(ContainerBuilder $container, array $config): void
    {
        if ($config['twig']['variable_name'] === null || !class_exists(Environment::class)) {
            return;
        }

        $container
            ->register(TwigExtension::class, TwigExtension::class)
            ->setPublic(false)
            ->addTag('twig.extension')
            ->setArguments([
                $config['twig']['variable_name'],
                new Reference(DeviceDetector::class),
            ]);
    }

    private function setupParsers(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(AbstractBotParser::class)
            ->addTag(self::BOT_PARSER_TAG);

        $container
            ->registerForAutoconfiguration(AbstractClientParser::class)
            ->addTag(self::CLIENT_PARSER_TAG);

        $container
            ->registerForAutoconfiguration(AbstractDeviceParser::class)
            ->addTag(self::DEVICE_PARSER_TAG);
    }
}
