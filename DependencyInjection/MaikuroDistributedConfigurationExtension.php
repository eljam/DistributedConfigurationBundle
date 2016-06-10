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

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MaikuroDistributedConfigurationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $this->loadStoreHandler($container, $config);
    }

    /**
     * loadStoreHandler.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function loadStoreHandler(ContainerBuilder $container, array $config)
    {
        $storeHandlerDef = new Definition('Maikuro\DistributedConfigurationBundle\Handler\StoreHandler');
        $storeDef = $this->loadStore($container, $config['store']);
        if (null === $storeDef) {
            throw new InvalidArgumentException('No store found');
        }
        $storeHandlerDef->addArgument($storeDef);
        //Enabled caching
        if ($config['cache']['enabled']) {
            if (null === $config['cache']['service_id']) {
                throw new InvalidArgumentException(
                    'You need to install DoctrineCacheBundle and configure it to use cache feature'
                );
            }
            $cachingDecoratorDef = new Definition('Webmozart\KeyValueStore\Decorator\CachingDecorator',
                [$storeDef, new Reference($config['cache']['service_id'])]
            );
            $storeHandlerDef->replaceArgument(0, $cachingDecoratorDef);
        }
        $container->setDefinition('maikuro_distributed_configuration.store_handler', $storeHandlerDef);
    }

    private function loadStore(ContainerBuilder $container, array $config)
    {
        switch ($config['type']) {
            case 'redis':
                return $this->loadRedisStore($container, $config);
                break;
            case 'predis':
                return $this->loadPredisStore($container, $config);
                break;
            case 'dbal':
                return $this->loadDbalStore($container, $config);
                break;
            case 'json':
                return $this->loadJsonStore($container, $config);
                break;
            default:
                return;
                break;
        }
    }

    private function loadRedisStore(ContainerBuilder $container, array $config)
    {
        $redisStoreDef = new Definition('Webmozart\KeyValueStore\PhpRedisStore');

        if (isset($config['store']['connection_id'])) {
            $redisStoreDef->addArgument(new Reference($config['connection_id']));
        } else {
            $host = $config['host'];
            $port = $config['port'];
            $connId = 'maikuro_distributed_configuration.redis.connection';
            $connDef = new Definition('Redis');
            $connParams = array($host, $port);
            if (isset($config['timeout'])) {
                $connParams[] = $config['timeout'];
            }
            $connMethod = 'connect';
            if (isset($config['persistent']) && $config['persistent']) {
                $connMethod = 'pconnect';
            }
            $connDef->setPublic(false);
            $connDef->addMethodCall($connMethod, $connParams);
            if (isset($config['password'])) {
                $password = $config['password'];
                $connDef->addMethodCall('auth', array($password));
            }
            if (isset($config['database'])) {
                $database = (int) $config['database'];
                $connDef->addMethodCall('select', array($database));
            }
            $container->setDefinition($connId, $connDef);

            $redisStoreDef->addArgument(new Reference($connId));
        }

        return $redisStoreDef;
    }

    private function loadPredisStore(ContainerBuilder $container, array $config)
    {
        $predisStoreDef = new Definition('Webmozart\KeyValueStore\PredisStore');

        if (isset($config['client_id'])) {
            $predisStoreDef->addArgument(new Reference($config['connection_id']));
        } else {
            $parameters = array(
                'scheme' => $config['scheme'],
                'host' => $config['host'],
                'port' => $config['port'],
            );
            if ($config['password']) {
                $parameters['password'] = $config['password'];
            }
            if ($config['timeout']) {
                $parameters['timeout'] = $config['timeout'];
            }
            if ($config['database']) {
                $parameters['database'] = $config['database'];
            }
            $options = null;
            if (isset($config['options'])) {
                $options = $config['options'];
            }
            $clientId = 'maikuro_distributed_configuration.predis.client';
            $clientDef = new Definition('Predis\Client');
            $clientDef->addArgument($parameters);
            $clientDef->addArgument($options);
            $clientDef->setPublic(false);
            $container->setDefinition($clientId, $clientDef);

            $predisStoreDef->addArgument(new Reference($clientId));
        }

        return $predisStoreDef;
    }

    private function loadJsonStore(ContainerBuilder $container, array $config)
    {
        $jsonStoreDef = new Definition('Webmozart\KeyValueStore\JsonFileStore');
        $jsonStoreDef->addArgument($config['json']['path']);

        return $jsonStoreDef;
    }

    private function loadDbalStore(ContainerBuilder $container, array $config)
    {
        $dbalStoreDef = new Definition('Webmozart\KeyValueStore\DbalStore');

        if (!isset($config['connection_id'])) {
            throw new InvalidArgumentException('No dbal connection configured');
        }

        $dbalStoreDef->addArgument(new Reference($config['connection_id']), $config['table_name']);

        return $dbalStoreDef;
    }
}
