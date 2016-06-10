<?php

/*
 * This file is part of the distributed-configuration-bundle package
 *
 * Copyright (c) 2016 Guillaume Cavana
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Guillaume Cavana <guillaume.cavana@gmail.com>
 */

namespace Maikuro\DistributedConfigurationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $self = $this;
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('maikuro_distributed_configuration');

        $supportedStores = ['dbal', 'predis', 'redis', 'json'];

        $normalization = function ($conf) use ($self) {

            $conf['type'] = $self->resolveNodeType($conf);

            return $conf;
        };

        $rootNode
            ->children()
                ->arrayNode('store')
                    ->beforeNormalization()
                        ->ifTrue(function ($v) {
                            return  !isset($v['type']);
                        })
                        ->then($normalization)
                    ->end()
                    ->children()
                        ->scalarNode('type')
                            ->validate()
                                ->ifNotInArray($supportedStores)
                                ->thenInvalid('The store %s is not supported. Please choose one of '.json_encode($supportedStores))
                            ->end()
                        ->end()
                        ->append($this->addRedisNode())
                        ->append($this->addPredisNode())
                        ->append($this->addDbalNode())
                        ->append($this->addJsonFileNode())
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('enabled')->defaultFalse()->end()
                        ->scalarNode('service_id')->defaultNull()->end()
                        ->scalarNode('default_ttl')->defaultValue('3600')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    public function resolveNodeType(array $parameters)
    {
        if (isset($parameters['type'])) {
            unset($parameters['type']);
        }

        $type = key($parameters);

        return $type;
    }

    /**
     * Build Predis node configuration definition.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    private function addPredisNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('predis');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('client_id')->defaultNull()->end()
                ->scalarNode('scheme')->defaultValue('tcp')->end()
                ->scalarNode('host')->defaultValue('localhost')->end()
                ->scalarNode('port')->defaultValue('6379')->end()
                ->scalarNode('password')->defaultNull()->end()
                ->scalarNode('timeout')->defaultNull()->end()
                ->scalarNode('database')->defaultNull()->end()
                ->arrayNode('options')
                  ->useAttributeAsKey('name')
                  ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Build JsonFile node configuration definition.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    private function addJsonFileNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('json');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('path')->defaultValue('%kernel.root_dir%/configuration.json')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Build dbal node configuration definition.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    private function addDbalNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('dbal');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('connection_id')->defaultNull()->end()
                ->scalarNode('table_name')->defaultValue('configuration')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Build Redis node configuration definition.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    private function addRedisNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('redis');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('connection_id')->defaultNull()->end()
                ->scalarNode('host')->defaultValue('localhost')->end()
                ->scalarNode('port')->defaultValue('6379')->end()
                ->scalarNode('password')->defaultNull()->end()
                ->scalarNode('timeout')->defaultNull()->end()
                ->scalarNode('database')->defaultNull()->end()
                ->booleanNode('persistent')->defaultFalse()->end()
            ->end()
        ;

        return $node;
    }
}
