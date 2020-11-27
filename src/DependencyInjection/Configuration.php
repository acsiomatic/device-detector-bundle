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

        if (method_exists($builder, 'root')) {
            // BC layer for Symfony 4.1 and older
            $rootNode = $builder->root($name);
        } else {
            $rootNode = $builder->getRootNode();
        }

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

            ->end()
        ;

        return $builder;
    }
}
