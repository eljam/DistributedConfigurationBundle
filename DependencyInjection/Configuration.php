<?php

namespace Eljam\KeyValueStoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $self        = $this;
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('eljam_key_value_store');

        $supportedStores = ['dbal', 'predis', 'redis'];

        $normalization   = function ($conf) use ($self) {
            $conf['type'] = $self->resolveNodeType($conf);

            return $conf;
        };

        $rootNode
            ->children()
                ->arrayNode('store')
                    ->beforeNormalization()
                        ->ifTrue(function ($v) {
                            return ( ! isset($v['type']));
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
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->children()
                        ->scalarNode('service_id')->defaultNull()->end()
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

        $type   = key($parameters);

        return $type;
    }

    /**
     * Build riak node configuration definition
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    private function addPredisNode()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('predis');
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
     * Build riak node configuration definition
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    private function addDbalNode()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('dbal');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('connection_id')->defaultNull()->end()
                ->scalarNode('driver')->defaultValue('pdo_mysql')->end()
                ->scalarNode('dbname')->defaultValue('db')->end()
                ->scalarNode('user')->defaultNull()->end()
                ->scalarNode('password')->defaultNull()->end()
                ->scalarNode('charset')->defaultNull()->end()
                ->scalarNode('table')->defaultValue('meta_key')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Build riak node configuration definition
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    private function addRedisNode()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('redis');
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
