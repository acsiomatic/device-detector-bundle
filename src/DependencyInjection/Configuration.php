<?php

namespace Acsiomatic\DeviceDetectorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    private const NAME = 'acsiomatic_device_detector';

    /**
     * @var string
     */
    private const CACHE_POOL = 'cache.app';

    /**
     * @var string
     */
    private const TWIG_VARIABLE = 'device';

    /**
     * @var string
     */
    private const VERSION_TRUNCATE = 'minor';

    /**
     * @var string
     */
    private const ROUTING_CONDITION_SERVICE_ALIAS = 'device';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::NAME);

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $rootNodeChildren = $rootNode->children();

        $this->buildCache($rootNodeChildren);
        $this->buildBot($rootNodeChildren);
        $this->buildTwig($rootNodeChildren);
        $this->buildVersionTruncate($rootNodeChildren);
        $this->buildRouting($rootNodeChildren);

        $rootNodeChildren->booleanNode('auto_parse')->defaultTrue();

        return $treeBuilder;
    }

    private function buildCache(NodeBuilder $node): void
    {
        $children = $node
            ->arrayNode('cache')
            ->addDefaultsIfNotSet()
            ->children();

        $children->scalarNode('pool')->defaultValue(self::CACHE_POOL);
    }

    private function buildBot(NodeBuilder $node): void
    {
        $children = $node
            ->arrayNode('bot')
            ->addDefaultsIfNotSet()
            ->children();

        $children->booleanNode('skip_detection')->defaultFalse();
        $children->booleanNode('discard_information')->defaultFalse();
    }

    private function buildTwig(NodeBuilder $node): void
    {
        $children = $node
            ->arrayNode('twig')
            ->addDefaultsIfNotSet()
            ->children();

        $children->scalarNode('variable_name')->defaultValue(self::TWIG_VARIABLE);
    }

    private function buildVersionTruncate(NodeBuilder $node): void
    {
        $node
            ->enumNode('version_truncation')
            ->values(['major', 'minor', 'patch', 'build', 'none'])
            ->defaultValue(self::VERSION_TRUNCATE);
    }

    private function buildRouting(NodeBuilder $node): void
    {
        $children = $node
            ->arrayNode('routing')
            ->addDefaultsIfNotSet()
            ->children();

        $children->scalarNode('condition_service_alias')->defaultValue(self::ROUTING_CONDITION_SERVICE_ALIAS);
    }
}
