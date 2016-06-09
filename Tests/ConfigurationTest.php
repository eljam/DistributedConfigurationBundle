<?php

namespace Eljam\KeyValueStoreBundle\Tests;

use Eljam\KeyValueStoreBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidStoreValue()
    {
        $configs = ['eljam_key_value_store' => ['store' => ['type' => 'wrong_store']]];

        $processor = new Processor();
        $configuration = new Configuration();

        $this->setExpectedException(InvalidConfigurationException::class);

        $config = $processor->processConfiguration($configuration, $configs);
    }

    public function testMultipleStoreConfiguration()
    {
        $configs =
        [
            'eljam_key_value_store' =>
            [
                'store' =>
                [
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
