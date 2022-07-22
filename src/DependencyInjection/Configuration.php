<?php

namespace Acsiomatic\DeviceDetectorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $name = 'acsiomatic_device_detector';
        $builder = new TreeBuilder($name);

        $rootNode = method_exists($builder, 'root')
            ? $builder->root($name) // BC layer for Symfony 4.1 and older
            : $builder->getRootNode();

        $rootNode
            ->children()

            ->arrayNode('cache')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('pool')->defaultValue('cache.app')->end()
            ->end()
            ->end()

            ->arrayNode('bot')
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('skip_detection')->defaultFalse()->end()
            ->booleanNode('discard_information')->defaultFalse()->end()
            ->end()
            ->end()

            ->arrayNode('twig')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('variable_name')->defaultValue('device')->end()
            ->end()
            ->end()

            ->booleanNode('auto_parse')->defaultTrue()->end()

            ->enumNode('version_truncation')
            ->values(['major', 'minor', 'patch', 'build', 'none'])
            ->defaultValue('minor')
            ->end()

            ->arrayNode('routing')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('condition_service_alias')->defaultValue('device')->end()
            ->end()
            ->end()

            ->end()
        ;

        return $builder;
    }
}
