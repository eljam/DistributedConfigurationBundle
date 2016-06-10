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

use Maikuro\DistributedConfigurationBundle\Handler\StoreHandler;
use Maikuro\DistributedConfigurationBundle\Model\KeyValue;
use Webmozart\KeyValueStore\ArrayStore;

class StoreHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * testStoringKeyWithHandler.
     */
    public function testStoringKeyWithHandler()
    {
        $keyValue = $this->createKeyValue();
        $store = new ArrayStore();
        $storeHandler = new StoreHandler($store);
        $storeHandler->flush($keyValue);

        $this->assertEquals($keyValue, $storeHandler->get('test_key'));
    }

    /**
     * testStoringKeyWithHandler.
     */
    public function testRemoveKey()
    {
        $keyValue = $this->createKeyValue();
        $store = new ArrayStore();
        $storeHandler = new StoreHandler($store);
        $storeHandler->flush($keyValue);
        $storeHandler->remove('test_key');

        $this->setExpectedException('Webmozart\KeyValueStore\Api\NoSuchKeyException');

        $storeHandler->get('test_key');

        $this->assertEquals($keyValue, $storeHandler->get('test_key'));
    }

    private function createKeyValue()
    {
        $keyValue = new KeyValue();
        $keyValue->setKey('test_key');
        $keyValue->setValue('test_value');

        return $keyValue;
    }
}
