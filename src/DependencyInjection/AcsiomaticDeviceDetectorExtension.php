<?php

namespace Acsiomatic\DeviceDetectorBundle\DependencyInjection;

use Acsiomatic\DeviceDetectorBundle\CacheWarmer\ProxyCacheWarmer;
use Acsiomatic\DeviceDetectorBundle\Contracts\ClientHintsFactoryInterface;
use Acsiomatic\DeviceDetectorBundle\Contracts\DeviceDetectorFactoryInterface;
use Acsiomatic\DeviceDetectorBundle\Factory\ClientHintsFactory;
use Acsiomatic\DeviceDetectorBundle\Factory\DeviceDetectorFactory;
use Acsiomatic\DeviceDetectorBundle\Factory\DeviceDetectorProxyFactory;
use Acsiomatic\DeviceDetectorBundle\Twig\TwigExtension;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\AbstractBotParser;
use DeviceDetector\Parser\AbstractParser;
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
     * @var array<string, int>
     */
    private const VERSION_TRUNCATION_MAP = [
        'major' => AbstractParser::VERSION_TRUNCATION_MAJOR,
        'minor' => AbstractParser::VERSION_TRUNCATION_MINOR,
        'patch' => AbstractParser::VERSION_TRUNCATION_PATCH,
        'build' => AbstractParser::VERSION_TRUNCATION_BUILD,
        'none' => AbstractParser::VERSION_TRUNCATION_NONE,
    ];

    /**
     * @param array<mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        /** @var BundleConfigArray $config */
        $config = $this->processConfiguration($configuration, $configs);

        $this->setupParsers($container);
        $this->setupProxy($container, $config);
        $this->setupClientHintsFactory($container);
        $this->setupDeviceDetectorFactory($container, $config);
        $this->setupDeviceDetector($container, $config);
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

    private function setupClientHintsFactory(ContainerBuilder $container): void
    {
        $container
            ->register(ClientHintsFactoryInterface::class, ClientHintsFactory::class)
            ->setPublic(false);
    }

    /**
     * @param BundleConfigArray $config
     */
    private function setupDeviceDetectorFactory(ContainerBuilder $container, array $config): void
    {
        $container
            ->register(DeviceDetectorFactoryInterface::class, DeviceDetectorFactory::class)
            ->setPublic(false)
            ->setArguments([
                $config['bot']['skip_detection'],
                $config['bot']['discard_information'],
                self::VERSION_TRUNCATION_MAP[$config['version_truncation']],
                new Reference(ClientHintsFactoryInterface::class),
                $config['cache']['pool'] !== null ? new Reference($config['cache']['pool']) : null,
                $config['auto_parse'] ? new Reference(DeviceDetectorProxyFactory::class) : null,
                new TaggedIteratorArgument(self::BOT_PARSER_TAG),
                new TaggedIteratorArgument(self::CLIENT_PARSER_TAG),
                new TaggedIteratorArgument(self::DEVICE_PARSER_TAG),
            ]);
    }

    /**
     * @param BundleConfigArray $config
     */
    private function setupDeviceDetector(ContainerBuilder $container, array $config): void
    {
        $definition = $container
            ->register(DeviceDetector::class, DeviceDetector::class)
            ->setPublic(false)
            ->setFactory([DeviceDetectorFactory::class, 'createDeviceDetectorFromRequestStack'])
            ->setArguments([
                new Reference(DeviceDetectorFactoryInterface::class),
                new Reference(RequestStack::class),
            ]);

        if ($config['routing']['condition_service_alias'] !== null) {
            $definition->addTag('routing.condition_service', [
                'alias' => $config['routing']['condition_service_alias'],
            ]);
        }
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
