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

namespace Maikuro\DistributedConfigurationBundle\Tests;

use Maikuro\DistributedConfigurationBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidStoreValue()
    {
        $configs = ['gundan_distributed_configuration' => ['store' => ['type' => 'wrong_store']]];

        $processor = new Processor();
        $configuration = new Configuration();

        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $config = $processor->processConfiguration($configuration, $configs);
    }

    public function testMultipleStoreConfiguration()
    {
        $configs =
        [
            'gundan_distributed_configuration' => [
                'store' => [
                    'predis' => ['client_id' => 'redis.client_id', 'scheme' => 'http'],
                ],
            ],
        ];

        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $configs);

        $this->assertEquals($config['store']['type'], 'predis');
        $this->assertEquals($config['store']['predis']['client_id'], 'redis.client_id');
    }
}
