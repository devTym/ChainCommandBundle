<?php

namespace DevTym\ChainCommandBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Defines the configuration structure for the ChainCommandBundle.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Builds the configuration tree for the bundle.
     *
     * Configuration structure:
     * chain_command:
     *   options:
     *     logging: true|false
     *   chains:
     *     foo:hello:
     *       options:
     *          env: prod
     *       members:
     *         - command: bar:hi
     *           options: {}
     *
     * @return TreeBuilder The configuration tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('chain_command');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('logging')->defaultTrue()->end()
                    ->end()
                ->end()

                ->arrayNode('chains')
                    ->useAttributeAsKey('command')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('options')
                                ->useAttributeAsKey('option')
                                ->scalarPrototype()->end()
                                ->defaultValue([])
                            ->end()

                            ->arrayNode('members')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('command')->isRequired()->end()
                                        ->arrayNode('options')
                                            ->useAttributeAsKey('option')
                                            ->scalarPrototype()->end()
                                            ->defaultValue([])
                                        ->end()
                                    ->end()
                                ->end()
                                ->defaultValue([])
                            ->end()

                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
