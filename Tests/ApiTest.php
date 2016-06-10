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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Webmozart\KeyValueStore\JsonFileStore;

class ApiTest extends WebTestCase
{
    private $client;

    protected function setUp()
    {
        $this->client = static::createClient([], ['HTTP_ACCEPT' => 'application/json']);
    }

    public function testApiGetNoKey()
    {
        $this->client->request(
            'GET',
            '/v1/keys/test_get_ke',
            [],
            [],
            ['HTTP_CONTENT_TYPE' => 'application/json', 'CONTENT_TYPE' => 'application/json']
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertEquals(
            '{"code":404,"message":"The key \"test_get_ke\" does not exist."}',
            $this->client->getResponse()->getContent()
        );
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testApiGet()
    {
        $this->createKeyValue('test_get_key', 'test_get_value');

        $this->client->request(
            'GET',
            '/v1/keys/test_get_key',
            [],
            [],
            ['HTTP_CONTENT_TYPE' => 'application/json', 'CONTENT_TYPE' => 'application/json']
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertEquals(
            '{"key":"test_get_key","value":"test_get_value"}',
            $this->client->getResponse()->getContent()
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testApiCreate()
    {
        $this->client->request(
            'POST',
            '/v1/keys',
            [],
            [],
            ['HTTP_CONTENT_TYPE' => 'application/json', 'CONTENT_TYPE' => 'application/json'],
            json_encode(['key' => 'test_key', 'value' => 'test_value'])
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertEquals(
            '{"key":"test_key","value":"test_value"}',
            $this->client->getResponse()->getContent()
        );
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testApiCreateFormError()
    {
        $this->client->request(
            'POST',
            '/v1/keys',
            [],
            [],
            ['HTTP_CONTENT_TYPE' => 'application/json', 'CONTENT_TYPE' => 'application/json'],
            json_encode(['ke' => 'test_key', 'value' => 'test_value'])
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertEquals(
            '{"code":400,"message":"Validation Failed","errors":{"errors":["This form should not contain extra fields."],"children":{"key":{},"value":{}}}}',
            $this->client->getResponse()->getContent()
        );
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testApiPatch()
    {
        $this->createKeyValue('key_to_patch');

        $this->client->request(
            'PATCH',
            '/v1/keys/key_to_patch',
            [],
            [],
            ['HTTP_CONTENT_TYPE' => 'application/json', 'CONTENT_TYPE' => 'application/json'],
            json_encode(['value' => 'value_to_patch'])
        );

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }

    public function testApiUpdate()
    {
        $this->createKeyValue('key_to_update');

        $this->client->request(
            'PUT',
            '/v1/keys/key_to_update',
            [],
            [],
            ['HTTP_CONTENT_TYPE' => 'application/json', 'CONTENT_TYPE' => 'application/json'],
            json_encode(['key' => 'key_to_update', 'value' => 'test_update_value'])
        );

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }

    public function testApiDelete()
    {
        $this->createKeyValue('key with space');

        $this->client->request(
            'DELETE',
            '/v1/keys/key with space',
            [],
            [],
            ['HTTP_CONTENT_TYPE' => 'application/json', 'CONTENT_TYPE' => 'application/json']
        );

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }

    /**
     * createKeyValue.
     *
     * @param string $key
     * @param string $value
     */
    private function createKeyValue($key = 'test_key', $value = 'test_value')
    {
        $keyValue = new KeyValue();
        $keyValue->setKey($key);
        $keyValue->setValue($value);

        $storeHandler = new StoreHandler(new JsonFileStore(__DIR__.'/App/test.json'));
        $storeHandler->flush($keyValue);
    }
}
